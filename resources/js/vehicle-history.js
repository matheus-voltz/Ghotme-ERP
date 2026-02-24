/**
 * Vehicle History management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const vehicleSearch = $('#vehicle-search'),
        btnAddHistory = document.getElementById('btn-add-history'),
        vehicleInfoCard = document.getElementById('vehicle-info-card'),
        timelineCard = document.getElementById('timeline-card'),
        vehicleTimeline = document.getElementById('vehicle-timeline'),
        formAddHistory = document.getElementById('formAddHistory');

    // Flatpickr initialization
    const flatpickrDate = document.querySelector('.flatpickr');
    if (flatpickrDate) {
        flatpickrDate.flatpickr({
            monthSelectorType: 'static',
            dateFormat: 'Y-m-d'
        });
    }

    // Select2 with AJAX Search
    if (vehicleSearch.length) {
        const placeholder = vehicleSearch.data('placeholder') || 'Digite a Placa ou Chassi...';
        vehicleSearch.select2({
            placeholder: placeholder,
            allowClear: true,
            ajax: {
                url: window.historySearchUrl || (baseUrl + 'vehicle-history/search'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            minimumInputLength: 2
        }).on('select2:select', function (e) {
            const data = e.params.data;
            loadVehicleData(data);
        }).on('select2:clear', function () {
            resetView();
        });
    }

    function loadVehicleData(data) {
        const v = data.full_data;

        // Show cards
        vehicleInfoCard.classList.remove('d-none');
        timelineCard.classList.remove('d-none');
        btnAddHistory.disabled = false;

        // Fill Info Card
        document.getElementById('info-plate').textContent = v.placa;
        document.getElementById('info-brand-model').textContent = `${v.marca} ${v.modelo}`;
        document.getElementById('timeline-entity-name').textContent = `- ${v.placa}`; // Título dinâmico
        document.getElementById('info-owner').textContent = data.client_name;
        document.getElementById('info-km').textContent = (v.km_atual || 0).toLocaleString() + ' ' + (configNiche?.metric_unit || 'anos');
        document.getElementById('info-chassis').textContent = v.chassi || '-';
        document.getElementById('info-color').textContent = v.cor || '-';
        document.getElementById('info-year').textContent = v.ano_modelo || v.ano_fabricacao || '-';

        // Modal Hidden Field
        document.getElementById('modal-vehicle-id').value = v.id;

        // Load Timeline
        fetchTimeline(v.id);
    }

    function fetchTimeline(vehicleId) {
        vehicleTimeline.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

        fetch((window.historyTimelineUrl || (baseUrl + 'vehicle-history/timeline/')) + vehicleId)
            .then(response => response.json())
            .then(data => {
                renderTimeline(data);
            });
    }

    function renderTimeline(data) {
        if (data.length === 0) {
            const noResults = vehicleSearch.data('no-results') || 'Nenhum registro encontrado para este veículo.';
            vehicleTimeline.innerHTML = `<p class="text-center text-muted py-10">${noResults}</p>`;
            return;
        }

        let html = '';
        data.forEach(item => {
            const date = moment(item.date).format('DD/MM/YYYY');
            let iconClass = 'ti-tool text-primary';
            let badgeColor = 'info';
            let typeLabel = 'Registro Manual';

            if (item.event_type === 'os_finalizada') {
                iconClass = 'ti-paws text-success';
                badgeColor = 'success';
                typeLabel = 'Atendimento Realizado';
            } else if (item.event_type === 'entrada_oficina') {
                iconClass = 'ti-dog text-warning';
                badgeColor = 'warning';
                typeLabel = 'Entrada no Pet';
            }

            html += `
                <div class="timeline-item">
                    <div class="timeline-point"></div>
                    <div class="timeline-date">${date} • ${item.km.toLocaleString()} ${configNiche?.metric_unit || 'anos'}</div>
                    <div class="timeline-title d-flex justify-content-between">
                        <span>${item.title}</span>
                        <span class="badge bg-label-${badgeColor} ms-2" style="font-size: 0.7rem">${typeLabel}</span>
                    </div>
                    <div class="timeline-desc text-muted mb-2">${item.description || ''}</div>
                    <div class="timeline-footer d-flex justify-content-between align-items-center">
                        <small>Realizado por: <strong>${item.performer || 'Pet Shop'}</strong></small>
                        ${item.cost ? `<small class="fw-medium text-heading">R$ ${parseFloat(item.cost).toFixed(2)}</small>` : ''}
                    </div>
                </div>
            `;
        });
        vehicleTimeline.innerHTML = html;
    }

    function resetView() {
        vehicleInfoCard.classList.add('d-none');
        timelineCard.classList.add('d-none');
        btnAddHistory.disabled = true;
        const selectPrompt = vehicleSearch.data('select-prompt') || 'Selecione um veículo para ver o histórico.';
        vehicleTimeline.innerHTML = `<p class="text-center text-muted py-10">${selectPrompt}</p>`;
    }

    // Form Submit (AJAX)
    if (formAddHistory) {
        formAddHistory.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(formAddHistory);

            fetch(window.historyStoreUrl || (baseUrl + 'vehicle-history'), {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message,
                            customClass: { confirmButton: 'btn btn-success' }
                        });
                        $('#modalAddHistory').modal('hide');
                        formAddHistory.reset();
                        // Reload timeline and update KM
                        const vehicleId = document.getElementById('modal-vehicle-id').value;
                        fetchTimeline(vehicleId);
                        document.getElementById('info-km').textContent = parseInt(formData.get('km')).toLocaleString() + ' ' + (configNiche?.metric_unit || 'anos');
                    }
                });
        });
    }
});
