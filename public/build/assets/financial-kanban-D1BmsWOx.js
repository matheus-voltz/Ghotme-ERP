document.addEventListener("DOMContentLoaded",function(){const p=document.querySelector(".kanban-wrapper"),a=document.querySelector(".kanban-update-item-sidebar"),s=a?new bootstrap.Offcanvas(a):null;let l,c=null;if(p){const o=document.querySelector("#due-date");o&&o.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"d/m/Y",dateFormat:"Y-m-d"}),typeof $<"u"&&$(".select2").each(function(){var r=$(this);r.wrap("<div class='position-relative'></div>").select2({placeholder:"Selecionar",dropdownParent:r.parent()})}),fetch(baseUrl+"finance/kanban/data").then(r=>r.json()).then(r=>{l=new jKanban({element:".kanban-wrapper",gutter:"15px",widthBoard:"250px",dragItems:!0,boards:r,addItemButton:!0,buttonContent:"+ Adicionar Recebível",itemAddOptions:{enabled:!0,content:"+ Adicionar Novo",class:"kanban-title-button btn btn-default border-none",footer:!1},click:function(e){c=e.getAttribute("data-eid");const t=e;if(a){a.querySelector("#title").value=t.getAttribute("data-pure_title")||"",a.querySelector("#amount").value=t.getAttribute("data-amount")||"",a.querySelector("#category").value=t.getAttribute("data-category")||"";const n=t.getAttribute("data-due-date");n&&a.querySelector("#due-date")._flatpickr&&a.querySelector("#due-date")._flatpickr.setDate(n);const d=t.getAttribute("data-client_id");typeof $<"u"&&$("#select-client").val(d).trigger("change"),s.show()}},buttonClick:function(e,t){const n=document.createElement("form");n.setAttribute("class","new-item-form p-2"),n.innerHTML=`
                            <div class="mb-3">
                                <textarea class="form-control add-new-item" rows="2" placeholder="Ex: Venda de Item 50.00" autofocus required></textarea>
                            </div>
                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary btn-sm me-2">Adicionar</button>
                                <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item">Cancelar</button>
                            </div>
                        `,l.addForm(t,n),n.addEventListener("submit",d=>{d.preventDefault();const u=d.target[0].value;fetch(baseUrl+"finance/kanban/add-item",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({boardId:t,title:u})}).then(i=>i.json()).then(i=>{location.reload()})}),n.querySelector(".cancel-add-item").addEventListener("click",()=>n.remove())},dropEl:function(e,t,n,d){const u=e.getAttribute("data-eid"),i=t.parentElement.getAttribute("data-id");fetch(baseUrl+"finance/kanban/update",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({id:u,targetBoard:i})}).then(f=>f.json())},itemRender:function(e){const t=typeof moment<"u"?moment(e["due-date"]).format("DD/MM/YYYY"):e["due-date"];return`
                            <div class="kanban-item-title mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-label-${e.badge} rounded-pill px-3 py-1">${e["badge-text"]}</span>
                                    <span class="text-muted fw-medium small">#${e.id}</span>
                                </div>
                                <h6 class="mb-1 text-dark" style="letter-spacing: -0.01em;">${e.title}</h6>
                                ${e.description?`<p class="text-muted small mb-0 mt-2">${e.description.substring(0,50)}${e.description.length>50?"...":""}</p>`:""}
                            </div>
                            <div class="kanban-item-footer border-top pt-3 d-flex justify-content-between align-items-center mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-light rounded p-2 d-flex align-items-center">
                                        <i class="ti tabler-calendar fs-5 me-1 text-secondary"></i>
                                        <small class="fw-bold text-dark">${t}</small>
                                    </div>
                                    <a href="${baseUrl}finance/transaction/${e.id}/pdf" target="_blank" class="btn btn-icon btn-sm btn-label-secondary border-0" title="Ver Fatura" onclick="event.stopPropagation();">
                                        <i class="ti tabler-file-text fs-4"></i>
                                    </a>
                                </div>
                                ${e.client_whatsapp?`
                                    <a href="https://api.whatsapp.com/send?phone=55${e.client_whatsapp.replace(/\D/g,"")}" 
                                       target="_blank" 
                                       class="btn btn-icon btn-sm btn-label-success border-0 shadow-sm pulse-button"
                                       title="Enviar Cobrança"
                                       onclick="event.stopPropagation();">
                                        <i class="ti tabler-brand-whatsapp fs-4"></i>
                                    </a>
                                `:""}
                            </div>
                        `}}),document.querySelectorAll(".kanban-board").forEach(e=>{const t=e.getAttribute("data-id"),n={overdue:"danger",due_today:"warning",upcoming:"info",notified:"primary",received:"success"};e.classList.add("border-top","border-3",`border-${n[t]}`)})})}const b=document.querySelector(".btn-update-transaction");b&&b.addEventListener("click",()=>{const o={title:a.querySelector("#title").value,amount:a.querySelector("#amount").value,dueDate:a.querySelector("#due-date").value,clientId:typeof $<"u"?$("#select-client").val():null,category:a.querySelector("#category").value};fetch(baseUrl+"finance/kanban/update-item/"+c,{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify(o)}).then(r=>r.json()).then(()=>{location.reload()})});const m=document.querySelector(".btn-delete-transaction");m&&m.addEventListener("click",()=>{confirm("Deseja realmente excluir este lançamento?")&&fetch(baseUrl+"finance/kanban/delete-item/"+c,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(o=>o.json()).then(()=>{l.removeElement(c),s&&s.hide()})})});
