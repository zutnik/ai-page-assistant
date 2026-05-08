(()=>{var S=window.aiPageAssistant||{},b=document.getElementById("ai-page-assistant");b&&w(b,S);function w(e,t){let r=I(),a=t.strings||{};e.style.setProperty("--ai-pa-primary",t.primaryColor||"#2563eb"),e.innerHTML=`
    <button class="ai-pa__button" type="button" aria-expanded="false">${c(a.button||"Ask AI")}</button>
    <section class="ai-pa__panel" aria-live="polite" hidden>
      <header class="ai-pa__header">
        <strong>AI Assistant</strong>
        <button class="ai-pa__close" type="button" aria-label="Close">x</button>
      </header>
      <div class="ai-pa__messages">
        <div class="ai-pa__message ai-pa__message--assistant">${c(t.greeting||"Hi! Ask me anything about this page.")}</div>
      </div>
      <div class="ai-pa__consent" ${t.consentRequired?"":"hidden"}>
        <p>${c(a.consent||"AI answers may be inaccurate. Do not share sensitive personal data.")}</p>
        <button type="button" class="ai-pa__accept">${c(a.accept||"I understand")}</button>
      </div>
      <form class="ai-pa__form">
        <textarea class="ai-pa__input" rows="2" placeholder="${c(a.placeholder||"Ask about this page...")}"></textarea>
        <button class="ai-pa__send" type="submit">${c(a.send||"Send")}</button>
      </form>
      <button class="ai-pa__delete" type="button">${c(a.deleteData||"Delete my AI chat data")}</button>
    </section>
  `;let s=e.querySelector(".ai-pa__button"),l=e.querySelector(".ai-pa__panel"),g=e.querySelector(".ai-pa__close"),d=e.querySelector(".ai-pa__form"),n=e.querySelector(".ai-pa__input"),p=e.querySelector(".ai-pa__messages"),i=e.querySelector(".ai-pa__consent"),y=e.querySelector(".ai-pa__accept"),_=e.querySelector(".ai-pa__delete");localStorage.getItem("ai-pa-consent")==="1"&&(i.hidden=!0),s.addEventListener("click",()=>{let o=!l.hidden;l.hidden=o,s.setAttribute("aria-expanded",String(!o)),o||n.focus()}),g.addEventListener("click",()=>{l.hidden=!0,s.setAttribute("aria-expanded","false")}),y.addEventListener("click",()=>{localStorage.setItem("ai-pa-consent","1"),i.hidden=!0,n.focus()}),_.addEventListener("click",async()=>{await fetch(`${t.apiBase}/data`,{method:"DELETE",headers:{"Content-Type":"application/json","X-WP-Nonce":t.nonce},body:JSON.stringify({visitor_id:r})}),f(p,"assistant","Your stored AI chat data was deleted.")}),d.addEventListener("submit",async o=>{if(o.preventDefault(),!i.hidden){i.classList.add("ai-pa__consent--attention");return}let u=n.value.trim();if(!u)return;n.value="",f(p,"user",u);let m=f(p,"assistant","");try{await E(t,r,u,m)}catch(v){let h=v.message||"Connection lost. Please retry.";m.textContent=m.textContent?`${m.textContent}

${h}`:h}})}async function E(e,t,r,a){let s=await fetch(`${e.apiBase}/chat`,{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":e.nonce},body:JSON.stringify({message:r,page_id:Number(e.pageId||0),page_title:document.title,page_url:window.location.href,page_text:$(),visitor_id:t,language:navigator.language||e.language||"en"})});if(!s.ok||!s.body){let n=await s.json().catch(()=>({}));throw new Error(n?.error?.message||`Request failed (${s.status})`)}let l=s.body.getReader(),g=new TextDecoder,d="";try{for(;;){let{done:n,value:p}=await l.read();if(n)break;d+=g.decode(p,{stream:!0});let i=d.split(`

`);d=i.pop()||"";for(let y of i){let _=y.split(`
`).find(u=>u.startsWith("data: "));if(!_)continue;let o=JSON.parse(_.slice(6));if(o.type==="token"&&(a.textContent+=o.content,a.parentElement.scrollTop=a.parentElement.scrollHeight),o.type==="error")throw new Error(o.message)}}}catch(n){throw new Error(n.message||"Connection lost. Please retry.")}}function f(e,t,r){let a=document.createElement("div");return a.className=`ai-pa__message ai-pa__message--${t}`,a.textContent=r,e.appendChild(a),e.scrollTop=e.scrollHeight,a}function I(){let e="ai-pa-visitor-id",t=localStorage.getItem(e);return t||(t=crypto.randomUUID?crypto.randomUUID():`${Date.now()}-${Math.random().toString(16).slice(2)}`,localStorage.setItem(e,t)),t}function $(){let t=(document.querySelector("main, article, .site-main, #content")||document.body).cloneNode(!0);return t.querySelectorAll("script, style, noscript, iframe, svg, nav, form, button, .ai-pa").forEach(r=>r.remove()),t.textContent.replace(/\s+/g," ").trim().slice(0,12e3)}function c(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}})();
