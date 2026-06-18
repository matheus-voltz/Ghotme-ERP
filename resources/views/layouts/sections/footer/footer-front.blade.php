<!-- Footer: Start -->
<footer class="landing-footer bg-body footer-text">
  <div class="footer-top position-relative overflow-hidden z-1">
    <img src="{{ asset('assets/img/front-pages/backgrounds/footer-bg.png') }}" alt="footer bg"
      class="footer-bg banner-bg-img z-n1" />
    <div class="container">
      <div class="row gx-0 gy-6 g-lg-10">
        <div class="col-lg-5">
          <a href="{{ url('/') }}" class="app-brand-link mb-6">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo text-white fw-bold ms-2 ps-1">{{ config('variables.templateName') }}</span>
          </a>
          <p class="footer-text footer-logo-description mb-6">
            {{ __('The complete solution for service and sales management. Simplify your daily routine and grow with Ghotme.') }}
          </p>
          <form class="footer-form">
            <label for="footer-email" class="small mb-1">{{ __('Subscribe to our newsletter') }}</label>
            <div class="d-flex mt-1">
              <input type="email" class="form-control rounded-0 rounded-start-bottom rounded-start-top"
                id="footer-email" placeholder="{{ __('Your best email') }}" />
              <button type="submit"
                class="btn btn-primary shadow-none rounded-0 rounded-end-bottom rounded-end-top">{{ __('Subscribe') }}</button>
            </div>
          </form>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-6">{{ __('System') }}</h6>
          <ul class="list-unstyled">
            <li class="mb-4">
              <a href="#landingHero" class="footer-link">{{ __('Home') }}</a>
            </li>
            <li class="mb-4">
              <a href="#landingFeatures" class="footer-link">{{ __('Features') }}</a>
            </li>
            <li class="mb-4">
              <a href="#landingPricing" class="footer-link">{{ __('Plans') }}</a>
            </li>
            <li class="mb-4">
              <a href="#landingFAQ" class="footer-link">{{ __('FAQ') }}</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-6">{{ __('Legal & Help') }}</h6>
          <ul class="list-unstyled">
            <li class="mb-4">
              <a href="{{ route('terms') }}" class="footer-link">{{ __('Terms of Use') }}</a>
            </li>
            <li class="mb-4">
              <a href="{{ route('privacy') }}" class="footer-link">{{ __('Privacy Policy') }}</a>
            </li>
            <li class="mb-4">
              <a href="mailto:suporte@ghotme.com.br" class="footer-link">{{ __('Support') }}</a>
            </li>
            <li class="mb-4">
              <a href="{{ url('/login') }}" class="footer-link">{{ __('Client Area') }}</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4">
          <h6 class="footer-title mb-6">{{ __('Contact') }}</h6>
          <ul class="list-unstyled">
            <li class="mb-4">
              <a href="mailto:contato@ghotme.com.br" class="footer-link d-flex align-items-center">
                <i class="ti tabler-mail me-2"></i> contato@ghotme.com.br
              </a>
            </li>
            <li class="mb-4">
              <span class="footer-link d-flex align-items-center" style="cursor: default;">
                <i class="ti tabler-clock me-2"></i> {{ __('Mon - Fri, 9am to 6pm') }}
              </span>
            </li>
          </ul>
          <div class="d-flex mt-4">
            <a href="javascript:void(0);" class="me-3 text-white">
              <i class="ti tabler-brand-instagram icon-lg"></i>
            </a>
            <a href="javascript:void(0);" class="me-3 text-white">
              <i class="ti tabler-brand-facebook icon-lg"></i>
            </a>
            <a href="javascript:void(0);" class="text-white">
              <i class="ti tabler-brand-linkedin icon-lg"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom py-3 py-md-5">
    <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
      <div class="mb-2 mb-md-0">
        <span class="footer-bottom-text">©
          <script>
            document.write(new Date().getFullYear());
          </script>
        </span>
        <span class="fw-bold text-white">Ghotme</span>
        <span class="footer-bottom-text">. {{ __('All rights reserved.') }}</span>
      </div>
      <div>
        <span class="footer-bottom-text">{{ __('Made with ❤️ to boost businesses.') }}</span>
      </div>
    </div>
  </div>
</footer>
<!-- Footer: End -->