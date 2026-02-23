document.addEventListener("DOMContentLoaded",async function(){let w;const i=document.querySelector(".kanban-update-item-sidebar"),S=document.querySelector(".kanban-wrapper"),E=document.querySelector(".comment-editor"),g=document.querySelector(".kanban-add-new-board"),v=[].slice.call(document.querySelectorAll(".kanban-add-board-input")),x=document.querySelector(".kanban-add-board-btn"),T=document.querySelector("#due-date"),q=$(".select2"),h=document.querySelector("html").getAttribute("data-assets-path");let k=[];const y=await fetch("/kanban/users");y.ok?(k=await y.json(),console.log("Kanban Users Loaded:",k)):console.error("Failed to fetch Kanban users:",y.status);const L=new bootstrap.Offcanvas(i);let p=null;const A=await fetch("/kanban/data");A.ok||console.error("error",A),w=await A.json(),T&&T.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"j F, Y",dateFormat:"Y-m-d"});//! TODO: Update Event label and guest code to JS once select removes jQuery dependency
if(q.length){let e=function(a){if(!a.id)return a.text;var r="<div class='badge "+$(a.element).data("color")+"'> "+a.text+"</div>";return r},t=function(a){if(!a.id)return a.text;var r=$(a.element).attr("data-avatar")||"",s='<div class="d-flex align-items-center"><div class="avatar avatar-xs me-2"><img src="'+(r||h+"img/avatars/1.png")+'" alt="Avatar" class="rounded-circle"></div><span>'+a.text+"</span></div>";return s};var z=e,Q=t;const n=$(".select2-users");n.length&&k.forEach(a=>{const r=`<option value="${a.id}" data-avatar="${a.avatar}">${a.name}</option>`;n.append(r)}),q.each(function(){var a=$(this);a.wrap("<div class='position-relative'></div>").select2({placeholder:"Selecionar",dropdownParent:a.parent(),templateResult:a.hasClass("select2-users")?t:e,templateSelection:a.hasClass("select2-users")?t:e,escapeMarkup:function(r){return r}})})}let m;E&&(m=new Quill(E,{modules:{toolbar:".comment-toolbar"},placeholder:"Escreva um comentário...",theme:"snow"}));const j=()=>`
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
              <span class="align-middle">Delete</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-edit icon-xs"></i>
              <span class="align-middle">Rename</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-archive icon-xs"></i>
              <span class="align-middle">Archive</span>
          </a>
      </div>
  </div>
`,C=()=>`
<div class="dropdown kanban-tasks-item-dropdown">
    <i class="dropdown-toggle icon-base ti tabler-dots-vertical"
       id="kanban-tasks-item-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
    </i>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kanban-tasks-item-dropdown">
        <a class="dropdown-item" href="javascript:void(0)">Copy task link</a>
        <a class="dropdown-item" href="javascript:void(0)">Duplicate task</a>
        <a class="dropdown-item delete-task" href="javascript:void(0)">Delete</a>
    </div>
</div>
`,H=(e,t)=>`
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${e}">${t}</div>
    </div>
    ${C()}
</div>
`,P=(e=[],t=!1,n="",a="",r=[])=>{const s=t?" pull-up":"",d=n?`avatar-${n}`:"",l=Array.isArray(r)?r:r?r.split(","):[],c=Array.isArray(e)?e:e?e.split(","):[];return c.length>0?c.map((o,b,_)=>{const X=a&&b!==_.length-1?` me-${a}`:"",J=l[b]||"";let F=o;return!o.startsWith("http")&&!o.startsWith("/")&&!o.startsWith("data:")&&(F=h+"img/avatars/"+o),`
            <div class="avatar ${d}${X} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${J}">
                <img src="${F}"
                     alt="Avatar"
                     class="rounded-circle${s}">
            </div>
        `}).join(""):""},R=async e=>{const t=document.querySelector(".activities-container");if(t){t.innerHTML='<div class="text-center p-4"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>';try{const n=await fetch(`/kanban/item/${e}/activities`);if(!n.ok)throw new Error("Falha ao carregar histórico");const a=await n.json();if(a.length===0){t.innerHTML='<div class="text-center p-4 text-muted">Nenhuma atividade registrada ainda.</div>';return}t.innerHTML=a.map(r=>`
        <div class="media mb-4 d-flex align-items-center">
          <div class="avatar me-3 flex-shrink-0">
            <img src="${r.user_avatar}" alt="Avatar" class="rounded-circle" />
          </div>
          <div class="media-body">
            <p class="mb-0"><span>${r.user_name}</span> ${r.description}</p>
            <small class="text-body-secondary">${r.time_ago}</small>
          </div>
        </div>
      `).join("")}catch(n){console.error(n),t.innerHTML='<div class="text-center p-4 text-danger">Erro ao carregar histórico.</div>'}}},K=(e,t,n,a)=>`
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
`,u=new jKanban({element:".kanban-wrapper",gutter:"12px",widthBoard:"250px",dragItems:!0,boards:w.map(e=>(e.item=e.item.map(t=>(t["assigned-ids"]=t.assigned_ids?t.assigned_ids.join(","):"",t)),e)),dragBoards:!0,addItemButton:!0,buttonContent:"+ Add Item",itemAddOptions:{enabled:!0,content:"+ Add New Item",class:"kanban-title-button btn btn-default border-none",footer:!1},click:e=>{const t=e;p=t.getAttribute("data-eid");const n=t.getAttribute("data-eid")?t.querySelector(".kanban-text").textContent:t.textContent,a=t.getAttribute("data-due-date"),r=t.getAttribute("data-badge-text");t.getAttribute("data-assigned"),L.show(),i.querySelector("#title").value=n,a&&i.querySelector("#due-date")._flatpickr?i.querySelector("#due-date")._flatpickr.setDate(a):i.querySelector("#due-date").value=a||"",$(".kanban-update-item-sidebar").find("#label").val(r).trigger("change");const s=t.getAttribute("data-assigned-ids");s?$("#select2-users").val(s.split(",")).trigger("change"):$("#select2-users").val(null).trigger("change"),R(p)},dropEl:(e,t,n,a)=>{const r=e.getAttribute("data-eid"),s=t.closest(".kanban-board").getAttribute("data-id");fetch("/kanban/move-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({itemId:r,targetBoardId:s})}).then(d=>d.json()).catch(d=>console.error("Error moving item:",d))},buttonClick:(e,t)=>{const n=document.createElement("form");n.setAttribute("class","new-item-form"),n.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Add Content" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Add</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancel</button>
        </div>
      `,u.addForm(t,n),n.addEventListener("submit",a=>{a.preventDefault(),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));const r=a.target[0].value;fetch("/kanban/add-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({boardId:t,title:r})}).then(s=>s.json()).then(s=>{u.addElement(t,{title:`<span class="kanban-text">${r}</span>`,id:s.id}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(o=>{(!o.previousElementSibling||!o.previousElementSibling.classList.contains("kanban-tasks-item-dropdown"))&&o.insertAdjacentHTML("beforebegin",C())}),Array.from(document.querySelectorAll(".kanban-item .kanban-tasks-item-dropdown")).forEach(o=>{o.addEventListener("click",b=>b.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(o=>{o.addEventListener("click",()=>{const b=o.closest(".kanban-item").getAttribute("data-eid");u.removeElement(b)})}),n.remove()})}),n.querySelector(".cancel-add-item").addEventListener("click",()=>n.remove())}});S&&new PerfectScrollbar(S);const B=document.querySelector(".kanban-update-item-sidebar .btn-primary");B&&B.addEventListener("click",()=>{var l;const e=document.querySelector("#title").value,t=document.querySelector("#due-date").value,n=$("#label").val(),a=((l=$("#label option:selected").data("color"))==null?void 0:l.replace("bg-label-",""))||"success",r=$("#select2-users").val(),s=m?m.root.innerHTML:"",d=m?m.getText().trim().length===0:!0;fetch("/kanban/update-item/"+p,{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:e,dueDate:t,badgeText:n,badgeColor:a,assignedTo:r,comment:d?null:s})}).then(c=>c.json()).then(c=>{m&&m.setContents([]),location.reload()}).catch(c=>console.error("Error updating item:",c))});const N=document.querySelector(".kanban-update-item-sidebar .btn-label-danger");N&&N.addEventListener("click",()=>{confirm("Tem certeza que deseja excluir esta tarefa?")&&fetch("/kanban/delete-item/"+p,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(e=>{u.removeElement(p),L.hide()}).catch(e=>console.error("Error deleting item:",e))});const f=document.querySelector(".kanban-container"),O=Array.from(document.querySelectorAll(".kanban-title-board")),D=Array.from(document.querySelectorAll(".kanban-item"));D.length&&D.forEach(e=>{const t=`<span class="kanban-text">${e.textContent}</span>`;let n="";e.getAttribute("data-image")&&(n=`
              <img class="img-fluid rounded mb-2"
                   src="${h}img/elements/${e.getAttribute("data-image")}">
          `),e.textContent="",e.getAttribute("data-badge")&&e.getAttribute("data-badge-text")&&e.insertAdjacentHTML("afterbegin",`${H(e.getAttribute("data-badge"),e.getAttribute("data-badge-text"))}${n}${t}`),(e.getAttribute("data-comments")||e.getAttribute("data-due-date")||e.getAttribute("data-assigned"))&&e.insertAdjacentHTML("beforeend",K(e.getAttribute("data-attachments")||0,e.getAttribute("data-comments")||0,e.getAttribute("data-assigned")?e.getAttribute("data-assigned").split(","):[],e.getAttribute("data-members")?e.getAttribute("data-members").split(","):[]))}),Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(e=>{new bootstrap.Tooltip(e)});const I=Array.from(document.querySelectorAll(".kanban-tasks-item-dropdown"));I.length&&I.forEach(e=>{e.addEventListener("click",t=>{t.stopPropagation()})}),x&&x.addEventListener("click",()=>{v.forEach(e=>{e.value="",e.classList.toggle("d-none")})}),f&&f.append(g),O&&O.forEach(e=>{e.addEventListener("mouseenter",()=>{e.contentEditable="true"}),e.insertAdjacentHTML("afterend",j())}),Array.from(document.querySelectorAll(".delete-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board").getAttribute("data-id");u.removeBoard(t)})}),Array.from(document.querySelectorAll(".delete-task")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-item").getAttribute("data-eid");u.removeElement(t)})});const M=document.querySelector(".kanban-add-board-cancel-btn");M&&M.addEventListener("click",()=>{v.forEach(e=>{e.classList.toggle("d-none")})}),g&&g.addEventListener("submit",e=>{e.preventDefault();const t=e.target.querySelector(".form-control").value.trim();fetch("/kanban/add-board",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:t})}).then(n=>n.json()).then(n=>{const a=n.id,r=n.title;u.addBoards([{id:a,title:r}]);const s=document.querySelector(".kanban-board:last-child");if(s){const d=s.querySelector(".kanban-title-board");d.insertAdjacentHTML("afterend",j()),d.addEventListener("mouseenter",()=>{d.contentEditable="true"});const l=s.querySelector(".delete-board");l&&l.addEventListener("click",()=>{const c=l.closest(".kanban-board").getAttribute("data-id");u.removeBoard(c)})}}),v.forEach(n=>{n.classList.add("d-none")}),f&&f.append(g)}),i.addEventListener("hidden.bs.offcanvas",()=>{const e=i.querySelector(".ql-editor").firstElementChild;e&&(e.innerHTML="")}),i&&i.addEventListener("shown.bs.offcanvas",()=>{Array.from(i.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(t=>{new bootstrap.Tooltip(t)})})});
