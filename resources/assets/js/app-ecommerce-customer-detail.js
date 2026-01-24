/**
 * App eCommerce Customer Detail - delete customer Script
 */
'use strict';

(function () {
  const deleteCustomer = document.querySelector('.delete-customer');

  // Suspend User javascript
  if (deleteCustomer) {
    deleteCustomer.onclick = function () {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert customer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete customer!',
        customClass: {
          confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Customer has been removed.',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelado',
            text: 'Cancelado Delete :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        }
      });
    };
  }

  //? Billing page have multiple buttons
  // Cancel Subscription alert
  // Cancel Subscription alert
  const cancelSubscription = document.querySelectorAll('.cancel-subscription');

  // Alert With Functional Confirm Button
  if (cancelSubscription) {
    cancelSubscription.forEach(btnCancle => {
      btnCancle.onclick = function () {
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
    });
  }
})();
