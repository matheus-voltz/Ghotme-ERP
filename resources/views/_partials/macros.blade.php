@php
$width = $width ?? '32';
$height = $height ?? '22';
@endphp

<span class="text-primary">
  <svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 32 24" fill="none"
    xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M16 0L4 6V18L16 24L28 18V10H22V15L16 18L10 15V9L16 6L22 9V3L16 0Z" fill="currentColor" />
    <path opacity="0.1" d="M16 0L22 3V9L16 6V0Z" fill="#161616" />
    <path opacity="0.1" d="M16 24L16 18L22 15V10H28V18L16 24Z" fill="#161616" />
  </svg>
</span>