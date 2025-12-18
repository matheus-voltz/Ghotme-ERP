
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="exampleModalLabel">New Record</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
      <div class="col-sm-12 form-control-validation">
        <label class="form-label" for="basicFullname">Full Name</label>
        <div class="input-group input-group-merge">
          <span id="basicFullname2" class="input-group-text"><i class="icon-base ti tabler-user"></i></span>
          <input type="text" id="basicFullname" class="form-control dt-full-name" name="basicFullname"
            placeholder="John Doe" aria-label="John Doe" aria-describedby="basicFullname2" />
        </div>
      </div>
      <div class="col-sm-12 form-control-validation">
        <label class="form-label" for="basicPost">Post</label>
        <div class="input-group input-group-merge">
          <span id="basicPost2" class="input-group-text"><i class="icon-base ti tabler-briefcase"></i></span>
          <input type="text" id="basicPost" name="basicPost" class="form-control dt-post" placeholder="Web Developer"
            aria-label="Web Developer" aria-describedby="basicPost2" />
        </div>
      </div>
      <div class="col-sm-12 form-control-validation">
        <label class="form-label" for="basicEmail">Email</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-mail"></i></span>
          <input type="text" id="basicEmail" name="basicEmail" class="form-control dt-email"
            placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
        </div>
        <div class="form-text">You can use letters, numbers & periods</div>
      </div>
      <div class="col-sm-12 form-control-validation">
        <label class="form-label" for="basicDate">Joining Date</label>
        <div class="input-group input-group-merge">
          <span id="basicDate2" class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
          <input type="text" class="form-control dt-date" id="basicDate" name="basicDate" aria-describedby="basicDate2"
            placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
        </div>
      </div>
      <div class="col-sm-12 form-control-validation">
        <label class="form-label" for="basicSalary">Salary</label>
        <div class="input-group input-group-merge">
          <span id="basicSalary2" class="input-group-text"><i class="icon-base ti tabler-currency-dollar"></i></span>
          <input type="number" id="basicSalary" name="basicSalary" class="form-control dt-salary" placeholder="12000"
            aria-label="12000" aria-describedby="basicSalary2" />
        </div>
      </div>
      <div class="col-sm-12">
        <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- <script>
const cepInput = document.getElementById('cep');

if (cepInput) {
  cepInput.addEventListener('blur', function () {
    let cep = this.value.replace(/\D/g, '');

    if (cep.length !== 8) return;

    fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
      .then(response => {
        if (!response.ok) throw new Error('CEP não encontrado');
        return response.json();
      })
      .then(data => {
        document.getElementById('rua')?.value = data.street || '';
        document.getElementById('bairro')?.value = data.neighborhood || '';
        document.getElementById('cidade')?.value = data.city || '';
        document.getElementById('estado')?.value = data.state || '';
      })
      .catch(() => {
        alert('CEP inválido ou não encontrado');
      });
  });
}
</script>
<script>
function aplicarMascaraTelefone(campo) {
  let valor = campo.value.replace(/\D/g, '');

  if (valor.length > 11) {
    valor = valor.substring(0, 11);
  }

  valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
  valor = valor.replace(/(\d{1})(\d{4})(\d{4})$/, '$1 $2-$3');

  campo.value = valor;
}

// aplica automaticamente em todos os campos com data-mask="phone"
document.addEventListener('input', function (e) {
  if (e.target.dataset.mask === 'phone') {
    aplicarMascaraTelefone(e.target);
  }
});
</script> --}}