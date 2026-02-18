@php
$containerFooter =
isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
? 'container-xxl'
: 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        &#169;
        <script>
          document.write(new Date().getFullYear());
        </script>
        <strong>Ghotme ERP</strong>. Todos os direitos reservados.
      </div>
      <div class="d-none d-lg-inline-block">
        <a href="{{ route('support.chat') }}" class="footer-link me-4">Suporte</a>
        <a href="{{ route('settings.company-data') }}" class="footer-link me-4">Dados da Empresa</a>
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
