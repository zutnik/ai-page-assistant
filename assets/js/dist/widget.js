(()=>{var I=window.aiPageAssistant||{},$=document.getElementById("ai-page-assistant");$&&E($,I);function E(e,t){let n=A(),a=t.strings||{};e.style.setProperty("--ai-pa-primary",t.primaryColor||"#2563eb"),e.innerHTML=`
    <button class="ai-pa__button" type="button" aria-expanded="false">${l(a.button||"Ask AI")}</button>
    <section class="ai-pa__panel" aria-live="polite" hidden>
      <header class="ai-pa__header">
        <strong>AI Assistant</strong>
        <button class="ai-pa__close" type="button" aria-label="Close">x</button>
      </header>
      <div class="ai-pa__messages">
        <div class="ai-pa__message ai-pa__message--assistant">${l(t.greeting||"Hi! Ask me anything about this page.")}</div>
      </div>
      <div class="ai-pa__consent" ${t.consentRequired?"":"hidden"}>
        <p>${l(a.consent||"AI answers may be inaccurate. Do not share sensitive personal data.")}</p>
        <button type="button" class="ai-pa__accept">${l(a.accept||"I understand")}</button>
      </div>
      <form class="ai-pa__form">
        <textarea class="ai-pa__input" rows="2" placeholder="${l(a.placeholder||"Ask about this page...")}"></textarea>
        <button class="ai-pa__send" type="submit">${l(a.send||"Send")}</button>
      </form>
      <button class="ai-pa__delete" type="button">${l(a.deleteData||"Delete my AI chat data")}</button>
    </section>
  `;let s=e.querySelector(".ai-pa__button"),i=e.querySelector(".ai-pa__panel"),d=e.querySelector(".ai-pa__close"),c=e.querySelector(".ai-pa__form"),o=e.querySelector(".ai-pa__input"),u=e.querySelector(".ai-pa__messages"),p=e.querySelector(".ai-pa__consent"),f=e.querySelector(".ai-pa__accept"),_=e.querySelector(".ai-pa__delete");localStorage.getItem("ai-pa-consent")==="1"&&(p.hidden=!0),s.addEventListener("click",()=>{let r=!i.hidden;i.hidden=r,s.setAttribute("aria-expanded",String(!r)),r||o.focus()}),d.addEventListener("click",()=>{i.hidden=!0,s.setAttribute("aria-expanded","false")}),f.addEventListener("click",()=>{localStorage.setItem("ai-pa-consent","1"),p.hidden=!0,o.focus()}),_.addEventListener("click",async()=>{await fetch(`${t.apiBase}/data`,{method:"DELETE",headers:{"Content-Type":"application/json","X-WP-Nonce":t.nonce},body:JSON.stringify({visitor_id:n})}),h(u,"assistant","Your stored AI chat data was deleted.")}),c.addEventListener("submit",async r=>{if(r.preventDefault(),!p.hidden){p.classList.add("ai-pa__consent--attention");return}let m=o.value.trim();if(!m)return;o.value="",h(u,"user",m);let g=h(u,"assistant","");try{await k(t,n,m,g)}catch(S){let w=S.message||"Connection lost. Please retry.",v=g.dataset.raw||g.textContent||"";g.dataset.raw=v?`${v}

${w}`:w,y(g)}})}async function k(e,t,n,a){let s=await fetch(`${e.apiBase}/chat`,{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":e.nonce},body:JSON.stringify({message:n,page_id:Number(e.pageId||0),page_title:document.title,page_url:window.location.href,page_text:x(),visitor_id:t,language:navigator.language||e.language||"en"})});if(!s.ok||!s.body){let o=await s.json().catch(()=>({}));throw new Error(o?.error?.message||`Request failed (${s.status})`)}let i=s.body.getReader(),d=new TextDecoder,c="";try{for(;;){let{done:o,value:u}=await i.read();if(o)break;c+=d.decode(u,{stream:!0});let p=c.split(`

`);c=p.pop()||"";for(let f of p){let _=f.split(`
`).find(m=>m.startsWith("data: "));if(!_)continue;let r=JSON.parse(_.slice(6));if(r.type==="token"&&(a.dataset.raw=`${a.dataset.raw||""}${r.content}`,y(a),a.parentElement.scrollTop=a.parentElement.scrollHeight),r.type==="error")throw new Error(r.message)}}}catch(o){throw new Error(o.message||"Connection lost. Please retry.")}}function h(e,t,n){let a=document.createElement("div");return a.className=`ai-pa__message ai-pa__message--${t}`,t==="assistant"?(a.dataset.raw=n,y(a)):a.textContent=n,e.appendChild(a),e.scrollTop=e.scrollHeight,a}function y(e){let t=e.dataset.raw||"";if(!t){e.textContent="";return}e.innerHTML=q(t)}function q(e){let t=l(e).split(/\n+/),n=[],a=[],s=()=>{a.length&&(n.push(`<ul>${a.map(i=>`<li>${b(i)}</li>`).join("")}</ul>`),a=[])};for(let i of t){let d=i.trim();if(!d){s();continue}let c=d.match(/^[-*]\s+(.+)$/);if(c){a.push(c[1]);continue}s(),n.push(`<p>${b(d)}</p>`)}return s(),n.join("")}function b(e){return e.replace(/\*\*(.+?)\*\*/g,"<strong>$1</strong>").replace(/\*(.+?)\*/g,"<em>$1</em>").replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g,'<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')}function A(){let e="ai-pa-visitor-id",t=localStorage.getItem(e);return t||(t=crypto.randomUUID?crypto.randomUUID():`${Date.now()}-${Math.random().toString(16).slice(2)}`,localStorage.setItem(e,t)),t}function x(){let t=(document.querySelector("main, article, .site-main, #content")||document.body).cloneNode(!0);return t.querySelectorAll("script, style, noscript, iframe, svg, nav, form, button, .ai-pa").forEach(n=>n.remove()),t.textContent.replace(/\s+/g," ").trim().slice(0,12e3)}function l(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}})();
