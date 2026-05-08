const config = window.aiPageAssistant || {};
const root = document.getElementById('ai-page-assistant');

if (root) {
  initWidget(root, config);
}

function initWidget(container, settings) {
  const visitorId = getVisitorId();
  const strings = settings.strings || {};

  container.style.setProperty('--ai-pa-primary', settings.primaryColor || '#2563eb');
  container.innerHTML = `
    <button class="ai-pa__button" type="button" aria-expanded="false">${escapeHtml(strings.button || 'Ask AI')}</button>
    <section class="ai-pa__panel" aria-live="polite" hidden>
      <header class="ai-pa__header">
        <strong>AI Assistant</strong>
        <button class="ai-pa__close" type="button" aria-label="Close">x</button>
      </header>
      <div class="ai-pa__messages">
        <div class="ai-pa__message ai-pa__message--assistant">${escapeHtml(settings.greeting || 'Hi! Ask me anything about this page.')}</div>
      </div>
      <div class="ai-pa__consent" ${settings.consentRequired ? '' : 'hidden'}>
        <p>${escapeHtml(strings.consent || 'AI answers may be inaccurate. Do not share sensitive personal data.')}</p>
        <button type="button" class="ai-pa__accept">${escapeHtml(strings.accept || 'I understand')}</button>
      </div>
      <form class="ai-pa__form">
        <textarea class="ai-pa__input" rows="2" placeholder="${escapeHtml(strings.placeholder || 'Ask about this page...')}"></textarea>
        <button class="ai-pa__send" type="submit">${escapeHtml(strings.send || 'Send')}</button>
      </form>
      <button class="ai-pa__delete" type="button">${escapeHtml(strings.deleteData || 'Delete my AI chat data')}</button>
    </section>
  `;

  const button = container.querySelector('.ai-pa__button');
  const panel = container.querySelector('.ai-pa__panel');
  const close = container.querySelector('.ai-pa__close');
  const form = container.querySelector('.ai-pa__form');
  const input = container.querySelector('.ai-pa__input');
  const messages = container.querySelector('.ai-pa__messages');
  const consent = container.querySelector('.ai-pa__consent');
  const accept = container.querySelector('.ai-pa__accept');
  const deleteButton = container.querySelector('.ai-pa__delete');

  if (localStorage.getItem('ai-pa-consent') === '1') {
    consent.hidden = true;
  }

  button.addEventListener('click', () => {
    const isOpen = !panel.hidden;
    panel.hidden = isOpen;
    button.setAttribute('aria-expanded', String(!isOpen));
    if (!isOpen) input.focus();
  });

  close.addEventListener('click', () => {
    panel.hidden = true;
    button.setAttribute('aria-expanded', 'false');
  });

  accept.addEventListener('click', () => {
    localStorage.setItem('ai-pa-consent', '1');
    consent.hidden = true;
    input.focus();
  });

  deleteButton.addEventListener('click', async () => {
    await fetch(`${settings.apiBase}/data`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': settings.nonce
      },
      body: JSON.stringify({ visitor_id: visitorId })
    });
    appendMessage(messages, 'assistant', 'Your stored AI chat data was deleted.');
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!consent.hidden) {
      consent.classList.add('ai-pa__consent--attention');
      return;
    }

    const message = input.value.trim();
    if (!message) return;

    input.value = '';
    appendMessage(messages, 'user', message);
    const assistantBubble = appendMessage(messages, 'assistant', '');

    try {
      await streamChat(settings, visitorId, message, assistantBubble);
    } catch (error) {
      assistantBubble.textContent = error.message || 'AI request failed.';
    }
  });
}

async function streamChat(settings, visitorId, message, bubble) {
  const response = await fetch(`${settings.apiBase}/chat`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': settings.nonce
    },
    body: JSON.stringify({
      message,
      page_id: Number(settings.pageId || 0),
      page_title: document.title,
      page_url: window.location.href,
      page_text: collectPageText(),
      visitor_id: visitorId,
      language: navigator.language || settings.language || 'en'
    })
  });

  if (!response.ok || !response.body) {
    const payload = await response.json().catch(() => ({}));
    throw new Error(payload?.error?.message || `Request failed (${response.status})`);
  }

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const events = buffer.split('\n\n');
    buffer = events.pop() || '';

    for (const event of events) {
      const line = event.split('\n').find((item) => item.startsWith('data: '));
      if (!line) continue;

      const payload = JSON.parse(line.slice(6));

      if (payload.type === 'token') {
        bubble.textContent += payload.content;
        bubble.parentElement.scrollTop = bubble.parentElement.scrollHeight;
      }

      if (payload.type === 'error') {
        throw new Error(payload.message);
      }
    }
  }
}

function appendMessage(messages, role, text) {
  const bubble = document.createElement('div');
  bubble.className = `ai-pa__message ai-pa__message--${role}`;
  bubble.textContent = text;
  messages.appendChild(bubble);
  messages.scrollTop = messages.scrollHeight;
  return bubble;
}

function getVisitorId() {
  const key = 'ai-pa-visitor-id';
  let id = localStorage.getItem(key);

  if (!id) {
    id = crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    localStorage.setItem(key, id);
  }

  return id;
}

function collectPageText() {
  const source = document.querySelector('main, article, .site-main, #content') || document.body;
  const clone = source.cloneNode(true);

  clone.querySelectorAll('script, style, noscript, iframe, svg, nav, form, button, .ai-pa').forEach((node) => node.remove());

  return clone.textContent
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, 12000);
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
