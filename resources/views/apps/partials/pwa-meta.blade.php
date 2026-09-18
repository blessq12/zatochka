{{-- Единые PWA meta для SPA (client / master / manager). --}}
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="{{ $themeColor ?? '#003859' }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $appleTitle ?? ($pwaName ?? 'Заточка') }}">
<link rel="manifest" href="{{ $manifestUrl }}">
<link rel="apple-touch-icon" href="{{ asset($appleTouchIcon) }}">
