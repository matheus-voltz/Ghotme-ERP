<!-- Detailed Car Blueprint SVG -->
<svg id="car-blueprint" width="900" height="300" viewBox="0 0 900 300" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    <defs>
        <style>
            .car-body {
                fill: #f1f3f5;
                stroke: #adb5bd;
                stroke-width: 2;
                stroke-linejoin: round;
            }

            .car-window {
                fill: #e9ecef;
                stroke: #ced4da;
                stroke-width: 1;
            }

            .car-wheel {
                fill: #343a40;
            }

            .car-light {
                fill: #fff;
                stroke: #ced4da;
            }

            .view-label {
                font-family: sans-serif;
                font-size: 13px;
                fill: #6c757d;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
        </style>
    </defs>

    <!-- Left Side View -->
    <g transform="translate(40, 60)">
        <!-- Body -->
        <path class="car-body" d="M10,70 L25,40 L70,25 L160,25 L215,40 L230,70 L230,90 L205,90 A22,22 0 0,1 161,90 L79,90 A22,22 0 0,1 35,90 L10,90 Z" />
        <!-- Windows -->
        <path class="car-window" d="M35,42 L68,28 L115,28 L115,42 Z" />
        <path class="car-window" d="M120,28 L155,28 L195,42 L120,42 Z" />
        <!-- Wheels -->
        <circle class="car-wheel" cx="57" cy="90" r="18" />
        <circle class="car-wheel" cx="183" cy="90" r="18" />
        <text x="120" y="140" text-anchor="middle" class="view-label">Lateral Esquerda</text>
    </g>

    <!-- Front View -->
    <g transform="translate(310, 60)">
        <path class="car-body" d="M10,90 L10,55 Q10,25 60,25 Q110,25 110,55 L110,90 Z" />
        <!-- Windshield -->
        <path class="car-window" d="M20,55 L30,30 L90,30 L100,55 Z" />
        <!-- Lights -->
        <rect class="car-light" x="15" y="65" width="20" height="10" rx="2" />
        <rect class="car-light" x="85" y="65" width="20" height="10" rx="2" />
        <!-- Grille -->
        <rect x="40" y="65" width="40" height="15" rx="2" fill="#e9ecef" stroke="#ced4da" />
        <text x="60" y="140" text-anchor="middle" class="view-label">Frente</text>
    </g>

    <!-- Rear View -->
    <g transform="translate(460, 60)">
        <path class="car-body" d="M10,90 L10,55 Q10,25 60,25 Q110,25 110,55 L110,90 Z" />
        <!-- Rear Window -->
        <path class="car-window" d="M25,50 L35,30 L85,30 L95,50 Z" />
        <!-- Lights -->
        <rect x="15" y="60" width="25" height="12" rx="2" fill="#e03131" opacity="0.8" />
        <rect x="80" y="60" width="25" height="12" rx="2" fill="#e03131" opacity="0.8" />
        <!-- Plate Area -->
        <rect x="45" y="65" width="30" height="15" fill="#fff" stroke="#dee2e6" />
        <text x="60" y="140" text-anchor="middle" class="view-label">Traseira</text>
    </g>

    <!-- Right Side View -->
    <g transform="translate(610, 60) scale(-1, 1) translate(-240, 0)">
        <path class="car-body" d="M10,70 L25,40 L70,25 L160,25 L215,40 L230,70 L230,90 L205,90 A22,22 0 0,1 161,90 L79,90 A22,22 0 0,1 35,90 L10,90 Z" />
        <!-- Windows -->
        <path class="car-window" d="M35,42 L68,28 L115,28 L115,42 Z" />
        <path class="car-window" d="M120,28 L155,28 L195,42 L120,42 Z" />
        <!-- Wheels -->
        <circle class="car-wheel" cx="57" cy="90" r="18" />
        <circle class="car-wheel" cx="183" cy="90" r="18" />
        <!-- Label needs un-mirroring -->
        <text x="120" y="140" text-anchor="middle" class="view-label" transform="scale(-1, 1) translate(-240, 0)">Lateral Direita</text>
    </g>
</svg>