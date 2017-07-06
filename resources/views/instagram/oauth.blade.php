<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>INSTAGRAM</title>
        </style>
    </head>
    <body>
    <script type="text/javascript">

        function setCookie(name, value, minute) {
            var d = new Date();
            d.setTime(d.getTime() + (minute * 60 * 1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        };

        function parseStr(text, params)
        {
            var parts = text.split('=');
            for (var i = 0; i < parts.length; i++) {
                let name = parts[i],
                    value = parts[i + 1];
                params[name] = value;
                i++;
            };
        };

        var hash = window.location.hash || '';
        if (hash.length) {
            hash = hash.substring(1);

            var params = {};
            parseStr(hash, params);

            if (params.access_token !== undefined) {
                setCookie('instagram_access_token', params.access_token, 1440);
                window.location.href = '{{ route('instagram.sync.index') }}';
            };
        } else {
            // @todo oauth error
        };

    </script>
    </body>
</html>
