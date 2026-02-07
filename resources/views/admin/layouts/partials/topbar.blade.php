<!-- Topbar -->
<header class="sticky top-0 z-20 bg-ink-50/85 backdrop-blur border-b border-ink-200">
    <div class="h-16 px-4 sm:px-6 flex items-center gap-3">
        <button id="btnSidebar"
            class="lg:hidden w-10 h-10 rounded-2xl hover:bg-ink-100 transition flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </button>

        <div class="flex-1 min-w-0">
            <!-- Breadcrumb + Page info -->
            <nav class="flex items-center gap-2 text-sm text-ink-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-ink-700 transition">
                    Dashboard
                </a>
                <span class="text-ink-300">/</span>
                <span class="truncate text-ink-700 font-semibold">
                    @yield('page_title', 'Admin Panel')
                </span>
            </nav>

            <p class="mt-0.5 text-xs text-ink-500 truncate">
                @yield('page_desc', 'Kelola data dan pengaturan sistem akademik')
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button class="w-10 h-10 rounded-2xl hover:bg-ink-100 transition flex items-center justify-center">
                <svg class="w-6 h-6 text-ink-600" viewBox="0 0 24 24" fill="none">
                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="1.8"
                        stroke-linejoin="round" />
                    <path d="M10 19a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>

            <a href="{{ route('admin.activity_logs.index') }}"
                class="w-10 h-10 rounded-2xl hover:bg-ink-100 transition
                flex items-center justify-center">
                <svg class="w-6 h-6 text-ink-600" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="1.8" />
                    <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>

            {{-- Profile --}}
            <button id="btnProfile"
                class="flex items-center gap-2 pl-2 pr-3 py-2 rounded-2xl hover:bg-ink-100 transition">
                {{-- Avatar SVG (Admin/Person) --}}
                <div
                    class="w-9 h-9 rounded-full overflow-hidden border border-ink-200 bg-white grid place-items-center">
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="h-full w-full object-cover"
                            alt="Avatar">
                    @else
                        <svg class="h-5 w-5 text-ink-600" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8" />
                            <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                        </svg>
                    @endif
                </div>


                <svg class="w-4 h-4 text-ink-500" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Profile dropdown -->
    <div id="profileMenu"
        class="hidden absolute right-4 sm:right-6 top-[64px] w-56 bg-white border border-ink-200 rounded-2xl shadow-soft overflow-hidden">
        <a class="block px-4 py-3 hover:bg-ink-50 text-sm" href="{{ route('admin.profile.edit') }}">Profile</a>
        <div class="h-px bg-ink-200"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full text-left block px-4 py-3 hover:bg-ink-50 text-sm text-maroon-700 font-semibold">
                Logout
            </button>
        </form>

    </div>
</header>
