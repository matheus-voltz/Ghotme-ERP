/**
 * App User View - Billing
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Cancel Subscription alert
  const cancelSubscription = document.querySelector('.cancel-subscription');

  // Alert With Functional Confirm Button
  if (cancelSubscription) {
    cancelSubscription.onclick = function () {
      Swal.fire({
        text: 'Tem certeza que quer cancelar sua inscrição?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        customClass: {
          confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          Swal.fire({
            icon: 'success',
            title: 'Assinatura cancelada!',
            text: 'Sua inscrição foi cancelada com sucesso!!',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelado',
            text: 'Desinscrição cancelada!!',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        }
      });
    };
  }

  // On edit address click, update text of add address modal
  const addressEdit = document.querySelector('.edit-address'),
    addressTitle = document.querySelector('.address-title'),
    addressSubTitle = document.querySelector('.address-subtitle');

  addressEdit.onclick = function () {
    addressTitle.innerHTML = 'Edit Address'; // reset text
    addressSubTitle.innerHTML = 'Edit your current address';
  };
});
