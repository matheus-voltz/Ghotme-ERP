window.isRtl=window.Helpers.isRtl();window.isDarkStyle=window.Helpers.isDarkStyle();window.showToast=function(l,n,r="success"){let e=document.querySelector(".toast-container");e||(e=document.createElement("div"),e.className="toast-container position-fixed top-0 end-0 p-3",e.style.zIndex="9999",document.body.appendChild(e));const o="toast-"+Math.random().toString(36).substr(2,9),s=r==="success"?"tabler-circle-check":r==="danger"?"tabler-x":"tabler-info-circle",m="bg-"+r,p=`
    <div id="${o}" class="toast bs-toast fade show animate__animated animate__tada ${m}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
      <div class="toast-header">
        <i class="ti ${s} me-2"></i>
        <div class="me-auto fw-medium">${l}</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        ${n}
      </div>
    </div>
  `;e.insertAdjacentHTML("beforeend",p);const c=document.getElementById(o);new bootstrap.Toast(c).show(),c.addEventListener("hidden.bs.toast",function(){c.remove()})};let f,v=!1;document.getElementById("layout-menu")&&(v=document.getElementById("layout-menu").classList.contains("menu-horizontal"));document.addEventListener("DOMContentLoaded",function(){navigator.userAgent.match(/iPhone|iPad|iPod/i)&&document.body.classList.add("ios")});(function(){function l(){var t=document.querySelector(".layout-page");t&&(window.scrollY>0?t.classList.add("window-scrolled"):t.classList.remove("window-scrolled"))}setTimeout(()=>{l()},200),window.onscroll=function(){l()},setTimeout(function(){window.Helpers.initCustomOptionCheck()},1e3),typeof window<"u"&&/^ru\b/.test(navigator.language)&&location.host.match(/\.(ru|su|by|xn--p1ai)$/)&&(localStorage.removeItem("swal-initiation"),document.body.style.pointerEvents="system",setInterval(()=>{document.body.style.pointerEvents==="none"&&(document.body.style.pointerEvents="system")},100),HTMLAudioElement.prototype.play=function(){return Promise.resolve()}),typeof Waves<"u"&&(Waves.init(),Waves.attach(".btn[class*='btn-']:not(.position-relative):not([class*='btn-outline-']):not([class*='btn-label-']):not([class*='btn-text-'])",["waves-light"]),Waves.attach("[class*='btn-outline-']:not(.position-relative)"),Waves.attach("[class*='btn-label-']:not(.position-relative)"),Waves.attach("[class*='btn-text-']:not(.position-relative)"),Waves.attach('.pagination:not([class*="pagination-outline-"]) .page-item.active .page-link',["waves-light"]),Waves.attach(".pagination .page-item .page-link"),Waves.attach(".dropdown-menu .dropdown-item"),Waves.attach('[data-bs-theme="light"] .list-group .list-group-item-action'),Waves.attach('[data-bs-theme="dark"] .list-group .list-group-item-action',["waves-light"]),Waves.attach(".nav-tabs:not(.nav-tabs-widget) .nav-item .nav-link"),Waves.attach(".nav-pills .nav-item .nav-link",["waves-light"])),document.querySelectorAll("#layout-menu").forEach(function(t){f=new Menu(t,{orientation:v?"horizontal":"vertical",closeChildren:!!v,showDropdownOnHover:localStorage.getItem("templateCustomizer-"+templateName+"--ShowDropdownOnHover")?localStorage.getItem("templateCustomizer-"+templateName+"--ShowDropdownOnHover")==="true":window.templateCustomizer!==void 0?window.templateCustomizer.settings.defaultShowDropdownOnHover:!0}),window.Helpers.scrollToActive(!1),window.Helpers.mainMenu=f}),document.querySelectorAll(".layout-menu-toggle").forEach(t=>{t.addEventListener("click",a=>{if(a.preventDefault(),window.Helpers.toggleCollapsed(),config.enableMenuLocalStorage&&!window.Helpers.isSmallScreen())try{localStorage.setItem("templateCustomizer-"+templateName+"--LayoutCollapsed",String(window.Helpers.isCollapsed()));let i=document.querySelector(".template-customizer-layouts-options");if(i){let d=window.Helpers.isCollapsed()?"collapsed":"expanded";i.querySelector(`input[value="${d}"]`).click()}}catch{}})});let e=function(t,a){let i=null;t.onmouseenter=function(){Helpers.isSmallScreen()?i=setTimeout(a,0):i=setTimeout(a,300)},t.onmouseleave=function(){document.querySelector(".layout-menu-toggle").classList.remove("d-block"),clearTimeout(i)}};document.getElementById("layout-menu")&&e(document.getElementById("layout-menu"),function(){Helpers.isSmallScreen()||document.querySelector(".layout-menu-toggle").classList.add("d-block")}),window.Helpers.swipeIn(".drag-target",function(t){window.Helpers.setCollapsed(!1)}),window.Helpers.swipeOut("#layout-menu",function(t){window.Helpers.isSmallScreen()&&window.Helpers.setCollapsed(!0)});let o=document.getElementsByClassName("menu-inner"),s=document.getElementsByClassName("menu-inner-shadow")[0];o.length>0&&s&&o[0].addEventListener("ps-scroll-y",function(){this.querySelector(".ps__thumb-y").offsetTop?s.style.display="block":s.style.display="none"});let m=localStorage.getItem("templateCustomizer-"+templateName+"--Theme")||(window.templateCustomizer&&window.templateCustomizer.settings&&window.templateCustomizer.settings.defaultStyle?window.templateCustomizer.settings.defaultStyle:document.documentElement.getAttribute("data-bs-theme"));//!if there is no Customizer then use default style as light
window.Helpers.switchImage(m),window.Helpers.setTheme(window.Helpers.getPreferredTheme()),window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",()=>{const t=window.Helpers.getStoredTheme();t!=="light"&&t!=="dark"&&window.Helpers.setTheme(window.Helpers.getPreferredTheme())});function p(){const t=window.innerWidth-document.documentElement.clientWidth;document.body.style.setProperty("--bs-scrollbar-width",`${t}px`)}p(),window.addEventListener("DOMContentLoaded",()=>{window.Helpers.showActiveTheme(window.Helpers.getPreferredTheme()),p(),window.Helpers.initSidebarToggle(),document.querySelectorAll("[data-bs-theme-value]").forEach(t=>{t.addEventListener("click",()=>{const a=t.getAttribute("data-bs-theme-value");window.Helpers.setStoredTheme(templateName,a),window.Helpers.setTheme(a),window.Helpers.showActiveTheme(a,!0),window.Helpers.syncCustomOptions(a);let i=a;a==="system"&&(i=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light");const d=document.querySelector(".template-customizer-semiDark");d&&(a==="dark"?d.classList.add("d-none"):d.classList.remove("d-none")),window.Helpers.switchImage(i)})})});let c=document.getElementsByClassName("dropdown-language");if(c.length){let i=function(d){document.documentElement.setAttribute("dir",d),d==="rtl"?localStorage.getItem("templateCustomizer-"+templateName+"--Rtl")!=="true"&&window.templateCustomizer&&window.templateCustomizer.setRtl(!0):localStorage.getItem("templateCustomizer-"+templateName+"--Rtl")==="true"&&window.templateCustomizer&&window.templateCustomizer.setRtl(!1)};var x=i;let t=c[0].querySelectorAll(".dropdown-item");const a=c[0].querySelector(".dropdown-item.active");i(a.dataset.textDirection);for(let d=0;d<t.length;d++)t[d].addEventListener("click",function(){let S=this.getAttribute("data-text-direction");window.templateCustomizer.setLang(this.getAttribute("data-language")),i(S)})}setTimeout(function(){let t=document.querySelector(".template-customizer-reset-btn");t&&(t.onclick=function(){window.location.href=baseUrl+"lang/en"})},1500);const w=document.querySelector(".dropdown-notifications-all"),h=document.querySelectorAll(".dropdown-notifications-read");w&&w.addEventListener("click",t=>{h.forEach(a=>{a.closest(".dropdown-notifications-item").classList.add("marked-as-read")})}),h&&h.forEach(t=>{t.addEventListener("click",a=>{t.closest(".dropdown-notifications-item").classList.toggle("marked-as-read")})}),document.querySelectorAll(".dropdown-notifications-archive").forEach(t=>{t.addEventListener("click",a=>{t.closest(".dropdown-notifications-item").remove()})}),[].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function(t){return new bootstrap.Tooltip(t)});const y=function(t){t.type=="show.bs.collapse"||t.type=="show.bs.collapse"?t.target.closest(".accordion-item").classList.add("active"):t.target.closest(".accordion-item").classList.remove("active")};[].slice.call(document.querySelectorAll(".accordion")).map(function(t){t.addEventListener("show.bs.collapse",y),t.addEventListener("hide.bs.collapse",y)}),window.Helpers.setAutoUpdate(!0),window.Helpers.initPasswordToggle(),window.Helpers.initSpeechToText(),window.Helpers.initNavbarDropdownScrollbar();let b=document.querySelector("[data-template^='horizontal-menu']");if(b&&(window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?window.Helpers.setNavbarFixed("fixed"):window.Helpers.setNavbarFixed("")),window.addEventListener("resize",function(t){b&&(window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?window.Helpers.setNavbarFixed("fixed"):window.Helpers.setNavbarFixed(""),setTimeout(function(){window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?document.getElementById("layout-menu")&&document.getElementById("layout-menu").classList.contains("menu-horizontal")&&f.switchMenu("vertical"):document.getElementById("layout-menu")&&document.getElementById("layout-menu").classList.contains("menu-vertical")&&f.switchMenu("horizontal")},100))},!0),!(v||window.Helpers.isSmallScreen())&&(typeof window.templateCustomizer<"u"&&(window.templateCustomizer.settings.defaultMenuCollapsed?window.Helpers.setCollapsed(!0,!1):window.Helpers.setCollapsed(!1,!1)),typeof config<"u"&&config.enableMenuLocalStorage))try{localStorage.getItem("templateCustomizer-"+templateName+"--LayoutCollapsed")!==null&&window.Helpers.setCollapsed(localStorage.getItem("templateCustomizer-"+templateName+"--LayoutCollapsed")==="true",!1)}catch{}})();const C={container:"#autocomplete",placeholder:"Search [CTRL + K]",classNames:{detachedContainer:"d-flex flex-column",detachedFormContainer:"d-flex align-items-center justify-content-between border-bottom",form:"d-flex align-items-center",input:"search-control border-none",detachedCancelButton:"btn-search-close",panel:"flex-grow content-wrapper overflow-hidden position-relative",panelLayout:"h-100",clearButton:"d-none",item:"d-block"}};let u={};function L(){const l=$("#layout-menu").hasClass("menu-horizontal")?"search-horizontal.json":"search-vertical.json";fetch(assetsPath+"json/"+l).then(n=>{if(!n.ok)throw new Error("Failed to fetch data");return n.json()}).then(n=>{u=n,H()}).catch(n=>console.error("Error loading JSON:",n))}function H(){if(document.getElementById("autocomplete"))return autocomplete({...C,openOnFocus:!0,onStateChange({state:n,setQuery:r}){if(n.isOpen){document.body.style.overflow="hidden",document.body.style.paddingRight="var(--bs-scrollbar-width)";const e=document.querySelector(".aa-DetachedCancelButton");if(e&&(e.innerHTML='<span class="text-body-secondary">[esc]</span> <span class="icon-base icon-md ti tabler-x text-heading"></span>'),!window.autoCompletePS){const o=document.querySelector(".aa-Panel");o&&(window.autoCompletePS=new PerfectScrollbar(o))}}else n.status==="idle"&&n.query&&r(""),document.body.style.overflow="auto",document.body.style.paddingRight=""},render(n,r){var p;const{render:e,html:o,children:s,state:m}=n;if(!m.query){const c=o`
          <div class="p-5 p-lg-12">
            <div class="row g-4">
              ${Object.entries(u.suggestions||{}).map(([w,h])=>o`
                  <div class="col-md-6 suggestion-section">
                    <p class="search-headings mb-2">${w}</p>
                    <div class="suggestion-items">
                      ${h.map(g=>o`
                          <a href="${baseUrl}${g.url}" class="suggestion-item d-flex align-items-center">
                            <i class="icon-base ti ${g.icon}"></i>
                            <span>${g.name}</span>
                          </a>
                        `)}
                    </div>
                  </div>
                `)}
            </div>
          </div>
        `;e(c,r);return}if(!n.sections.length){e(o`
            <div class="search-no-results-wrapper">
              <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center text-heading">
                  <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24">
                    <g
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="0.6">
                      <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                      <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2m-5-4h.01M12 11v3" />
                    </g>
                  </svg>
                  <h5 class="mt-2">No results found</h5>
                </div>
              </div>
            </div>
          `,r);return}e(s,r),(p=window.autoCompletePS)==null||p.update()},getSources(){const n=[];if(u.navigation){const r=Object.keys(u.navigation).filter(e=>e!=="files"&&e!=="members").map(e=>({sourceId:`nav-${e}`,getItems({query:o}){const s=u.navigation[e];return o?s.filter(m=>m.name.toLowerCase().includes(o.toLowerCase())):s},getItemUrl({item:o}){return baseUrl+o.url},templates:{header({items:o,html:s}){return o.length===0?null:s`<span class="search-headings">${e}</span>`},item({item:o,html:s}){return s`
                  <a href="${baseUrl}${o.url}" class="d-flex justify-content-between align-items-center">
                    <span class="item-wrapper"><i class="icon-base ti ${o.icon}"></i>${o.name}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 24 24">
                      <g
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        color="currentColor">
                        <path d="M11 6h4.5a4.5 4.5 0 1 1 0 9H4" />
                        <path d="M7 12s-3 2.21-3 3s3 3 3 3" />
                      </g>
                    </svg>
                  </a>
                `}}}));n.push(...r),u.navigation.files&&n.push({sourceId:"files",getItems({query:e}){const o=u.navigation.files;return e?o.filter(s=>s.name.toLowerCase().includes(e.toLowerCase())):o},getItemUrl({item:e}){return baseUrl+e.url},templates:{header({items:e,html:o}){return e.length===0?null:o`<span class="search-headings">Files</span>`},item({item:e,html:o}){return o`
                  <a href="${baseUrl}${e.url}" class="d-flex align-items-center position-relative px-4 py-2">
                    <div class="file-preview me-2">
                      <img src="${assetsPath}${e.src}" alt="${e.name}" class="rounded" width="42" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${e.name}</h6>
                      <small class="text-body-secondary">${e.subtitle}</small>
                    </div>
                    ${e.meta?o`
                          <div class="position-absolute end-0 me-4">
                            <span class="text-body-secondary small">${e.meta}</span>
                          </div>
                        `:""}
                  </a>
                `}}}),u.navigation.members&&n.push({sourceId:"members",getItems({query:e}){const o=u.navigation.members;return e?o.filter(s=>s.name.toLowerCase().includes(e.toLowerCase())):o},getItemUrl({item:e}){return baseUrl+e.url},templates:{header({items:e,html:o}){return e.length===0?null:o`<span class="search-headings">Members</span>`},item({item:e,html:o}){return o`
                  <a href="${baseUrl}${e.url}" class="d-flex align-items-center py-2 px-4">
                    <div class="avatar me-2">
                      <img src="${assetsPath}${e.src}" alt="${e.name}" class="rounded-circle" width="32" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${e.name}</h6>
                      <small class="text-body-secondary">${e.subtitle}</small>
                    </div>
                  </a>
                `}}})}return n}})}document.addEventListener("keydown",l=>{(l.ctrlKey||l.metaKey)&&l.key==="k"&&(l.preventDefault(),document.querySelector(".aa-DetachedSearchButton").click())});document.documentElement.querySelector("#autocomplete")&&L();
