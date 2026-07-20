<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#b91c1c">
        @routes
        @vite('resources/js/app.js')
        @vite('resources/css/app.css')
        @inertiaHead
    </head>
    <body>
        @inertia
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('PWA: ServiceWorker berhasil didaftarkan dengan scope: ', registration.scope);
                }, function(err) {
                    console.log('PWA: ServiceWorker gagal didaftarkan: ', err);
                });
            });
        }
    </script>
    </body>
</html>