(()=>{var v=window.aiPageAssistant||{},h=document.getElementById("ai-page-assistant");h&&S(h,v);function S(e,t){let s=I(),a=t.strings||{};e.style.setProperty("--ai-pa-primary",t.primaryColor||"#2563eb"),e.innerHTML=`
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
  `;let o=e.querySelector(".ai-pa__button"),l=e.querySelector(".ai-pa__panel"),m=e.querySelector(".ai-pa__close"),d=e.querySelector(".ai-pa__form"),i=e.querySelector(".ai-pa__input"),p=e.querySelector(".ai-pa__messages"),r=e.querySelector(".ai-pa__consent"),g=e.querySelector(".ai-pa__accept"),_=e.querySelector(".ai-pa__delete");localStorage.getItem("ai-pa-consent")==="1"&&(r.hidden=!0),o.addEventListener("click",()=>{let n=!l.hidden;l.hidden=n,o.setAttribute("aria-expanded",String(!n)),n||i.focus()}),m.addEventListener("click",()=>{l.hidden=!0,o.setAttribute("aria-expanded","false")}),g.addEventListener("click",()=>{localStorage.setItem("ai-pa-consent","1"),r.hidden=!0,i.focus()}),_.addEventListener("click",async()=>{await fetch(`${t.apiBase}/data`,{method:"DELETE",headers:{"Content-Type":"application/json","X-WP-Nonce":t.nonce},body:JSON.stringify({visitor_id:s})}),y(p,"assistant","Your stored AI chat data was deleted.")}),d.addEventListener("submit",async n=>{if(n.preventDefault(),!r.hidden){r.classList.add("ai-pa__consent--attention");return}let u=i.value.trim();if(!u)return;i.value="",y(p,"user",u);let f=y(p,"assistant","");try{await w(t,s,u,f)}catch(b){f.textContent=b.message||"AI request failed."}})}async function w(e,t,s,a){let o=await fetch(`${e.apiBase}/chat`,{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":e.nonce},body:JSON.stringify({message:s,page_id:Number(e.pageId||0),page_title:document.title,page_url:window.location.href,page_text:q(),visitor_id:t,language:navigator.language||e.language||"en"})});if(!o.ok||!o.body){let i=await o.json().catch(()=>({}));throw new Error(i?.error?.message||`Request failed (${o.status})`)}let l=o.body.getReader(),m=new TextDecoder,d="";for(;;){let{done:i,value:p}=await l.read();if(i)break;d+=m.decode(p,{stream:!0});let r=d.split(`

`);d=r.pop()||"";for(let g of r){let _=g.split(`
`).find(u=>u.startsWith("data: "));if(!_)continue;let n=JSON.parse(_.slice(6));if(n.type==="token"&&(a.textContent+=n.content,a.parentElement.scrollTop=a.parentElement.scrollHeight),n.type==="error")throw new Error(n.message)}}}function y(e,t,s){let a=document.createElement("div");return a.className=`ai-pa__message ai-pa__message--${t}`,a.textContent=s,e.appendChild(a),e.scrollTop=e.scrollHeight,a}function I(){let e="ai-pa-visitor-id",t=localStorage.getItem(e);return t||(t=crypto.randomUUID?crypto.randomUUID():`${Date.now()}-${Math.random().toString(16).slice(2)}`,localStorage.setItem(e,t)),t}function q(){let t=(document.querySelector("main, article, .site-main, #content")||document.body).cloneNode(!0);return t.querySelectorAll("script, style, noscript, iframe, svg, nav, form, button, .ai-pa").forEach(s=>s.remove()),t.textContent.replace(/\s+/g," ").trim().slice(0,12e3)}function c(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}})();
