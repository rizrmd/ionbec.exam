<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @php
    $client = request()->attributes->get('client');
    $title = $client ? $client->name : config('app.name', 'IONBEC');
    $favicon = $client && $client->logo_url ? $client->logo_url : '/images/logo.png';
  @endphp

  <title inertia>{{ $title }}</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ $favicon }}">

  <!-- Fonts -->
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">

  <!-- Scripts -->
  <script type="text/javascript">
    @php
      // Use the @routes directive which automatically handles the current domain
      $ziggy = new \Tightenco\Ziggy\Ziggy();
      $ziggyArray = $ziggy->toArray();
      // Override the URL to be the current domain for proper route generation
      $ziggyArray['url'] = request()->getSchemeAndHttpHost();
      $ziggyArray['port'] = null;
    @endphp
    const Ziggy = {!! json_encode($ziggyArray) !!};
  </script>
  <script src="{{ mix('js/app.js') }}" defer></script>
</head>
<body class="font-sans antialiased h-full bg-gray-50">
@inertia
</body>
</html>
