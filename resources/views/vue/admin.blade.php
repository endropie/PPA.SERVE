<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>AdminLTE 3 | Starter</title>

  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <!-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"> -->

</head>
<body class="hold-transition sidebar-mini">
  <div class="wrapper" id="app">
    <router-view></router-view>
  </div>

  <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
