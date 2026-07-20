<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#b91c1c">
    <title>@yield('title', 'Dashboard - Maroon Sneat Style')</title>

    {{-- Kalau kamu pakai Tailwind via Vite, aktifkan ini: --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    {{-- Kalau kamu masih mau pakai CDN (sesuai format kamu), biarin ini: --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @vite('resources/css/app.css')

    <!-- Chart.js (for bar chart) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50: "#fff1f2",
                            100: "#ffe4e6",
                            200: "#fecdd3",
                            300: "#fda4af",
                            400: "#fb7185",
                            500: "#e11d48",
                            600: "#be123c",
                            700: "#9f1239",
                            800: "#7f1d1d",
                            900: "#58151a"
                        },
                        ink: {
                            50: "#f8fafc",
                            100: "#f1f5f9",
                            200: "#e2e8f0",
                            300: "#cbd5e1",
                            400: "#94a3b8",
                            500: "#64748b",
                            600: "#475569",
                            700: "#334155",
                            800: "#1f2937",
                            900: "#0f172a"
                        }
                    },
                    boxShadow: {
                        soft: "0 12px 28px -14px rgba(15, 23, 42, .22)"
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            color-scheme: light;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-ink-50 text-ink-900 overflow-x-hidden">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        @include('admin.layouts.partials.sidebar')

        <!-- Backdrop (mobile) -->
        <div id="backdrop" class="fixed inset-0 bg-black/35 z-30 hidden lg:hidden"></div>

        <!-- Main -->
        <div class="flex-1 lg:pl-72 min-w-0">
            {{-- Topbar --}}
            @include('admin.layouts.partials.topbar')

            <!-- Content -->
            <main class="px-4 sm:px-6 py-6 min-w-0 overflow-x-hidden">
                @yield('content')
                <script>
                    if ('serviceWorker' in navigator) {
                        window.addEventListener('load', function() {
                            navigator.serviceWorker.register('/sw.js').then(function(registration) {
                                console.log('PWA Admin: ServiceWorker sukses terdaftar!');
                            }, function(err) {
                                console.log('PWA Admin: ServiceWorker gagal: ', err);
                            });
                        });
                    }
                </script>
            </main>

        </div>
    </div>

    {{-- Scripts (JS kamu dipindah ke sini) --}}
    @include('admin.layouts.partials.scripts')
    @stack('scripts')
</body>

</html>
