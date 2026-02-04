/**
 * Main - Front Pages
 */
'use strict';

(function () {
  const nav = document.querySelector('.layout-navbar'),
    heroAnimation = document.getElementById('hero-animation'),
    animationImg = document.querySelectorAll('.hero-dashboard-img'),
    animationElements = document.querySelectorAll('.hero-elements-img'),
    swiperLogos = document.getElementById('swiper-clients-logos'),
    swiperReviews = document.getElementById('swiper-reviews'),
    ReviewsPreviousBtn = document.getElementById('reviews-previous-btn'),
    ReviewsNextBtn = document.getElementById('reviews-next-btn'),
    ReviewsSliderPrev = document.querySelector('.swiper-button-prev'),
    ReviewsSliderNext = document.querySelector('.swiper-button-next');

  // Hero
  const mediaQueryXL = '1200';
  const width = screen.width;
  if (width >= mediaQueryXL && heroAnimation) {
    heroAnimation.addEventListener('mousemove', function parallax(e) {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(1rem)';
      });
      animationImg.forEach(layer => {
        let x = (window.innerWidth - e.pageX * 2) / 100;
        let y = (window.innerHeight - e.pageY * 2) / 100;
        layer.style.transform = `perspective(1200px) rotateX(${y}deg) rotateY(${x}deg) scale3d(1, 1, 1)`;
      });
    });
    nav.addEventListener('mousemove', function parallax(e) {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(1rem)';
      });
      animationImg.forEach(layer => {
        let x = (window.innerWidth - e.pageX * 2) / 100;
        let y = (window.innerHeight - e.pageY * 2) / 100;
        layer.style.transform = `perspective(1200px) rotateX(${y}deg) rotateY(${x}deg) scale3d(1, 1, 1)`;
      });
    });

    heroAnimation.addEventListener('mouseout', function () {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(0)';
      });
      animationImg.forEach(layer => {
        layer.style.transform = 'perspective(1200px) scale(1) rotateX(0) rotateY(0)';
      });
    });
  }

  // swiper carousel
  // Customers reviews
  // -----------------------------------
  if (swiperReviews) {
    new Swiper(swiperReviews, {
      slidesPerView: 1,
      spaceBetween: 5,
      grabCursor: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false
      },
      loop: true,
      loopAdditionalSlides: 1,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev'
      },
      breakpoints: {
        1200: {
          slidesPerView: 3,
          spaceBetween: 26
        },
        992: {
          slidesPerView: 2,
          spaceBetween: 20
        }
      }
    });
  }

  // Reviews slider next and previous
  // -----------------------------------
  // Add click event listener to next button
  if (ReviewsNextBtn) {
    ReviewsNextBtn.addEventListener('click', function () {
      ReviewsSliderNext.click();
    });
  }
  if (ReviewsPreviousBtn) {
    ReviewsPreviousBtn.addEventListener('click', function () {
      ReviewsSliderPrev.click();
    });
  }

  // Review client logo
  // -----------------------------------
  if (swiperLogos) {
    new Swiper(swiperLogos, {
      slidesPerView: 2,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false
      },
      breakpoints: {
        992: {
          slidesPerView: 5
        },
        768: {
          slidesPerView: 3
        }
      }
    });
  }

  // Pricing Plans
  // -----------------------------------
  const initPricing = () => {
    const priceDurationToggler = document.querySelector('.price-duration-toggler');
    const priceMonthlyList = [].slice.call(document.querySelectorAll('.price-monthly'));
    const priceYearlyList = [].slice.call(document.querySelectorAll('.price-yearly'));
    const planActionButtons = [].slice.call(document.querySelectorAll('.plan-action-btn'));

    function togglePrice() {
      if (priceDurationToggler.checked) {
        // If checked (Anual)
        priceYearlyList.forEach(function (yearEl) {
          yearEl.classList.remove('d-none');
        });
        priceMonthlyList.forEach(function (monthEl) {
          monthEl.classList.add('d-none');
        });
        // Update button links to yearly
        planActionButtons.forEach(function (btnEl) {
          const yearlyLink = btnEl.getAttribute('data-yearly-link');
          if (yearlyLink) {
            btnEl.setAttribute('href', yearlyLink);
          }
        });
      } else {
        // If not checked (Mensal)
        priceYearlyList.forEach(function (yearEl) {
          yearEl.classList.add('d-none');
        });
        priceMonthlyList.forEach(function (monthEl) {
          monthEl.classList.remove('d-none');
        });
        // Update button links to monthly
        planActionButtons.forEach(function (btnEl) {
          const monthlyLink = btnEl.getAttribute('data-monthly-link');
          if (monthlyLink) {
            btnEl.setAttribute('href', monthlyLink);
          }
        });
      }
    }

    if (priceDurationToggler) {
      togglePrice();
      priceDurationToggler.addEventListener('change', togglePrice);
    }
  };

  // Initialize on load
  initPricing();
})();
