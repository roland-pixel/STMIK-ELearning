<!-- Sidebar -->
@php
    function navItemClass($patterns)
    {
        $active = request()->routeIs($patterns);
        return $active ? 'bg-maroon-600/10 text-maroon-800 ring-1 ring-maroon-700/10' : 'text-ink-700 hover:bg-ink-100';
    }

    function navIconWrapClass($patterns)
    {
        $active = request()->routeIs($patterns);
        return $active ? 'bg-white border-maroon-200/70 text-maroon-800' : 'bg-white border-ink-200 text-ink-600';
    }

    function navLabelClass($patterns)
    {
        $active = request()->routeIs($patterns);
        return $active ? 'font-semibold' : 'font-medium';
    }
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full lg:translate-x-0 transition-transform
           bg-white border-r border-ink-200">

    {{-- Brand --}}
    <div class="h-16 px-5 flex items-center gap-3 border-b border-ink-200/70">
        {{-- Logo wrap --}}
        <div
            class="w-11 h-11 rounded-2xl bg-maroon-600/10 ring-1 ring-maroon-700/10 flex items-center justify-center overflow-hidden">
            {{-- Logo image (dibikin proper) --}}
            <img src="{{ asset('assets/stmiklogo.png') }}" alt="STMIK Logo"
                class="h-9 w-9 object-contain drop-shadow-sm" />
        </div>

        <div class="min-w-0">
            <p class="font-semibold text-[15px] leading-tight text-ink-900 truncate">Admin Panel</p>
            <p class="text-xs text-ink-500 -mt-0.5 truncate">Sistem Akademik</p>
        </div>
    </div>

    <nav class="px-4 pb-6 overflow-y-auto scrollbar-hide h-[calc(100vh-64px)]">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
            class="mt-4 flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.dashboard']) }}">
            <span
                class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.dashboard']) }}">
                {{-- Home / Dashboard --}}
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5Z" stroke="currentColor"
                        stroke-width="1.8" stroke-linejoin="round" />
                </svg>
            </span>
            <span class="{{ navLabelClass(['admin.dashboard']) }}">Dashboard</span>
        </a>

        {{-- Section --}}
        <div class="mt-6">
            <p class="px-3 text-xs uppercase tracking-wider text-ink-400">Master Data</p>

            <div class="mt-2 space-y-1">

                {{-- Jurusan --}}
                <a href="{{ route('admin.jurusans.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.jurusans.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.jurusans.*']) }}">
                        {{-- Layers / Jurusan --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3 3 8l9 5 9-5-9-5Z" stroke="currentColor" stroke-width="1.8"
                                stroke-linejoin="round" />
                            <path d="M3 12l9 5 9-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M3 16l9 5 9-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.jurusans.*']) }}">Kelola Jurusan</span>
                </a>

                {{-- Semester --}}
                <a href="{{ route('admin.semesters.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.semesters.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.semesters.*']) }}">
                        {{-- Calendar --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M7 3v3M17 3v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M4 7h16" stroke="currentColor" stroke-width="1.8" />
                            <path d="M6 5h12a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"
                                stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M8 11h4M8 15h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.semesters.*']) }}">Kelola Semester</span>
                </a>

                {{-- Mahasiswa --}}
                <a href="{{ route('admin.mahasiswas.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.mahasiswas.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.mahasiswas.*']) }}">
                        {{-- User / Student --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8" />
                            <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.mahasiswas.*']) }}">Kelola Mahasiswa</span>
                </a>

                {{-- Dosen --}}
                <a href="{{ route('admin.dosens.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.dosens.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.dosens.*']) }}">
                        {{-- User badge / Lecturer --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8" />
                            <path d="M6 21a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                            <path d="M18.5 8.5 21 10l-2.5 1.5L16 10l2.5-1.5Z" stroke="currentColor" stroke-width="1.6"
                                stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.dosens.*']) }}">Kelola Dosen</span>
                </a>

                {{-- Mata Kuliah --}}
                <a href="{{ route('admin.mata_kuliahs.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.mata_kuliahs.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.mata_kuliahs.*']) }}">
                        {{-- Book --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M6 4h11a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2V6a2 2 0 0 1 2-2Z"
                                stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M8 8h9M8 12h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.mata_kuliahs.*']) }}">Kelola Mata Kuliah</span>
                </a>

                {{-- Kelas --}}
                <a href="{{ route('admin.kelases.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.kelases.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.kelases.*']) }}">
                        {{-- Chalkboard / Class --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 5h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"
                                stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M7 21h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M9 9h6M9 12h4" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.kelases.*']) }}">Kelola Kelas</span>
                </a>

                {{-- Penilaian Manual --}}
                <a href="{{ route('admin.input_nilai_manual.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.input_nilai_manual.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.input_nilai_manual.*']) }}">
                        {{-- Link / Mentoring --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M10 13a5 5 0 0 1 0-7l1.2-1.2a5 5 0 0 1 7 7L17 13" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14 11a5 5 0 0 1 0 7L12.8 19.2a5 5 0 1 1-7-7L7 11" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.input_nilai_manual.*']) }}">Kelola Penilaian Manual</span>
                </a>

                {{-- Bimbingan --}}
                <a href="{{ route('admin.bimbingans.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ navItemClass(['admin.bimbingans.*']) }}">
                    <span
                        class="w-9 h-9 rounded-xl border flex items-center justify-center {{ navIconWrapClass(['admin.bimbingans.*']) }}">
                        {{-- Link / Mentoring --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M10 13a5 5 0 0 1 0-7l1.2-1.2a5 5 0 0 1 7 7L17 13" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14 11a5 5 0 0 1 0 7L12.8 19.2a5 5 0 1 1-7-7L7 11" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="{{ navLabelClass(['admin.bimbingans.*']) }}">Kelola Bimbingan</span>
                </a>

                {{-- Rekap Nilai --}}
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition text-ink-700 hover:bg-ink-100"
                    href="#">
                    <span
                        class="w-9 h-9 rounded-xl bg-white border border-ink-200 flex items-center justify-center text-ink-600">
                        {{-- Chart --}}
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M4 19h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M8 16v-5M12 16V8m4 8v-3" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="font-medium">Rekap Nilai</span>
                </a>

            </div>
        </div>

        {{-- Logout --}}
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <div class="mt-6">
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-2xl hover:bg-maroon-50 transition text-maroon-800">
                    <span
                        class="w-9 h-9 rounded-xl bg-white border border-maroon-200/70 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M10 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"
                                stroke="currentColor" stroke-width="1.8" />
                            <path d="M15 12H3m0 0 3-3m-3 3 3 3" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="font-semibold">Logout</span>
                </button>
            </div>
        </form>
    </nav>
</aside>
