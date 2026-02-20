/**
 * Financial Kanban Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const kanbanWrapper = document.querySelector('.kanban-wrapper');

    if (kanbanWrapper) {
        // Fetch data from API
        fetch(baseUrl + 'finance/kanban/data')
            .then(res => res.json())
            .then(boards => {
                const kanban = new jKanban({
                    element: '.kanban-wrapper',
                    gutter: '15px',
                    widthBoard: '250px',
                    dragItems: true,
                    boards: boards,
                    addItemButton: false,
                    itemAddOptions: {
                        enabled: false
                    },
                    dropEl: function (el, target, source, sibling) {
                        const transactionId = el.getAttribute('data-eid');
                        const targetBoard = target.parentElement.getAttribute('data-id');

                        // Update status in backend
                        fetch(baseUrl + 'finance/kanban/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                id: transactionId,
                                targetBoard: targetBoard
                            })
                        }).then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Opcional: Mostrar um toast de sucesso
                            }
                        });
                    },
                    itemRender: function (item) {
                        // Custom rendering for finance cards
                        return `
                            <div class="kanban-item-title mb-2">
                                <span class="badge bg-label-${item.badge} mb-2">${item['badge-text']}</span>
                                <h6 class="mb-1">${item.title}</h6>
                            </div>
                            <div class="kanban-item-footer d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti tabler-calendar me-1 fs-6"></i>
                                    <small class="text-muted">${item['due-date']}</small>
                                    <a href="${baseUrl}finance/transaction/${item.id}/pdf" target="_blank" class="text-secondary" title="Ver PDF">
                                        <i class="ti tabler-file-text fs-5"></i>
                                    </a>
                                </div>
                                ${item.client_whatsapp ? `
                                    <a href="https://api.whatsapp.com/send?phone=55${item.client_whatsapp.replace(/\D/g, '')}" target="_blank" class="text-success">
                                        <i class="ti tabler-brand-whatsapp fs-5"></i>
                                    </a>
                                ` : ''}
                            </div>
                        `;
                    }
                });

                // Styling adjustments after render
                const kanbanBoards = document.querySelectorAll('.kanban-board');
                kanbanBoards.forEach(board => {
                    const boardId = board.getAttribute('data-id');
                    const colorMap = {
                        overdue: 'danger',
                        due_today: 'warning',
                        upcoming: 'info',
                        notified: 'primary',
                        received: 'success'
                    };
                    board.classList.add('border-top', 'border-3', `border-${colorMap[boardId]}`);
                });
            });
    }
});
