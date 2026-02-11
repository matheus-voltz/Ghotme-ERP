(async function(){let h;const d=document.querySelector(".kanban-update-item-sidebar"),y=document.querySelector(".kanban-wrapper"),A=document.querySelector(".comment-editor"),u=document.querySelector(".kanban-add-new-board"),f=[].slice.call(document.querySelectorAll(".kanban-add-board-input")),w=document.querySelector(".kanban-add-board-btn"),S=document.querySelector("#due-date"),v=$(".select2"),E=document.querySelector("html").getAttribute("data-assets-path"),q=new bootstrap.Offcanvas(d);let m=null;const k=await fetch("/kanban/data");k.ok||console.error("error",k),h=await k.json(),S&&S.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"j F, Y",dateFormat:"Y-m-d"});//! TODO: Update Event label and guest code to JS once select removes jQuery dependency
if(v.length){let e=function(t){if(!t.id)return t.text;var a="<div class='badge "+$(t.element).data("color")+"'> "+t.text+"</div>";return a};var X=e;v.each(function(){var t=$(this);t.wrap("<div class='position-relative'></div>").select2({placeholder:"Select Label",dropdownParent:t.parent(),templateResult:e,templateSelection:e,escapeMarkup:function(a){return a}})})}A&&new Quill(A,{modules:{toolbar:".comment-toolbar"},placeholder:"Write a Comment...",theme:"snow"});const T=()=>`
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
`,x=()=>`
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
`,I=(e,t)=>`
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${e}">${t}</div>
    </div>
    ${x()}
</div>
`,L=(e="",t=!1,a="",r="",o="")=>{const n=t?" pull-up":"",i=a?`avatar-${a}`:"",l=o?o.split(","):[];return e?e.split(",").map((g,s,b)=>{const H=r&&s!==b.length-1?` me-${r}`:"",M=l[s]||"";return`
            <div class="avatar ${i}${H} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${M}">
                <img src="${E}img/avatars/${g}"
                     alt="Avatar"
                     class="rounded-circle${n}">
            </div>
        `}).join(""):""},F=(e,t,a,r)=>`
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
        ${L(a,!0,"xs",null,r)}
    </div>
</div>
`,c=new jKanban({element:".kanban-wrapper",gutter:"12px",widthBoard:"250px",dragItems:!0,boards:h,dragBoards:!0,addItemButton:!0,buttonContent:"+ Add Item",itemAddOptions:{enabled:!0,content:"+ Add New Item",class:"kanban-title-button btn btn-default border-none",footer:!1},click:e=>{const t=e;m=t.getAttribute("data-eid");const a=t.getAttribute("data-eid")?t.querySelector(".kanban-text").textContent:t.textContent,r=t.getAttribute("data-due-date"),o=t.getAttribute("data-badge-text"),n=t.getAttribute("data-assigned");q.show(),d.querySelector("#title").value=a,r&&d.querySelector("#due-date")._flatpickr?d.querySelector("#due-date")._flatpickr.setDate(r):d.querySelector("#due-date").value=r||"",$(".kanban-update-item-sidebar").find(v).val(o).trigger("change"),d.querySelector(".assigned").innerHTML="",d.querySelector(".assigned").insertAdjacentHTML("afterbegin",`${L(n,!1,"xs","1",e.getAttribute("data-members"))}
        <div class="avatar avatar-xs ms-1">
            <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ti tabler-plus icon-xs text-heading"></i>
            </span>
        </div>`)},dropEl:(e,t,a,r)=>{const o=e.getAttribute("data-eid"),n=t.closest(".kanban-board").getAttribute("data-id");fetch("/kanban/move-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({itemId:o,targetBoardId:n})}).then(i=>i.json()).catch(i=>console.error("Error moving item:",i))},buttonClick:(e,t)=>{const a=document.createElement("form");a.setAttribute("class","new-item-form"),a.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Add Content" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Add</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancel</button>
        </div>
      `,c.addForm(t,a),a.addEventListener("submit",r=>{r.preventDefault(),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));const o=r.target[0].value;fetch("/kanban/add-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({boardId:t,title:o})}).then(n=>n.json()).then(n=>{c.addElement(t,{title:`<span class="kanban-text">${o}</span>`,id:n.id}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(s=>{(!s.previousElementSibling||!s.previousElementSibling.classList.contains("kanban-tasks-item-dropdown"))&&s.insertAdjacentHTML("beforebegin",x())}),Array.from(document.querySelectorAll(".kanban-item .kanban-tasks-item-dropdown")).forEach(s=>{s.addEventListener("click",b=>b.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(s=>{s.addEventListener("click",()=>{const b=s.closest(".kanban-item").getAttribute("data-eid");c.removeElement(b)})}),a.remove()})}),a.querySelector(".cancel-add-item").addEventListener("click",()=>a.remove())}});y&&new PerfectScrollbar(y);const j=document.querySelector(".kanban-update-item-sidebar .btn-primary");j&&j.addEventListener("click",()=>{var o;const e=document.querySelector("#title").value,t=document.querySelector("#due-date").value,a=$("#label").val(),r=((o=$("#label option:selected").data("color"))==null?void 0:o.replace("bg-label-",""))||"success";fetch("/kanban/update-item/"+m,{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:e,dueDate:t,badgeText:a,badgeColor:r})}).then(n=>n.json()).then(n=>{location.reload()}).catch(n=>console.error("Error updating item:",n))});const C=document.querySelector(".kanban-update-item-sidebar .btn-label-danger");C&&C.addEventListener("click",()=>{confirm("Tem certeza que deseja excluir esta tarefa?")&&fetch("/kanban/delete-item/"+m,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(e=>{c.removeElement(m),q.hide()}).catch(e=>console.error("Error deleting item:",e))});const p=document.querySelector(".kanban-container"),B=Array.from(document.querySelectorAll(".kanban-title-board")),N=Array.from(document.querySelectorAll(".kanban-item"));N.length&&N.forEach(e=>{const t=`<span class="kanban-text">${e.textContent}</span>`;let a="";e.getAttribute("data-image")&&(a=`
              <img class="img-fluid rounded mb-2"
                   src="${E}img/elements/${e.getAttribute("data-image")}">
          `),e.textContent="",e.getAttribute("data-badge")&&e.getAttribute("data-badge-text")&&e.insertAdjacentHTML("afterbegin",`${I(e.getAttribute("data-badge"),e.getAttribute("data-badge-text"))}${a}${t}`),(e.getAttribute("data-comments")||e.getAttribute("data-due-date")||e.getAttribute("data-assigned"))&&e.insertAdjacentHTML("beforeend",F(e.getAttribute("data-attachments")||0,e.getAttribute("data-comments")||0,e.getAttribute("data-assigned")||"",e.getAttribute("data-members")||""))}),Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(e=>{new bootstrap.Tooltip(e)});const O=Array.from(document.querySelectorAll(".kanban-tasks-item-dropdown"));O.length&&O.forEach(e=>{e.addEventListener("click",t=>{t.stopPropagation()})}),w&&w.addEventListener("click",()=>{f.forEach(e=>{e.value="",e.classList.toggle("d-none")})}),p&&p.append(u),B&&B.forEach(e=>{e.addEventListener("mouseenter",()=>{e.contentEditable="true"}),e.insertAdjacentHTML("afterend",T())}),Array.from(document.querySelectorAll(".delete-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board").getAttribute("data-id");c.removeBoard(t)})}),Array.from(document.querySelectorAll(".delete-task")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-item").getAttribute("data-eid");c.removeElement(t)})});const D=document.querySelector(".kanban-add-board-cancel-btn");D&&D.addEventListener("click",()=>{f.forEach(e=>{e.classList.toggle("d-none")})}),u&&u.addEventListener("submit",e=>{e.preventDefault();const t=e.target.querySelector(".form-control").value.trim();fetch("/kanban/add-board",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({title:t})}).then(a=>a.json()).then(a=>{const r=a.id,o=a.title;c.addBoards([{id:r,title:o}]);const n=document.querySelector(".kanban-board:last-child");if(n){const i=n.querySelector(".kanban-title-board");i.insertAdjacentHTML("afterend",T()),i.addEventListener("mouseenter",()=>{i.contentEditable="true"});const l=n.querySelector(".delete-board");l&&l.addEventListener("click",()=>{const g=l.closest(".kanban-board").getAttribute("data-id");c.removeBoard(g)})}}),f.forEach(a=>{a.classList.add("d-none")}),p&&p.append(u)}),d.addEventListener("hidden.bs.offcanvas",()=>{const e=d.querySelector(".ql-editor").firstElementChild;e&&(e.innerHTML="")}),d&&d.addEventListener("shown.bs.offcanvas",()=>{Array.from(d.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(t=>{new bootstrap.Tooltip(t)})})})();
