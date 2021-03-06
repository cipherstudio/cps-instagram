<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ Voyager::setting('title') }}</title>

        <!-- Fonts -->
        {{--
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        --}}

        <!-- CSS Libs -->
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/app.css') }}">

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/x-icon">

        <!-- Styles -->
        <style>
           
        </style>
    </head>
    <body class="home">
        <div id="cps" class="app-container fade in">
            @include('instagram.partials.index.navbar')
            <div class="main-container">
                @yield('content')
                <footer>
                © CPS CHAPS
                </footer>
            </div>
            
        </div>
        <script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>
        @yield('javascript')
    </body>
</html>
