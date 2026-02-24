document.addEventListener("DOMContentLoaded",async function(){let E;const m=document.querySelector(".kanban-update-item-sidebar"),S=document.querySelector(".kanban-wrapper"),x=document.querySelector(".comment-editor"),g=document.querySelector(".kanban-add-new-board"),v=[].slice.call(document.querySelectorAll(".kanban-add-board-input")),q=document.querySelector(".kanban-add-board-btn"),T=document.querySelector("#due-date"),L=$(".select2"),h=document.querySelector("html").getAttribute("data-assets-path");let k=[];const y=await fetch("/kanban/users");y.ok?(k=await y.json(),console.log("Kanban Users Loaded:",k)):console.error("Failed to fetch Kanban users:",y.status);const j=new bootstrap.Offcanvas(m);let p=null;const A=await fetch("/kanban/data");A.ok||console.error("error",A),E=await A.json(),T&&T.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"j F, Y",dateFormat:"Y-m-d"});//! TODO: Update Event label and guest code to JS once select removes jQuery dependency
if(L.length){let e=function(a){if(!a.id)return a.text;var r="<div class='badge "+$(a.element).data("color")+"'> "+a.text+"</div>";return r},t=function(a){if(!a.id)return a.text;var r=$(a.element).attr("data-avatar")||"",s='<div class="d-flex align-items-center"><div class="avatar avatar-xs me-2"><img src="'+(r||h+"img/avatars/1.png")+'" alt="Avatar" class="rounded-circle"></div><span>'+a.text+"</span></div>";return s};var Y=e,Q=t;const n=$(".select2-users");n.length&&k.forEach(a=>{const r=`<option value="${a.id}" data-avatar="${a.avatar}">${a.name}</option>`;n.append(r)}),L.each(function(){var a=$(this);a.wrap("<div class='position-relative'></div>").select2({placeholder:"Selecionar",dropdownParent:a.parent(),templateResult:a.hasClass("select2-users")?t:e,templateSelection:a.hasClass("select2-users")?t:e,escapeMarkup:function(r){return r}})})}let b;x&&(b=new Quill(x,{modules:{toolbar:".comment-toolbar"},placeholder:"Escreva um comentário...",theme:"snow"}));const C=()=>`
  <div class="dropdown">
      <i class="dropdown-toggle icon-base ti tabler-dots-vertical cursor-pointer"
         id="board-dropdown"
         data-bs-toggle="dropdown"
         aria-haspopup="true"
         aria-expanded="false">
      </i>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="board-dropdown">
          <a class="dropdown-item delete-board" href="javascript:void(0)">
              <i class="icon-base ti tabler-trash icon-xs"></i>
              <span class="align-middle">Excluir</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-edit icon-xs"></i>
              <span class="align-middle">Renomear</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-archive icon-xs"></i>
              <span class="align-middle">Arquivar</span>
          </a>
      </div>
  </div>
`,B=()=>`
<div class="dropdown kanban-tasks-item-dropdown">
    <i class="dropdown-toggle icon-base ti tabler-dots-vertical"
       id="kanban-tasks-item-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
    </i>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kanban-tasks-item-dropdown">
        <a class="dropdown-item" href="javascript:void(0)">Copiar link da tarefa</a>
        <a class="dropdown-item" href="javascript:void(0)">Duplicar tarefa</a>
        <a class="dropdown-item delete-task" href="javascript:void(0)">Excluir</a>
    </div>
</div>
`,_=(e,t)=>`
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${e}">${t}</div>
    </div>
    ${B()}
</div>
`,P=(e=[],t=!1,n="",a="",r=[])=>{const s=t?" pull-up":"",c=n?`avatar-${n}`:"",d=Array.isArray(r)?r:r?r.split(","):[],l=Array.isArray(e)?e:e?e.split(","):[];return l.length>0?l.map((i,o,w)=>{const X=a&&o!==w.length-1?` me-${a}`:"",W=d[o]||"";let H=i;return!i.startsWith("http")&&!i.startsWith("/")&&!i.startsWith("data:")&&(H=h+"img/avatars/"+i),`
            <div class="avatar ${c}${X} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${W}">
                <img src="${H}"
                     alt="Avatar"
                     class="rounded-circle${s}">
            </div>
        `}).join(""):""},R=async e=>{const t=document.querySelector(".activities-container");if(t){t.innerHTML='<div class="text-center p-4"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>',console.log("Fetching activities for Item ID:",e);try{const n=await fetch(`/kanban/item/${e}/activities`);if(!n.ok)throw new Error("Falha ao carregar histórico");const a=await n.json();if(a.length===0){t.innerHTML='<div class="text-center p-4 text-muted">Nenhuma atividade registrada ainda.</div>';return}t.innerHTML=a.map(r=>{var c,d;let s="";return r.type==="comment"&&((c=r.extra_data)!=null&&c.text)?s=`<div class="mt-2 p-2 bg-light rounded text-body">${r.extra_data.text}</div>`:r.type==="attachment"&&((d=r.extra_data)!=null&&d.files)&&(s=`<div class="mt-2">
            ${r.extra_data.files.map(l=>`
              <a href="/storage/${l.path}" target="_blank" class="d-block text-primary mb-1">
                <i class="ti tabler-file-download me-1"></i>${l.name}
              </a>
            `).join("")}
          </div>`),`
          <div class="media mb-4 d-flex align-items-start">
            <div class="avatar me-3 flex-shrink-0">
              <img src="${r.user_avatar}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body w-100">
              <p class="mb-0 pt-1"><span>${r.user_name}</span> ${r.description}</p>
              ${s}
              <small class="text-body-secondary">${r.time_ago}</small>
            </div>
          </div>
        `}).join("")}catch(n){console.error(n),t.innerHTML='<div class="text-center p-4 text-danger">Erro ao carregar histórico.</div>'}}},K=(e,t,n,a)=>`
<div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
    <div class="d-flex">
        <span class="d-flex align-items-center me-2">
            <i class="icon-base ti tabler-paperclip me-1"></i>
            <span class="attachments">${e}</span>
        </span>
        <span class="d-flex align-items-center ms-2">
            <i class="icon-base ti tabler-message-2 me-1"></i>
            <span>${t}</span>
        </span>
    </div>
    <div class="avatar-group d-flex align-items-center assigned-avatar">
        ${P(n,!0,"xs",null,a)}
    </div>
</div>
`,u=new jKanban({element:".kanban-wrapper",gutter:"12px",widthBoard:"250px",dragItems:!0,boards:E.map(e=>(e.item=e.item.map(t=>(t["assigned-ids"]=t.assigned_ids?t.assigned_ids.join(","):"",t)),e)),dragBoards:!0,addItemButton:!0,buttonContent:"+ Adicionar Item",itemAddOptions:{enabled:!0,content:"+ Adicionar Novo Item",class:"kanban-title-button btn btn-default border-none",footer:!1},click:e=>{const t=e;p=t.getAttribute("data-eid");const n=t.getAttribute("data-eid")?t.querySelector(".kanban-text").textContent:t.textContent,a=t.getAttribute("data-due-date"),r=t.getAttribute("data-badge-text");t.getAttribute("data-assigned"),j.show(),m.querySelector("#title").value=n,a&&m.querySelector("#due-date")._flatpickr?m.querySelector("#due-date")._flatpickr.setDate(a):m.querySelector("#due-date").value=a||"",$(".kanban-update-item-sidebar").find("#label").val(r).trigger("change");const s=t.getAttribute("data-assigned-ids");s?$("#select2-users").val(s.split(",")).trigger("change"):$("#select2-users").val(null).trigger("change"),R(p)},dropEl:(e,t,n,a)=>{const r=e.getAttribute("data-eid"),s=t.closest(".kanban-board").getAttribute("data-id");fetch("/kanban/move-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({itemId:r,targetBoardId:s})}).then(c=>c.json()).catch(c=>console.error("Error moving item:",c))},buttonClick:(e,t)=>{const n=document.createElement("form");n.setAttribute("class","new-item-form"),n.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Adicionar Conteúdo" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Adicionar</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancelar</button>
        </div>
      `,u.addForm(t,n),n.addEventListener("submit",a=>{a.preventDefault(),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));const r=a.target[0].value;fetch("/kanban/add-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({boardId:t,title:r})}).then(s=>s.json()).then(s=>{u.addElement(t,{title:`<span class="kanban-text">${r}</span>`,id:s.id}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(i=>{(!i.previousElementSibling||!i.previousElementSibling.classList.contains("kanban-tasks-item-dropdown"))&&i.insertAdjacentHTML("beforebegin",B())}),Array.from(document.querySelectorAll(".kanban-item .kanban-tasks-item-dropdown")).forEach(i=>{i.addEventListener("click",o=>o.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(i=>{i.addEventListener("click",()=>{const o=i.closest(".kanban-item").getAttribute("data-eid");u.removeElement(o)})}),n.remove()})}),n.querySelector(".cancel-add-item").addEventListener("click",()=>n.remove())}});S&&new PerfectScrollbar(S);const D=document.querySelector(".kanban-update-item-sidebar .btn-primary");D&&D.addEventListener("click",()=>{var i;const e=document.querySelector("#title").value,t=document.querySelector("#due-date").value,n=$("#label").val(),a=((i=$("#label option:selected").data("color"))==null?void 0:i.replace("bg-label-",""))||"success",r=$("#select2-users").val(),s=b?b.root.innerHTML:"",c=b?b.getText().trim().length===0:!0,d=new FormData;d.append("_method","PUT"),d.append("title",e),d.append("dueDate",t),d.append("badgeText",n||""),d.append("badgeColor",a),d.append("comment",c?"":s),r&&r.forEach(o=>d.append("assignedTo[]",o));const l=document.querySelector("#attachments");if(l&&l.files.length>0)for(let o=0;o<l.files.length;o++)d.append("attachments[]",l.files[o]);fetch("/kanban/update-item/"+p,{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:d}).then(async o=>{if(!o.ok){const w=await o.json();throw new Error(w.error||"Erro ao atualizar item")}return o.json()}).then(o=>{b&&b.setContents([]),location.reload()}).catch(o=>{alert(o.message),console.error("Error updating item:",o)})});const I=document.querySelector(".kanban-update-item-sidebar .btn-label-danger");I&&I.addEventListener("click",()=>{confirm("Tem certeza que deseja excluir esta tarefa?")&&fetch("/kanban/delete-item/"+p,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(e=>{u.removeElement(p),j.hide()}).catch(e=>console.error("Error deleting item:",e))});const f=document.querySelector(".kanban-container"),N=Array.from(document.querySelectorAll(".kanban-title-board")),O=Array.from(document.querySelectorAll(".kanban-item"));O.length&&O.forEach(e=>{const t=`<span class="kanban-text">${e.textContent}</span>`;let n="";e.getAttribute("data-image")&&(n=`
              <img class="img-fluid rounded mb-2"
                   src="${h}img/elements/${e.getAttribute("data-image")}">
          `),e.textContent="",e.getAttribute("data-badge")&&e.getAttribute("data-badge-text")&&e.insertAdjacentHTML("afterbegin",`${_(e.getAttribute("data-badge"),e.getAttribute("data-badge-text"))}${n}${t}`),(e.getAttribute("data-comments")||e.getAttribute("data-due-date")||e.getAttribute("data-assigned"))&&e.insertAdjacentHTML("beforeend",K(e.getAttribute("data-attachments")||0,e.getAttribute("data-comments")||0,e.getAttribute("data-assigned")?e.getAttribute("data-assigned").split(","):[],e.getAttribute("data-members")?e.getAttribute("data-members").split(","):[]))}),Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(e=>{new bootstrap.Tooltip(e)});const F=Array.from(document.querySelectorAll(".kanban-tasks-item-dropdown"));F.length&&F.forEach(e=>{e.addEventListener("click",t=>{t.stopPropagation()})}),q&&q.addEventListener("click",()=>{v.forEach(e=>{e.value="",e.classList.toggle("d-none")})}),f&&f.append(g),N&&N.forEach(e=>{e.addEventListener("mouseenter",()=>{e.contentEditable="true"}),e.insertAdjacentHTML("afterend",C())}),Array.from(document.querySelectorAll(".delete-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board").getAttribute("data-id");u.removeBoard(t)})}),Array.from(document.querySelectorAll(".delete-task")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-item").getAttribute("data-eid");u.removeElement(t)})});const M=document.querySelector(".kanban-add-board-cancel-btn");M&&M.addEventListener("click",()=>{v.forEach(e=>{e.classList.toggle("d-none")})}),g&&g.addEventListener("submit",e=>{e.preventDefault();const t=e.target.querySelector(".form-control").value.trim();fetch("/kanban/add-board",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:t})}).then(n=>n.json()).then(n=>{const a=n.id,r=n.title;u.addBoards([{id:a,title:r}]);const s=document.querySelector(".kanban-board:last-child");if(s){const c=s.querySelector(".kanban-title-board");c.insertAdjacentHTML("afterend",C()),c.addEventListener("mouseenter",()=>{c.contentEditable="true"});const d=s.querySelector(".delete-board");d&&d.addEventListener("click",()=>{const l=d.closest(".kanban-board").getAttribute("data-id");u.removeBoard(l)})}}),v.forEach(n=>{n.classList.add("d-none")}),f&&f.append(g)}),m.addEventListener("hidden.bs.offcanvas",()=>{const e=m.querySelector(".ql-editor").firstElementChild;e&&(e.innerHTML="")}),m&&m.addEventListener("shown.bs.offcanvas",()=>{Array.from(m.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(t=>{new bootstrap.Tooltip(t)})})});
