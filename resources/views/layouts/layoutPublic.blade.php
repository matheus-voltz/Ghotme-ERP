@php
$configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster')

@section('layoutContent')
@php
$isDark = $configData['theme'] === 'dark';
@endphp

<div class="container-xxl">
  <!-- Theme Toggle Button -->
  <div class="p-3 text-end d-flex justify-content-end align-items-center" style="position: absolute; top: 0; right: 0; z-index: 1100;">
    <div class="nav-item dropdown me-2">
      <a class="nav-link dropdown-toggle hide-arrow bg-white shadow-sm theme-switcher d-flex align-items-center justify-content-center" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown" style="width: 40px; height: 40px; border-radius: 50% !important; padding: 0 !important;">
        <i class="ti tabler-sun ti-md theme-icon-active text-primary"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
            <i class="ti tabler-sun me-2 ti-sm"></i>Claro
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">
            <i class="ti tabler-moon me-2 ti-sm"></i>Escuro
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="system">
            <i class="ti tabler-device-desktop me-2 ti-sm"></i>Sistema
          </button>
        </li>
      </ul>
    </div>
  </div>

  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
      @yield('content')
    </div>
  </div>
</div>

<script>
  // Script para gerenciar a troca de temas no portal público
  (function() {
    const getStoredTheme = () => localStorage.getItem('templateCustomizer-laravel-v1.0.0--theme') || 'system';
    const setStoredTheme = theme => localStorage.setItem('templateCustomizer-laravel-v1.0.0--theme', theme);

    const getPreferredTheme = () => {
      const storedTheme = getStoredTheme();
      if (storedTheme !== 'system') return storedTheme;
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = theme => {
      const themeValue = theme === 'system' ? getPreferredTheme() : theme;
      document.documentElement.setAttribute('data-bs-theme', themeValue);

      // Update icon
      const activeIcon = document.querySelector('.theme-icon-active');
      if (activeIcon) {
        activeIcon.className = `ti tabler-${themeValue === 'dark' ? 'moon' : 'sun'} ti-md theme-icon-active text-primary`;
      }

      // Update active state in dropdown
      document.querySelectorAll('[data-bs-theme-value]').forEach(el => {
        el.classList.remove('active');
        if (el.getAttribute('data-bs-theme-value') === theme) {
          el.classList.add('active');
        }
      });
    };

    window.addEventListener('DOMContentLoaded', () => {
      const currentTheme = getStoredTheme();
      setTheme(currentTheme);

      document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
        toggle.addEventListener('click', () => {
          const theme = toggle.getAttribute('data-bs-theme-value');
          setStoredTheme(theme);
          setTheme(theme);
        });
      });
    });
  })();
</script>
@endsection