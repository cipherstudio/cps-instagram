<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>INSTAGRAM</title>

        <!-- Fonts -->
        {{--
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        --}}

        <!-- CSS Libs -->
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/app.css') }}">

        <!-- Styles -->
        <style>
           
        </style>
    </head>
    <body>
        <div id="cps" class="fade in">
            @include('instagram.partials.index.navbar')
            <div class="main-container">
                @yield('content')
            </div>
        </div>
    <script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>

    </body>
</html>
