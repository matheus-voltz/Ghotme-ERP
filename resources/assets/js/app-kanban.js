/**
 * App Kanban
 */

'use strict';

document.addEventListener('DOMContentLoaded', async function () {
  let boards;
  const kanbanSidebar = document.querySelector('.kanban-update-item-sidebar'),
    kanbanWrapper = document.querySelector('.kanban-wrapper'),
    commentEditor = document.querySelector('.comment-editor'),
    kanbanAddNewBoard = document.querySelector('.kanban-add-new-board'),
    kanbanAddNewInput = [].slice.call(document.querySelectorAll('.kanban-add-board-input')),
    kanbanAddBoardBtn = document.querySelector('.kanban-add-board-btn'),
    datePicker = document.querySelector('#due-date'),
    select2 = $('.select2'), // ! Using jquery vars due to select2 jQuery dependency
    assetsPath = document.querySelector('html').getAttribute('data-assets-path');

  let allUsers = [];

  // Fetch company users
  const usersResponse = await fetch('/kanban/users');
  if (usersResponse.ok) {
    allUsers = await usersResponse.json();
    console.log('Kanban Users Loaded:', allUsers);
  } else {
    console.error('Failed to fetch Kanban users:', usersResponse.status);
  }

  // Init kanban Offcanvas
  const kanbanOffcanvas = new bootstrap.Offcanvas(kanbanSidebar);
  let currentItemId = null; // Store ID for updates/deletion

  // Get kanban data
  const kanbanResponse = await fetch('/kanban/data');
  if (!kanbanResponse.ok) {
    console.error('error', kanbanResponse);
  }
  boards = await kanbanResponse.json();

  // datepicker init
  if (datePicker) {
    datePicker.flatpickr({
      monthSelectorType: 'static',
      static: true,
      altInput: true,
      altFormat: 'j F, Y',
      dateFormat: 'Y-m-d'
    });
  }

  //! TODO: Update Event label and guest code to JS once select removes jQuery dependency
  // select2
  if (select2.length) {
    function renderLabels(option) {
      if (!option.id) {
        return option.text;
      }
      var $badge = "<div class='badge " + $(option.element).data('color') + "'> " + option.text + '</div>';
      return $badge;
    }

    function renderUsers(option) {
      if (!option.id) {
        return option.text;
      }
      var avatar = $(option.element).attr('data-avatar') || '';
      var $user =
        '<div class="d-flex align-items-center">' +
        '<div class="avatar avatar-xs me-2">' +
        '<img src="' + (avatar || assetsPath + 'img/avatars/1.png') + '" alt="Avatar" class="rounded-circle">' +
        '</div>' +
        '<span>' + option.text + '</span>' +
        '</div>';
      return $user;
    }

    // Populate Users Select
    const userSelect = $('.select2-users');
    if (userSelect.length) {
      allUsers.forEach(user => {
        const option = `<option value="${user.id}" data-avatar="${user.avatar}">${user.name}</option>`;
        userSelect.append(option);
      });
    }

    select2.each(function () {
      var $this = $(this);
      $this.wrap("<div class='position-relative'></div>").select2({
        placeholder: 'Selecionar',
        dropdownParent: $this.parent(),
        templateResult: $this.hasClass('select2-users') ? renderUsers : renderLabels,
        templateSelection: $this.hasClass('select2-users') ? renderUsers : renderLabels,
        escapeMarkup: function (es) {
          return es;
        }
      });
    });
  }

  // Comment editor
  let quillEditor;
  if (commentEditor) {
    quillEditor = new Quill(commentEditor, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Escreva um comentário...',
      theme: 'snow'
    });
  }

  // Render board dropdown
  const renderBoardDropdown = () => `
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
`;
  // Render item dropdown
  const renderDropdown = () => `
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
`;

  // Render header
  const renderHeader = (color, text) => `
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${color}">${text}</div>
    </div>
    ${renderDropdown()}
</div>
`;

  // Render avatar
  const renderAvatar = (images = [], pullUp = false, size = '', margin = '', members = []) => {
    const transitionClass = pullUp ? ' pull-up' : '';
    const sizeClass = size ? `avatar-${size}` : '';
    const memberList = Array.isArray(members) ? members : (members ? members.split(',') : []);
    const imgList = Array.isArray(images) ? images : (images ? images.split(',') : []);

    return imgList.length > 0
      ? imgList
        .map((img, index, arr) => {
          const marginClass = margin && index !== arr.length - 1 ? ` me-${margin}` : '';
          const memberName = memberList[index] || '';
          let imgSrc = img;
          if (!img.startsWith('http') && !img.startsWith('/') && !img.startsWith('data:')) {
            imgSrc = assetsPath + 'img/avatars/' + img;
          }
          return `
            <div class="avatar ${sizeClass}${marginClass} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${memberName}">
                <img src="${imgSrc}"
                     alt="Avatar"
                     class="rounded-circle${transitionClass}">
            </div>
        `;
        })
        .join('')
      : '';
  };

  // Load and Render Activities
  const loadActivities = async (itemId) => {
    const container = document.querySelector('.activities-container');
    if (!container) return;

    container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>';

    console.log('Fetching activities for Item ID:', itemId);
    try {
      const response = await fetch(`/kanban/item/${itemId}/activities`);
      if (!response.ok) throw new Error('Falha ao carregar histórico');

      const activities = await response.json();

      if (activities.length === 0) {
        container.innerHTML = '<div class="text-center p-4 text-muted">Nenhuma atividade registrada ainda.</div>';
        return;
      }

      container.innerHTML = activities.map(activity => {
        let details = '';
        if (activity.type === 'comment' && activity.extra_data?.text) {
          details = `<div class="mt-2 p-2 bg-light rounded text-body">${activity.extra_data.text}</div>`;
        } else if (activity.type === 'attachment' && activity.extra_data?.files) {
          details = `<div class="mt-2">
            ${activity.extra_data.files.map(file => `
              <a href="/storage/${file.path}" target="_blank" class="d-block text-primary mb-1">
                <i class="ti tabler-file-download me-1"></i>${file.name}
              </a>
            `).join('')}
          </div>`;
        }

        return `
          <div class="media mb-4 d-flex align-items-start">
            <div class="avatar me-3 flex-shrink-0">
              <img src="${activity.user_avatar}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body w-100">
              <p class="mb-0 pt-1"><span>${activity.user_name}</span> ${activity.description}</p>
              ${details}
              <small class="text-body-secondary">${activity.time_ago}</small>
            </div>
          </div>
        `;
      }).join('');

    } catch (error) {
      console.error(error);
      container.innerHTML = '<div class="text-center p-4 text-danger">Erro ao carregar histórico.</div>';
    }
  };

  // Render footer
  const renderFooter = (attachments, comments, assigned, members) => `
<div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
    <div class="d-flex">
        <span class="d-flex align-items-center me-2">
            <i class="icon-base ti tabler-paperclip me-1"></i>
            <span class="attachments">${attachments}</span>
        </span>
        <span class="d-flex align-items-center ms-2">
            <i class="icon-base ti tabler-message-2 me-1"></i>
            <span>${comments}</span>
        </span>
    </div>
    <div class="avatar-group d-flex align-items-center assigned-avatar">
        ${renderAvatar(assigned, true, 'xs', null, members)}
    </div>
</div>
`;

  // Initialize kanban
  const kanban = new jKanban({
    element: '.kanban-wrapper',
    gutter: '12px',
    widthBoard: '250px',
    dragItems: true,
    boards: boards.map(board => {
      board.item = board.item.map(item => {
        // Store assigned_ids in a data attribute for easier access
        item['assigned-ids'] = item.assigned_ids ? item.assigned_ids.join(',') : '';
        return item;
      });
      return board;
    }),
    dragBoards: true,
    addItemButton: true,
    buttonContent: '+ Adicionar Item',
    itemAddOptions: {
      enabled: true,
      content: '+ Adicionar Novo Item',
      class: 'kanban-title-button btn btn-default border-none',
      footer: false
    },
    click: el => {
      const element = el;
      currentItemId = element.getAttribute('data-eid');
      const title = element.getAttribute('data-eid')
        ? element.querySelector('.kanban-text').textContent
        : element.textContent;
      const date = element.getAttribute('data-due-date');
      const label = element.getAttribute('data-badge-text');
      const avatars = element.getAttribute('data-assigned');

      // Show kanban offcanvas
      kanbanOffcanvas.show();

      // Populate sidebar fields
      kanbanSidebar.querySelector('#title').value = title;
      if (date && kanbanSidebar.querySelector('#due-date')._flatpickr) {
        kanbanSidebar.querySelector('#due-date')._flatpickr.setDate(date);
      } else {
        kanbanSidebar.querySelector('#due-date').value = date || '';
      }

      // Using jQuery for select2
      $('.kanban-update-item-sidebar').find('#label').val(label).trigger('change');

      // Populate assigned users in Select2
      const assignedIds = element.getAttribute('data-assigned-ids');
      if (assignedIds) {
        $('#select2-users').val(assignedIds.split(',')).trigger('change');
      } else {
        $('#select2-users').val(null).trigger('change');
      }

      // Load activities
      loadActivities(currentItemId);
    },

    dropEl: (el, target, source, sibling) => {
      const itemId = el.getAttribute('data-eid');
      const targetBoardId = target.closest('.kanban-board').getAttribute('data-id');

      fetch('/kanban/move-item', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ itemId: itemId, targetBoardId: targetBoardId })
      })
        .then(response => response.json())
        .catch(error => console.error('Error moving item:', error));
    },

    buttonClick: (el, boardId) => {
      const addNewForm = document.createElement('form');
      addNewForm.setAttribute('class', 'new-item-form');
      addNewForm.innerHTML = `
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Adicionar Conteúdo" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Adicionar</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancelar</button>
        </div>
      `;

      kanban.addForm(boardId, addNewForm);

      addNewForm.addEventListener('submit', e => {
        e.preventDefault();
        const currentBoard = Array.from(document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .kanban-item`));
        const titleText = e.target[0].value;

        // API Call
        fetch('/kanban/add-item', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ boardId: boardId, title: titleText })
        })
          .then(response => response.json())
          .then(data => {
            kanban.addElement(boardId, {
              title: `<span class="kanban-text">${titleText}</span>`,
              id: data.id // Use ID from database
            });

            // Add dropdown to new tasks
            const kanbanTextElements = Array.from(
              document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .kanban-text`)
            );
            kanbanTextElements.forEach(textElem => {
              if (!textElem.previousElementSibling || !textElem.previousElementSibling.classList.contains('kanban-tasks-item-dropdown')) {
                textElem.insertAdjacentHTML('beforebegin', renderDropdown());
              }
            });

            // Prevent sidebar from opening on dropdown click
            const newTaskDropdowns = Array.from(document.querySelectorAll('.kanban-item .kanban-tasks-item-dropdown'));
            newTaskDropdowns.forEach(dropdown => {
              dropdown.addEventListener('click', event => event.stopPropagation());
            });

            // Add delete functionality for new tasks
            const deleteTaskButtons = Array.from(
              document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .delete-task`)
            );
            deleteTaskButtons.forEach(btn => {
              btn.addEventListener('click', () => {
                const taskId = btn.closest('.kanban-item').getAttribute('data-eid');
                kanban.removeElement(taskId);
              });
            });

            addNewForm.remove();
          });
      });

      // Remove form on clicking cancel button
      addNewForm.querySelector('.cancel-add-item').addEventListener('click', () => addNewForm.remove());
    }
  });

  // Kanban Wrapper scrollbar
  if (kanbanWrapper) {
    new PerfectScrollbar(kanbanWrapper);
  }

  // Sidebar Update Button Logic
  const updateItemBtn = document.querySelector('.kanban-update-item-sidebar .btn-primary');
  if (updateItemBtn) {
    updateItemBtn.addEventListener('click', () => {
      const title = document.querySelector('#title').value;
      const dueDate = document.querySelector('#due-date').value;
      const label = $('#label').val();
      const badgeColor = $('#label option:selected').data('color')?.replace('bg-label-', '') || 'success';
      const assignedTo = $('#select2-users').val(); // Array de IDs
      const comment = quillEditor ? quillEditor.root.innerHTML : '';
      const isCommentEmpty = quillEditor ? (quillEditor.getText().trim().length === 0) : true;

      const formData = new FormData();
      formData.append('_method', 'PUT');
      formData.append('title', title);
      formData.append('dueDate', dueDate);
      formData.append('badgeText', label || '');
      formData.append('badgeColor', badgeColor);
      formData.append('comment', isCommentEmpty ? '' : comment);

      if (assignedTo) {
        assignedTo.forEach(id => formData.append('assignedTo[]', id));
      }

      const fileInput = document.querySelector('#attachments');
      if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
          formData.append('attachments[]', fileInput.files[i]);
        }
      }

      fetch('/kanban/update-item/' + currentItemId, {
        method: 'POST', // Usamos POST com _method=PUT para suporte a arquivos
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
      })
        .then(async response => {
          if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Erro ao atualizar item');
          }
          return response.json();
        })
        .then(data => {
          if (quillEditor) quillEditor.setContents([]); // Clear editor
          location.reload();
        })
        .catch(error => {
          alert(error.message);
          console.error('Error updating item:', error);
        });
    });
  }

  // Sidebar Delete Button Logic
  const deleteItemBtn = document.querySelector('.kanban-update-item-sidebar .btn-label-danger');
  if (deleteItemBtn) {
    deleteItemBtn.addEventListener('click', () => {
      if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
        fetch('/kanban/delete-item/' + currentItemId, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
          .then(response => {
            kanban.removeElement(currentItemId);
            kanbanOffcanvas.hide();
          })
          .catch(error => console.error('Error deleting item:', error));
      }
    });
  }

  const kanbanContainer = document.querySelector('.kanban-container');
  const kanbanTitleBoard = Array.from(document.querySelectorAll('.kanban-title-board'));
  const kanbanItem = Array.from(document.querySelectorAll('.kanban-item'));

  // Render custom items
  if (kanbanItem.length) {
    kanbanItem.forEach(el => {
      const element = `<span class="kanban-text">${el.textContent}</span>`;
      let img = '';

      if (el.getAttribute('data-image')) {
        img = `
              <img class="img-fluid rounded mb-2"
                   src="${assetsPath}img/elements/${el.getAttribute('data-image')}">
          `;
      }

      el.textContent = '';

      if (el.getAttribute('data-badge') && el.getAttribute('data-badge-text')) {
        // Check if header already exists to prevent duplicates
        if (!el.querySelector('.kanban-tasks-item-dropdown')) {
          el.insertAdjacentHTML(
            'afterbegin',
            `${renderHeader(el.getAttribute('data-badge'), el.getAttribute('data-badge-text'))}${img}${element}`
          );
        }
      }

      if (el.getAttribute('data-comments') || el.getAttribute('data-due-date') || el.getAttribute('data-assigned')) {
        // Check if footer already exists to prevent duplicates
        if (!el.querySelector('.assigned-avatar')) {
          el.insertAdjacentHTML(
            'beforeend',
            renderFooter(
              el.getAttribute('data-attachments') || 0,
              el.getAttribute('data-comments') || 0,
              el.getAttribute('data-assigned') ? el.getAttribute('data-assigned').split(',') : [],
              el.getAttribute('data-members') ? el.getAttribute('data-members').split(',') : []
            )
          );
        }
      }
    });
  }

  // Initialize tooltips for rendered items
  const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(tooltipTriggerEl => {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Prevent sidebar from opening on dropdown button click
  const tasksItemDropdown = Array.from(document.querySelectorAll('.kanban-tasks-item-dropdown'));
  if (tasksItemDropdown.length) {
    tasksItemDropdown.forEach(dropdown => {
      dropdown.addEventListener('click', event => {
        event.stopPropagation();
      });
    });
  }

  // Toggle "add new" input and actions for add-new-btn
  if (kanbanAddBoardBtn) {
    kanbanAddBoardBtn.addEventListener('click', () => {
      kanbanAddNewInput.forEach(el => {
        el.value = ''; // Clear input value
        el.classList.toggle('d-none'); // Toggle visibility
      });
    });
  }

  // Render "add new" inline with boards
  if (kanbanContainer) {
    kanbanContainer.append(kanbanAddNewBoard);
  }

  // Makes kanban title editable for rendered boards
  if (kanbanTitleBoard) {
    kanbanTitleBoard.forEach(elem => {
      elem.addEventListener('mouseenter', () => {
        elem.contentEditable = 'true';
      });

      elem.addEventListener('blur', e => {
        const boardId = elem.closest('.kanban-board').getAttribute('data-id');
        const newTitle = e.target.textContent.trim();

        fetch('/kanban/update-board', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ id: boardId, title: newTitle })
        })
          .then(response => response.json())
          .then(data => {
            console.log('Board renamed:', data);
          })
          .catch(error => console.error('Error renaming board:', error));
      });

      // Appends delete icon with title
      elem.insertAdjacentHTML('afterend', renderBoardDropdown());
    });
  }

  // Delete Board for rendered boards
  const deleteBoards = Array.from(document.querySelectorAll('.delete-board'));
  deleteBoards.forEach(elem => {
    elem.addEventListener('click', () => {
      const id = elem.closest('.kanban-board').getAttribute('data-id');
      if (confirm('Tem certeza que deseja excluir este quadro? Todas as tarefas dele serão removidas.')) {
        fetch('/kanban/delete-board/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
          .then(response => response.json())
          .then(() => {
            kanban.removeBoard(id);
          })
          .catch(error => console.error('Error deleting board:', error));
      }
    });
  });

  // Rename Board from dropdown
  const renameBoards = Array.from(document.querySelectorAll('.rename-board'));
  renameBoards.forEach(elem => {
    elem.addEventListener('click', () => {
      const header = elem.closest('.kanban-board-header').querySelector('.kanban-title-board');
      header.contentEditable = 'true';
      header.focus();
    });
  });

  // Delete task for rendered boards
  const deleteTasks = Array.from(document.querySelectorAll('.delete-task'));
  deleteTasks.forEach(task => {
    task.addEventListener('click', () => {
      const id = task.closest('.kanban-item').getAttribute('data-eid');
      kanban.removeElement(id);
    });
  });

  // Cancel "Add New Board" input
  const cancelAddNew = document.querySelector('.kanban-add-board-cancel-btn');
  if (cancelAddNew) {
    cancelAddNew.addEventListener('click', () => {
      kanbanAddNewInput.forEach(el => {
        el.classList.toggle('d-none');
      });
    });
  }

  // Add new board
  if (kanbanAddNewBoard) {
    kanbanAddNewBoard.addEventListener('submit', e => {
      e.preventDefault();
      const value = e.target.querySelector('.form-control').value.trim();

      // API Call
      fetch('/kanban/add-board', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ title: value })
      })
        .then(response => response.json())
        .then(data => {
          const id = data.id;
          const title = data.title;
          kanban.addBoards([{ id, title }]);

          // ... rest of the UI logic ...

          // Add delete board option to new board and make title editable
          const newBoard = document.querySelector('.kanban-board:last-child');
          if (newBoard) {
            const header = newBoard.querySelector('.kanban-title-board');
            header.insertAdjacentHTML('afterend', renderBoardDropdown());

            // Make title editable
            header.addEventListener('mouseenter', () => {
              header.contentEditable = 'true';
            });

            header.addEventListener('blur', e => {
              const boardId = newBoard.getAttribute('data-id');
              const newTitle = e.target.textContent.trim();

              fetch('/kanban/update-board', {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: boardId, title: newTitle })
              })
                .then(response => response.json())
                .then(data => console.log('Board renamed:', data))
                .catch(error => console.error('Error renaming board:', error));
            });

            // Add functionality to new board dropdown items
            const dropdown = newBoard.querySelector('.dropdown-menu');
            const deleteBtn = dropdown.querySelector('.delete-board');
            const renameBtn = dropdown.querySelector('.rename-board');

            if (deleteBtn) {
              deleteBtn.addEventListener('click', () => {
                const id = newBoard.getAttribute('data-id');
                if (confirm('Tem certeza que deseja excluir este quadro? Todas as tarefas dele serão removidas.')) {
                  fetch('/kanban/delete-board/' + id, {
                    method: 'DELETE',
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                  })
                    .then(() => kanban.removeBoard(id))
                    .catch(error => console.error('Error deleting board:', error));
                }
              });
            }

            if (renameBtn) {
              renameBtn.addEventListener('click', () => {
                header.contentEditable = 'true';
                header.focus();
              });
            }
          }
        });

      // Hide input fields
      kanbanAddNewInput.forEach(el => {
        el.classList.add('d-none');
      });

      // Re-append the "Add New Board" form
      if (kanbanContainer) {
        kanbanContainer.append(kanbanAddNewBoard);
      }
    });
  }

  // Clear comment editor on Kanban sidebar close
  kanbanSidebar.addEventListener('hidden.bs.offcanvas', () => {
    const editor = kanbanSidebar.querySelector('.ql-editor').firstElementChild;
    if (editor) editor.innerHTML = '';
  });

  // Re-init tooltip when offcanvas opens(Bootstrap bug)
  if (kanbanSidebar) {
    kanbanSidebar.addEventListener('shown.bs.offcanvas', () => {
      const tooltipTriggerList = Array.from(kanbanSidebar.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  }
});
