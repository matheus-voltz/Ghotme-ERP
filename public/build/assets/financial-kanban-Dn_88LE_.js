document.addEventListener("DOMContentLoaded",function(){document.querySelector(".kanban-wrapper")&&fetch(baseUrl+"finance/kanban/data").then(t=>t.json()).then(t=>{new jKanban({element:".kanban-wrapper",gutter:"15px",widthBoard:"250px",dragItems:!0,boards:t,addItemButton:!1,itemAddOptions:{enabled:!1},dropEl:function(e,a,s,c){const r=e.getAttribute("data-eid"),d=a.parentElement.getAttribute("data-id");fetch(baseUrl+"finance/kanban/update",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify({id:r,targetBoard:d})}).then(n=>n.json()).then(n=>{n.success})},itemRender:function(e){return`
                            <div class="kanban-item-title mb-2">
                                <span class="badge bg-label-${e.badge} mb-2">${e["badge-text"]}</span>
                                <h6 class="mb-1">${e.title}</h6>
                            </div>
                            <div class="kanban-item-footer d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="ti tabler-calendar me-1 fs-6"></i>
                                    <small class="text-muted">${e["due-date"]}</small>
                                </div>
                                ${e.client_whatsapp?`
                                    <a href="https://api.whatsapp.com/send?phone=55${e.client_whatsapp.replace(/\D/g,"")}" target="_blank" class="text-success">
                                        <i class="ti tabler-brand-whatsapp fs-5"></i>
                                    </a>
                                `:""}
                            </div>
                        `}}),document.querySelectorAll(".kanban-board").forEach(e=>{const a=e.getAttribute("data-id"),s={overdue:"danger",due_today:"warning",upcoming:"info",notified:"primary",received:"success"};e.classList.add("border-top","border-3",`border-${s[a]}`)})})});
