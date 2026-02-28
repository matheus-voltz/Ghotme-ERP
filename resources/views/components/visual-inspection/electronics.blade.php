<!-- Smartphone Blueprint SVG for Electronics Niche -->
<svg id="electronics-blueprint" width="600" height="400" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    <defs>
        <style>
            .phone-body {
                fill: #f1f3f5;
                stroke: #adb5bd;
                stroke-width: 2;
                stroke-linejoin: round;
            }

            .phone-screen {
                fill: #e9ecef;
                stroke: #ced4da;
                stroke-width: 1;
            }

            .phone-button {
                fill: #adb5bd;
            }

            .phone-camera {
                fill: #343a40;
            }

            .view-label {
                font-family: sans-serif;
                font-size: 14px;
                fill: #6c757d;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
        </style>
    </defs>

    <!-- FRONT VIEW -->
    <g transform="translate(80, 40)">
        <!-- Phone Frame -->
        <rect class="phone-body" x="0" y="0" width="160" height="300" rx="20" />
        <!-- Screen -->
        <rect class="phone-screen" x="8" y="10" width="144" height="280" rx="12" />
        <!-- Notch/Speaker -->
        <rect class="phone-camera" x="60" y="18" width="40" height="4" rx="2" />
        <!-- Front Camera -->
        <circle class="phone-camera" cx="110" cy="20" r="3" />
        
        <text x="80" y="340" text-anchor="middle" class="view-label">Frente (Tela)</text>
    </g>

    <!-- BACK VIEW -->
    <g transform="translate(360, 40)">
        <!-- Phone Frame -->
        <rect class="phone-body" x="0" y="0" width="160" height="300" rx="20" />
        <!-- Camera Module -->
        <rect x="15" y="15" width="50" height="50" rx="8" fill="#dee2e6" stroke="#adb5bd" />
        <circle class="phone-camera" cx="30" cy="30" r="8" />
        <circle class="phone-camera" cx="50" cy="50" r="8" />
        <circle class="phone-camera" cx="50" cy="30" r="4" fill="#666" /> <!-- Flash -->
        
        <!-- Logo Placeholder -->
        <circle cx="80" cy="150" r="15" fill="#dee2e6" opacity="0.5" />

        <text x="80" y="340" text-anchor="middle" class="view-label">Verso (Carcaça)</text>
    </g>

    <!-- Side Buttons (Visual Only) -->
    <rect class="phone-button" x="75" y="100" width="5" height="30" rx="2" /> <!-- Power on Front -->
    <rect class="phone-button" x="240" y="100" width="5" height="30" rx="2" /> <!-- Power on Side View (simulated) -->
</svg>
