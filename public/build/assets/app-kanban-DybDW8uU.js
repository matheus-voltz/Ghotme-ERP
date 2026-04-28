document.addEventListener("DOMContentLoaded",async function(){let T;const u=document.querySelector(".kanban-update-item-sidebar"),x=document.querySelector(".kanban-wrapper"),q=document.querySelector(".comment-editor"),v=document.querySelector(".kanban-add-new-board"),k=[].slice.call(document.querySelectorAll(".kanban-add-board-input")),L=document.querySelector(".kanban-add-board-btn"),j=document.querySelector("#due-date"),C=$(".select2"),y=document.querySelector("html").getAttribute("data-assets-path");let A=[];const E=await fetch("/kanban/users");E.ok?(A=await E.json(),console.log("Kanban Users Loaded:",A)):console.error("Failed to fetch Kanban users:",E.status);const B=new bootstrap.Offcanvas(u);let g=null;const w=await fetch("/kanban/data");w.ok||console.error("error",w),T=await w.json(),j&&j.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"j F, Y",dateFormat:"Y-m-d"});//! TODO: Update Event label and guest code to JS once select removes jQuery dependency
if(C.length){let e=function(r){if(!r.id)return r.text;var n="<div class='badge "+$(r.element).data("color")+"'> "+r.text+"</div>";return n},t=function(r){if(!r.id)return r.text;var n=$(r.element).attr("data-avatar")||"",s='<div class="d-flex align-items-center"><div class="avatar avatar-xs me-2"><img src="'+(n||y+"img/avatars/1.png")+'" alt="Avatar" class="rounded-circle"></div><span>'+r.text+"</span></div>";return s};var Q=e,G=t;const a=$(".select2-users");a.length&&A.forEach(r=>{const n=`<option value="${r.id}" data-avatar="${r.avatar}">${r.name}</option>`;a.append(n)}),C.each(function(){var r=$(this);r.wrap("<div class='position-relative'></div>").select2({placeholder:"Selecionar",dropdownParent:r.parent(),templateResult:r.hasClass("select2-users")?t:e,templateSelection:r.hasClass("select2-users")?t:e,escapeMarkup:function(n){return n}})})}let b;q&&(b=new Quill(q,{modules:{toolbar:".comment-toolbar"},placeholder:"Escreva um comentário...",theme:"snow"}));const O=()=>`
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
          <a class="dropdown-item rename-board" href="javascript:void(0)">
              <i class="icon-base ti tabler-edit icon-xs"></i>
              <span class="align-middle">Renomear</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-archive icon-xs"></i>
              <span class="align-middle">Arquivar</span>
          </a>
      </div>
  </div>
`,N=()=>`
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
    ${N()}
</div>
`,K=(e=[],t=!1,a="",r="",n=[])=>{const s=t?" pull-up":"",i=a?`avatar-${a}`:"",c=Array.isArray(n)?n:n?n.split(","):[],l=Array.isArray(e)?e:e?e.split(","):[];return l.length>0?l.map((d,o,p)=>{const S=r&&o!==p.length-1?` me-${r}`:"",f=c[o]||"";let R=d;return!d.startsWith("http")&&!d.startsWith("/")&&!d.startsWith("data:")&&(R=y+"img/avatars/"+d),`
            <div class="avatar ${i}${S} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${f}">
                <img src="${R}"
                     alt="Avatar"
                     class="rounded-circle${s}">
            </div>
        `}).join(""):""},X=async e=>{const t=document.querySelector(".activities-container");if(t){t.innerHTML='<div class="text-center p-4"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>',console.log("Fetching activities for Item ID:",e);try{const a=await fetch(`/kanban/item/${e}/activities`);if(!a.ok)throw new Error("Falha ao carregar histórico");const r=await a.json();if(r.length===0){t.innerHTML='<div class="text-center p-4 text-muted">Nenhuma atividade registrada ainda.</div>';return}t.innerHTML=r.map(n=>{var i,c;let s="";return n.type==="comment"&&((i=n.extra_data)!=null&&i.text)?s=`<div class="mt-2 p-2 bg-light rounded text-body">${n.extra_data.text}</div>`:n.type==="attachment"&&((c=n.extra_data)!=null&&c.files)&&(s=`<div class="mt-2">
            ${n.extra_data.files.map(l=>`
              <a href="/storage/${l.path}" target="_blank" class="d-block text-primary mb-1">
                <i class="ti tabler-file-download me-1"></i>${l.name}
              </a>
            `).join("")}
          </div>`),`
          <div class="media mb-4 d-flex align-items-start">
            <div class="avatar me-3 flex-shrink-0">
              <img src="${n.user_avatar}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body w-100">
              <p class="mb-0 pt-1"><span>${n.user_name}</span> ${n.description}</p>
              ${s}
              <small class="text-body-secondary">${n.time_ago}</small>
            </div>
          </div>
        `}).join("")}catch(a){console.error(a),t.innerHTML='<div class="text-center p-4 text-danger">Erro ao carregar histórico.</div>'}}},J=(e,t,a,r)=>`
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
        ${K(a,!0,"xs",null,r)}
    </div>
</div>
`,m=new jKanban({element:".kanban-wrapper",gutter:"12px",widthBoard:"250px",dragItems:!0,boards:T.map(e=>(e.item=e.item.map(t=>(t["assigned-ids"]=t.assigned_ids?t.assigned_ids.join(","):"",t)),e)),dragBoards:!0,addItemButton:!0,buttonContent:"+ Adicionar Item",itemAddOptions:{enabled:!0,content:"+ Adicionar Novo Item",class:"kanban-title-button btn btn-default border-none",footer:!1},click:e=>{const t=e;g=t.getAttribute("data-eid");const a=t.getAttribute("data-eid")?t.querySelector(".kanban-text").textContent:t.textContent,r=t.getAttribute("data-due-date"),n=t.getAttribute("data-badge-text");t.getAttribute("data-assigned"),B.show(),u.querySelector("#title").value=a,r&&u.querySelector("#due-date")._flatpickr?u.querySelector("#due-date")._flatpickr.setDate(r):u.querySelector("#due-date").value=r||"",$(".kanban-update-item-sidebar").find("#label").val(n).trigger("change");const s=t.getAttribute("data-assigned-ids");s?$("#select2-users").val(s.split(",")).trigger("change"):$("#select2-users").val(null).trigger("change"),X(g)},dropEl:(e,t,a,r)=>{const n=e.getAttribute("data-eid"),s=t.closest(".kanban-board").getAttribute("data-id");fetch("/kanban/move-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({itemId:n,targetBoardId:s})}).then(i=>i.json()).catch(i=>console.error("Error moving item:",i))},buttonClick:(e,t)=>{const a=document.createElement("form");a.setAttribute("class","new-item-form"),a.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Adicionar Conteúdo" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Adicionar</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancelar</button>
        </div>
      `,m.addForm(t,a),a.addEventListener("submit",r=>{r.preventDefault(),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));const n=r.target[0].value;fetch("/kanban/add-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({boardId:t,title:n})}).then(s=>s.json()).then(s=>{m.addElement(t,{title:`<span class="kanban-text">${n}</span>`,id:s.id}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(d=>{(!d.previousElementSibling||!d.previousElementSibling.classList.contains("kanban-tasks-item-dropdown"))&&d.insertAdjacentHTML("beforebegin",N())}),Array.from(document.querySelectorAll(".kanban-item .kanban-tasks-item-dropdown")).forEach(d=>{d.addEventListener("click",o=>o.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(d=>{d.addEventListener("click",()=>{const o=d.closest(".kanban-item").getAttribute("data-eid");m.removeElement(o)})}),a.remove()})}),a.querySelector(".cancel-add-item").addEventListener("click",()=>a.remove())}});x&&new PerfectScrollbar(x);const D=document.querySelector(".kanban-update-item-sidebar .btn-primary");D&&D.addEventListener("click",()=>{var d;const e=document.querySelector("#title").value,t=document.querySelector("#due-date").value,a=$("#label").val(),r=((d=$("#label option:selected").data("color"))==null?void 0:d.replace("bg-label-",""))||"success",n=$("#select2-users").val(),s=b?b.root.innerHTML:"",i=b?b.getText().trim().length===0:!0,c=new FormData;c.append("_method","PUT"),c.append("title",e),c.append("dueDate",t),c.append("badgeText",a||""),c.append("badgeColor",r),c.append("comment",i?"":s),n&&n.forEach(o=>c.append("assignedTo[]",o));const l=document.querySelector("#attachments");if(l&&l.files.length>0)for(let o=0;o<l.files.length;o++)c.append("attachments[]",l.files[o]);fetch("/kanban/update-item/"+g,{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:c}).then(async o=>{if(!o.ok){const p=await o.json();throw new Error(p.error||"Erro ao atualizar item")}return o.json()}).then(o=>{b&&b.setContents([]),location.reload()}).catch(o=>{alert(o.message),console.error("Error updating item:",o)})});const F=document.querySelector(".kanban-update-item-sidebar .btn-label-danger");F&&F.addEventListener("click",()=>{confirm("Tem certeza que deseja excluir esta tarefa?")&&fetch("/kanban/delete-item/"+g,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(e=>{m.removeElement(g),B.hide()}).catch(e=>console.error("Error deleting item:",e))});const h=document.querySelector(".kanban-container"),I=Array.from(document.querySelectorAll(".kanban-title-board")),M=Array.from(document.querySelectorAll(".kanban-item"));M.length&&M.forEach(e=>{const t=`<span class="kanban-text">${e.textContent}</span>`;let a="";e.getAttribute("data-image")&&(a=`
              <img class="img-fluid rounded mb-2"
                   src="${y}img/elements/${e.getAttribute("data-image")}">
          `),e.textContent="",e.getAttribute("data-badge")&&e.getAttribute("data-badge-text")&&(e.querySelector(".kanban-tasks-item-dropdown")||e.insertAdjacentHTML("afterbegin",`${_(e.getAttribute("data-badge"),e.getAttribute("data-badge-text"))}${a}${t}`)),(e.getAttribute("data-comments")||e.getAttribute("data-due-date")||e.getAttribute("data-assigned"))&&(e.querySelector(".assigned-avatar")||e.insertAdjacentHTML("beforeend",J(e.getAttribute("data-attachments")||0,e.getAttribute("data-comments")||0,e.getAttribute("data-assigned")?e.getAttribute("data-assigned").split(","):[],e.getAttribute("data-members")?e.getAttribute("data-members").split(","):[])))}),Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(e=>{new bootstrap.Tooltip(e)});const H=Array.from(document.querySelectorAll(".kanban-tasks-item-dropdown"));H.length&&H.forEach(e=>{e.addEventListener("click",t=>{t.stopPropagation()})}),L&&L.addEventListener("click",()=>{k.forEach(e=>{e.value="",e.classList.toggle("d-none")})}),h&&h.append(v),I&&I.forEach(e=>{e.addEventListener("mouseenter",()=>{e.contentEditable="true"}),e.addEventListener("blur",t=>{const a=e.closest(".kanban-board").getAttribute("data-id"),r=t.target.textContent.trim();fetch("/kanban/update-board",{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({id:a,title:r})}).then(n=>n.json()).then(n=>{console.log("Board renamed:",n)}).catch(n=>console.error("Error renaming board:",n))}),e.insertAdjacentHTML("afterend",O())}),Array.from(document.querySelectorAll(".delete-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board").getAttribute("data-id");confirm("Tem certeza que deseja excluir este quadro? Todas as tarefas dele serão removidas.")&&fetch("/kanban/delete-board/"+t,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(a=>a.json()).then(()=>{m.removeBoard(t)}).catch(a=>console.error("Error deleting board:",a))})}),Array.from(document.querySelectorAll(".rename-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board-header").querySelector(".kanban-title-board");t.contentEditable="true",t.focus()})}),Array.from(document.querySelectorAll(".delete-task")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-item").getAttribute("data-eid");m.removeElement(t)})});const P=document.querySelector(".kanban-add-board-cancel-btn");P&&P.addEventListener("click",()=>{k.forEach(e=>{e.classList.toggle("d-none")})}),v&&v.addEventListener("submit",e=>{e.preventDefault();const t=e.target.querySelector(".form-control").value.trim();fetch("/kanban/add-board",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:t})}).then(a=>a.json()).then(a=>{const r=a.id,n=a.title;m.addBoards([{id:r,title:n}]);const s=document.querySelector(".kanban-board:last-child");if(s){const i=s.querySelector(".kanban-title-board");i.insertAdjacentHTML("afterend",O()),i.addEventListener("mouseenter",()=>{i.contentEditable="true"}),i.addEventListener("blur",o=>{const p=s.getAttribute("data-id"),S=o.target.textContent.trim();fetch("/kanban/update-board",{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({id:p,title:S})}).then(f=>f.json()).then(f=>console.log("Board renamed:",f)).catch(f=>console.error("Error renaming board:",f))});const c=s.querySelector(".dropdown-menu"),l=c.querySelector(".delete-board"),d=c.querySelector(".rename-board");l&&l.addEventListener("click",()=>{const o=s.getAttribute("data-id");confirm("Tem certeza que deseja excluir este quadro? Todas as tarefas dele serão removidas.")&&fetch("/kanban/delete-board/"+o,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(()=>m.removeBoard(o)).catch(p=>console.error("Error deleting board:",p))}),d&&d.addEventListener("click",()=>{i.contentEditable="true",i.focus()})}}),k.forEach(a=>{a.classList.add("d-none")}),h&&h.append(v)}),u.addEventListener("hidden.bs.offcanvas",()=>{const e=u.querySelector(".ql-editor").firstElementChild;e&&(e.innerHTML="")}),u&&u.addEventListener("shown.bs.offcanvas",()=>{Array.from(u.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(t=>{new bootstrap.Tooltip(t)})})});
