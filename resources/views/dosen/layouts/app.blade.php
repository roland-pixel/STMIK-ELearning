<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dosen')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r border-slate-200/70 hidden md:flex md:flex-col">
            <div class="p-4 border-b border-slate-200/70">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-indigo-600 text-white grid place-items-center">
                        {{-- Icon dosen --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2 1 7l11 5 9-4.09V17h2V7L12 2Zm-7 9.27V17c0 2.76 3.13 5 7 5s7-2.24 7-5v-5.73l-7 3.18-7-3.18Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold leading-tight">Portal Dosen</div>
                        <div class="text-xs text-slate-500">E-Learning</div>
                    </div>
                </div>
            </div>

            <nav class="p-3 space-y-1">
                <a href="{{ route('dosen.dashboard') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">
                    Dashboard
                </a>

                {{-- contoh --}}
                {{-- <a href="{{ route('dosen.kelas.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">Kelas</a> --}}
                {{-- <a href="{{ route('dosen.materi.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">Materi</a> --}}
            </nav>

            <div class="mt-auto p-4 border-t border-slate-200/70">
                <div class="text-xs text-slate-500">Dosen:</div>
                <div class="text-sm font-medium text-slate-800">
                    {{ auth()->user()->nama_lengkap ?? '-' }}
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 flex flex-col">
            {{-- Topbar --}}
            <header class="bg-white border-b border-slate-200/70">
                <div class="px-4 md:px-6 py-3 flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-500">@yield('page_desc', 'Area dosen')</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            class="hidden sm:inline-flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs font-medium">
                            Dosen
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-xl px-3 py-2 text-sm bg-slate-900 text-white hover:bg-slate-800 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
