@php
    $petType = $petType ?? 'dog';
@endphp

<div class="pet-blueprint-container" style="max-width: 600px; margin: auto;">
    <svg id="car-blueprint" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <defs>
            <style>
                .pet-body { fill: #f1f3f5; stroke: #868e96; stroke-width: 5; stroke-linejoin: round; }
                .view-label { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 24px; fill: #adb5bd; font-weight: 500; }
            </style>
        </defs>
        
        @if($petType === 'cat')
            <title>Silhueta de Gato</title>
            <path class="pet-body" d="M378.1,253.9c-2.7-3.6-5.8-6.9-9.1-10c-35.1-32.9-82.4-52.6-133.6-52.6c-48,0-92.4,17.4-127,47.1 c-24,20.6-41.9,47.8-51.4,78.7c-9.3,30.3-10.1,62.7-2.3,94.2c9.5,38.4,32.2,71.9,64.4,94.8c34.9,24.9,76.5,36.5,118.7,33.1 c80-6.4,143-69.4,149.4-149.4C400,323.5,393.3,286.7,378.1,253.9z"/>
            <text x="256" y="480" text-anchor="middle" class="view-label">Gato</text>
        @else
            <title>Silhueta de Cão</title>
            <path class="pet-body" d="M352,128.2c-1.3-0.1-2.7-0.1-4-0.1c-44.2,0-80,35.8-80,80s35.8,80,80,80s80-35.8,80-80c0-21.3-8.3-40.6-21.9-55.2 c-2.9,33.8-21.2,63.5-47.5,82.2C355.8,252.3,353.9,240.2,352,128.2z M160,128c-44.2,0-80,35.8-80,80s35.8,80,80,80s80-35.8,80-80 S204.2,128,160,128z M448,352c0-53-43-96-96-96H160c-53,0-96,43-96,96v64h384V352z"/>
            <text x="256" y="480" text-anchor="middle" class="view-label">Cão</text>
        @endif
    </svg>
</div>
