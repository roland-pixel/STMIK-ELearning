<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STMIK Indonesia Banjarmasin — Portal Akademik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        crimson: {
                            50: '#fff1f1',
                            100: '#ffe0e0',
                            200: '#ffc0c0',
                            300: '#ff8c8c',
                            400: '#ff5050',
                            500: '#f92222',
                            600: '#e10c0c',
                            700: '#c00a0a',
                            800: '#9e0c0c',
                            900: '#820f0f',
                            950: '#470303',
                        },
                        gold: {
                            300: '#f5d88a',
                            400: '#edc455',
                            500: '#d4a017',
                        }
                    },
                    fontFamily: {
                        display: ['Playfair Display', 'serif'],
                        body: ['DM Sans', 'sans-serif'],
                    },
                    animation: {
                        'fade-up': 'fadeUp 0.7s ease forwards',
                        'fade-in': 'fadeIn 0.6s ease forwards',
                        'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeUp: {
                            '0%': {
                                opacity: 0,
                                transform: 'translateY(30px)'
                            },
                            '100%': {
                                opacity: 1,
                                transform: 'translateY(0)'
                            }
                        },
                        fadeIn: {
                            '0%': {
                                opacity: 0
                            },
                            '100%': {
                                opacity: 1
                            }
                        },
                        pulseSoft: {
                            '0%,100%': {
                                opacity: 1
                            },
                            '50%': {
                                opacity: 0.6
                            }
                        },
                        float: {
                            '0%,100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-12px)'
                            }
                        },
                    }
                }
            }
        }
    </script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
        }

        /* Hero gradient mesh */
        .hero-bg {
            background:
                radial-gradient(ellipse 80% 60% at 70% 30%, rgba(193, 10, 10, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 20% 80%, rgba(193, 10, 10, 0.10) 0%, transparent 55%),
                linear-gradient(160deg, #0f0303 0%, #1a0505 40%, #230808 70%, #1a0404 100%);
        }

        .hero-grid {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Card hover glow */
        .card-hover {
            transition: transform 0.35s cubic-bezier(.22, .68, 0, 1.4), box-shadow 0.35s ease;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(193, 10, 10, 0.18);
        }

        /* Stat counter line */
        .stat-line::after {
            content: '';
            display: block;
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #d4a017, #f5d88a);
            border-radius: 9px;
            margin: 10px auto 0;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f0303;
        }

        ::-webkit-scrollbar-thumb {
            background: #9e0c0c;
            border-radius: 99px;
        }

        /* Animate on load */
        .delay-100 {
            animation-delay: 0.10s;
            opacity: 0;
        }

        .delay-200 {
            animation-delay: 0.20s;
            opacity: 0;
        }

        .delay-300 {
            animation-delay: 0.30s;
            opacity: 0;
        }

        .delay-400 {
            animation-delay: 0.40s;
            opacity: 0;
        }

        .delay-500 {
            animation-delay: 0.50s;
            opacity: 0;
        }

        .delay-600 {
            animation-delay: 0.60s;
            opacity: 0;
        }

        .delay-700 {
            animation-delay: 0.70s;
            opacity: 0;
        }

        /* Badge pill */
        .badge {
            font-size: 0.68rem;
            letter-spacing: 0.06em;
        }

        /* Section separator wave */
        .wave-divider svg {
            display: block;
        }

        /* Navbar glass */
        .navbar-glass {
            backdrop-filter: blur(16px) saturate(1.4);
            -webkit-backdrop-filter: blur(16px) saturate(1.4);
            background: rgba(15, 3, 3, 0.75);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* Jurusan card accent border */
        .jurusan-card {
            border-top: 4px solid;
            border-image: linear-gradient(90deg, #c00a0a, #d4a017) 1;
        }

        /* MK badge color maps */
        .badge-umum {
            background: rgba(193, 10, 10, 0.15);
            color: #ff8c8c;
        }

        .badge-spesial {
            background: rgba(212, 160, 23, 0.15);
            color: #edc455;
        }

        /* Footer wave */
        .footer-wave {
            background: linear-gradient(180deg, #0f0303 0%, #080101 100%);
        }
    </style>
</head>

<body class="bg-[#0f0303] text-white overflow-x-hidden">

    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║            NAVBAR                ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <nav class="navbar-glass fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-10 h-16 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/stmiklogo.png') }}" alt="Logo STMIK" class="w-10 h-10 object-contain">
                <div class="leading-tight">
                    <p class="text-xs text-crimson-400 font-semibold tracking-widest uppercase"
                        style="font-family:'DM Sans'">STMIK</p>
                    <p class="text-sm font-bold text-white -mt-0.5">Indonesia Banjarmasin</p>
                </div>
            </div>

            <!-- Nav links -->
            <div class="hidden md:flex items-center gap-8">
                <a href="#tentang" class="text-sm text-gray-300 hover:text-white transition">Tentang</a>
                <a href="#statistik" class="text-sm text-gray-300 hover:text-white transition">Statistik</a>
                <a href="#jurusan" class="text-sm text-gray-300 hover:text-white transition">Jurusan</a>
                <a href="#matakuliah" class="text-sm text-gray-300 hover:text-white transition">Mata Kuliah</a>
            </div>

            <!-- CTA -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="hidden md:inline-flex px-4 py-2 text-sm font-semibold text-white bg-crimson-700 hover:bg-crimson-600 rounded-lg transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm text-gray-300 hover:text-white transition font-medium">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║              HERO                ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section class="hero-bg hero-grid relative min-h-screen flex items-center pt-16 overflow-hidden">

        <!-- Decorative blobs -->
        <div
            class="absolute top-1/4 right-0 w-[600px] h-[600px] rounded-full bg-crimson-700/10 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-1/4 w-[400px] h-[300px] rounded-full bg-crimson-900/20 blur-2xl pointer-events-none">
        </div>

        <!-- Floating abstract shape -->
        <div class="absolute top-32 right-8 md:right-24 animate-float pointer-events-none opacity-20 hidden lg:block">
            <svg width="340" height="340" viewBox="0 0 340 340" fill="none">
                <circle cx="170" cy="170" r="140" stroke="url(#g1)" stroke-width="1.5"
                    stroke-dasharray="8 6" />
                <circle cx="170" cy="170" r="100" stroke="url(#g1)" stroke-width="1" stroke-dasharray="4 8"
                    opacity="0.6" />
                <circle cx="170" cy="170" r="60" fill="rgba(193,10,10,0.08)" />
                <defs>
                    <linearGradient id="g1" x1="0" y1="0" x2="340" y2="340">
                        <stop offset="0%" stop-color="#c00a0a" />
                        <stop offset="100%" stop-color="#d4a017" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-10 w-full py-24">
            <div class="max-w-3xl">
                <!-- Semester aktif badge -->
                @if ($semesterAktif)
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-crimson-900/60 border border-crimson-700/50 mb-8 animate-fade-up delay-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse-soft"></span>
                        <span class="text-xs font-medium text-crimson-200 badge tracking-wider uppercase">
                            Semester Aktif: {{ $semesterAktif->nama_semester }}
                        </span>
                    </div>
                @endif

                <h1 class="font-display text-5xl md:text-7xl font-black leading-[1.05] animate-fade-up delay-200">
                    Portal <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-crimson-400 via-crimson-300 to-gold-400">Akademik</span><br>
                    STMIK Indonesia<br>
                    <span class="text-white/60 text-4xl md:text-5xl font-bold">Banjarmasin</span>
                </h1>

                <p class="mt-6 text-lg text-gray-300 leading-relaxed max-w-xl animate-fade-up delay-300">
                    Sistem informasi akademik terpadu — kelola kelas, penilaian, dan bimbingan dalam satu platform yang
                    modern dan efisien.
                </p>

                <div class="mt-10 flex flex-wrap gap-4 animate-fade-up delay-400">
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-7 py-3.5 border border-white/20 hover:border-white/40 text-white font-semibold rounded-xl transition text-base backdrop-blur-sm">
                            Masuk ke Sistem →
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 px-7 py-3.5 bg-gradient-to-r from-crimson-700 to-crimson-600 hover:from-crimson-600 hover:to-crimson-500 text-white font-semibold rounded-xl shadow-2xl shadow-crimson-900/60 transition text-base">
                            Buka Dashboard →
                        </a>
                    @endguest
                </div>

                <!-- Trust badges -->
                <div class="mt-12 flex flex-wrap items-center gap-6 animate-fade-up delay-500">
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        Penilaian Online
                    </div>
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        Bimbingan Akademik
                    </div>
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        Rekap Nilai Otomatis
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom wave -->
        <div class="absolute bottom-0 left-0 right-0 wave-divider">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0 40 C360 80 1080 0 1440 40 L1440 80 L0 80 Z" fill="#0f0303" />
            </svg>
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║            STATISTIK             ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section id="statistik" class="py-20 bg-[#0f0303]">
        <div class="max-w-7xl mx-auto px-6 lg:px-10">

            <div class="text-center mb-14">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-crimson-400 mb-3">Dalam Angka</p>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Statistik <span
                        class="text-crimson-400">Kampus</span></h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Mahasiswa -->
                <div
                    class="relative bg-gradient-to-br from-[#1c0606] to-[#150303] border border-white/8 rounded-2xl p-8 text-center card-hover overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-crimson-600/5 to-transparent pointer-events-none">
                    </div>
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-crimson-900/60 flex items-center justify-center">
                        <svg class="w-6 h-6 text-crimson-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                        </svg>
                    </div>
                    <p class="font-display text-4xl font-black text-white stat-line">
                        {{ number_format($stats['total_mahasiswa']) }}
                    </p>
                    <p class="mt-4 text-sm text-gray-400">Mahasiswa Aktif</p>
                </div>

                <!-- Dosen -->
                <div
                    class="relative bg-gradient-to-br from-[#1c0606] to-[#150303] border border-white/8 rounded-2xl p-8 text-center card-hover overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-gold-500/5 to-transparent pointer-events-none">
                    </div>
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gold-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gold-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <p class="font-display text-4xl font-black text-white stat-line">
                        {{ number_format($stats['total_dosen']) }}
                    </p>
                    <p class="mt-4 text-sm text-gray-400">Tenaga Dosen</p>
                </div>

                <!-- Kelas -->
                <div
                    class="relative bg-gradient-to-br from-[#1c0606] to-[#150303] border border-white/8 rounded-2xl p-8 text-center card-hover overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-crimson-600/5 to-transparent pointer-events-none">
                    </div>
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-crimson-900/60 flex items-center justify-center">
                        <svg class="w-6 h-6 text-crimson-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <p class="font-display text-4xl font-black text-white stat-line">
                        {{ number_format($stats['total_kelas']) }}
                    </p>
                    <p class="mt-4 text-sm text-gray-400">Kelas Tersedia</p>
                </div>

                <!-- Mata Kuliah -->
                <div
                    class="relative bg-gradient-to-br from-[#1c0606] to-[#150303] border border-white/8 rounded-2xl p-8 text-center card-hover overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-gold-500/5 to-transparent pointer-events-none">
                    </div>
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gold-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gold-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <p class="font-display text-4xl font-black text-white stat-line">
                        {{ number_format($stats['total_mk']) }}
                    </p>
                    <p class="mt-4 text-sm text-gray-400">Mata Kuliah</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║             FITUR                ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section id="tentang" class="py-24 relative overflow-hidden">
        <!-- Background accent -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#0f0303] via-[#130404] to-[#0f0303] pointer-events-none">
        </div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-px h-full bg-gradient-to-b from-transparent via-crimson-800/30 to-transparent pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-10 relative">
            <div class="text-center mb-16">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-crimson-400 mb-3">Kenapa STMIK?</p>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Fitur Unggulan <span
                        class="text-crimson-400">Platform</span></h2>
                <p class="mt-4 text-gray-400 max-w-xl mx-auto">Dirancang untuk memudahkan proses belajar-mengajar dan
                    administrasi akademik secara menyeluruh.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @php
                    $fitur = [
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                            'judul' => 'Penilaian Online',
                            'desc' =>
                                'Buat soal essai, pilihan ganda, dan upload file. Sistem menghitung nilai otomatis berdasarkan bobot tugas, UTS, dan UAS.',
                            'warna' => 'crimson',
                        ],
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                            'judul' => 'Bimbingan Akademik',
                            'desc' =>
                                'Kelola proses bimbingan skripsi dan tugas akhir antara mahasiswa dan dosen pembimbing secara terintegrasi.',
                            'warna' => 'gold',
                        ],
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                            'judul' => 'Rekap Nilai Otomatis',
                            'desc' =>
                                'Nilai akhir dihitung otomatis dengan konversi ke nilai huruf dan indeks prestasi sesuai standar akademik.',
                            'warna' => 'crimson',
                        ],
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
                            'judul' => 'Manajemen Kelas',
                            'desc' =>
                                'Dosen dapat membuat kelas, mengundang mahasiswa dengan kode unik, dan mendistribusikan materi dengan mudah.',
                            'warna' => 'gold',
                        ],
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                            'judul' => 'Kurikulum & Materi',
                            'desc' =>
                                'Distribusi materi kuliah berupa file dan link URL, tersusun rapi per pertemuan untuk setiap kelas.',
                            'warna' => 'crimson',
                        ],
                        [
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                            'judul' => 'Log Aktivitas',
                            'desc' =>
                                'Setiap perubahan data tercatat secara audit trail, memberikan keamanan dan transparansi penuh pada sistem.',
                            'warna' => 'gold',
                        ],
                    ];
                @endphp

                @foreach ($fitur as $i => $f)
                    <div class="group relative bg-[#150303]/80 border border-white/[0.07] rounded-2xl p-7 card-hover cursor-default"
                        style="animation-delay:{{ $i * 0.08 }}s">
                        <div
                            class="w-12 h-12 mb-5 rounded-xl flex items-center justify-center
                    {{ $f['warna'] === 'crimson' ? 'bg-crimson-900/60' : 'bg-gold-500/10' }}">
                            <svg class="w-6 h-6 {{ $f['warna'] === 'crimson' ? 'text-crimson-400' : 'text-gold-400' }}"
                                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                {!! $f['icon'] !!}
                            </svg>
                        </div>
                        <h3 class="font-display text-lg font-bold text-white mb-2">{{ $f['judul'] }}</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">{{ $f['desc'] }}</p>

                        <!-- Bottom hover line -->
                        <div
                            class="absolute bottom-0 left-0 right-0 h-0.5 rounded-b-2xl bg-gradient-to-r from-crimson-600 to-gold-400 scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║             JURUSAN              ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section id="jurusan" class="py-24 bg-gradient-to-b from-[#0f0303] to-[#120404]">
        <div class="max-w-7xl mx-auto px-6 lg:px-10">

            <div class="text-center mb-16">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-crimson-400 mb-3">Program Studi</p>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Jurusan <span
                        class="text-crimson-400">Kami</span></h2>
                <p class="mt-4 text-gray-400 max-w-xl mx-auto">Pilih program studi yang sesuai dengan passion dan karir
                    impianmu di bidang teknologi informasi.</p>
            </div>

            @if ($jurusans->isEmpty())
                <p class="text-center text-gray-500">Data jurusan belum tersedia.</p>
            @else
                <div class="grid md:grid-cols-{{ min($jurusans->count(), 3) }} gap-6">
                    @foreach ($jurusans as $j)
                        <div
                            class="relative bg-gradient-to-br from-[#1c0606] to-[#130303] rounded-2xl overflow-hidden card-hover group border border-white/[0.06] jurusan-card">

                            <!-- Top gradient band -->
                            <div class="h-2 bg-gradient-to-r from-crimson-700 to-gold-400 w-full"></div>

                            <div class="p-8">
                                <!-- Jenjang badge -->
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-xs font-bold badge tracking-widest uppercase
                        {{ $j->jenjang === 'S1' ? 'bg-crimson-900/60 text-crimson-300 border border-crimson-700/40' : 'bg-gold-500/10 text-gold-400 border border-gold-500/30' }}">
                                    {{ $j->jenjang }}
                                </span>

                                <h3 class="font-display text-2xl font-bold text-white mt-4 mb-2 leading-tight">
                                    {{ $j->nama_jurusan }}
                                </h3>
                                <p class="text-xs text-gray-500 font-mono tracking-wider mb-6">{{ $j->kode_jurusan }}
                                </p>

                                <!-- Mahasiswa count -->
                                <div
                                    class="flex items-center gap-3 p-4 bg-black/20 rounded-xl border border-white/[0.04]">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-crimson-900/50 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-crimson-400" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-display text-2xl font-bold text-white leading-none">
                                            {{ $j->total_mahasiswa ?? 0 }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Mahasiswa Aktif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║           MATA KULIAH            ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section id="matakuliah" class="py-24 bg-[#0f0303]">
        <div class="max-w-7xl mx-auto px-6 lg:px-10">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-14">
                <div>
                    <p class="text-xs font-bold tracking-[0.2em] uppercase text-crimson-400 mb-3">Kurikulum</p>
                    <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Mata Kuliah <span
                            class="text-crimson-400">Unggulan</span></h2>
                </div>
                <p class="text-sm text-gray-500 md:text-right max-w-xs">Beberapa mata kuliah representatif dari program
                    studi kami.</p>
            </div>

            @if ($mataKuliahs->isEmpty())
                <p class="text-center text-gray-500">Data mata kuliah belum tersedia.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($mataKuliahs as $mk)
                        <div
                            class="group relative bg-gradient-to-br from-[#1c0606] to-[#130303] border border-white/[0.06] rounded-2xl p-6 card-hover flex flex-col gap-4">

                            <!-- Header -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <h4
                                        class="font-display text-lg font-bold text-white leading-snug group-hover:text-crimson-300 transition">
                                        {{ $mk->nama_mk }}
                                    </h4>
                                </div>
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-crimson-900/40 border border-crimson-800/40 flex items-center justify-center">
                                    <span
                                        class="font-display text-base font-black text-crimson-300">{{ $mk->sks }}</span>
                                </div>
                            </div>

                            <!-- SKS label -->
                            <p class="text-xs text-gray-500">{{ $mk->sks }} SKS</p>

                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mt-auto">
                                <!-- Jenis MK -->
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold badge
                        {{ $mk->jenis_mk === 'Umum' ? 'badge-umum' : 'badge-spesial' }}">
                                    {{ $mk->jenis_mk }}
                                </span>

                                <!-- Kategori MK -->
                                @if ($mk->kategori_mk)
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold badge bg-white/5 text-gray-400 border border-white/10">
                                        {{ $mk->kategori_mk }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║              CTA                 ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-6 lg:px-10">
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-crimson-900 via-crimson-800 to-[#4a0808] p-12 md:p-20 text-center">

                <!-- Decorative grid -->
                <div class="absolute inset-0 opacity-10"
                    style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 32px 32px;">
                </div>

                <!-- Glowing orbs -->
                <div
                    class="absolute top-0 right-0 w-72 h-72 bg-crimson-500/20 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-64 h-64 bg-gold-500/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative">
                    <p class="text-xs font-bold tracking-[0.2em] uppercase text-crimson-200/70 mb-4">Bergabung Sekarang
                    </p>
                    <h2 class="font-display text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
                        Wujudkan Masa Depan<br>
                        <span class="text-gold-400">Digitalmu</span> Bersama Kami
                    </h2>
                    <p class="text-lg text-crimson-100/70 mb-10 max-w-xl mx-auto">
                        Bergabung dengan ribuan mahasiswa dan ratusan dosen di ekosistem akademik digital STMIK
                        Indonesia Banjarmasin.
                    </p>

                    @guest
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">

                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 border border-white/20 text-white font-semibold rounded-xl hover:bg-white/20 transition text-base backdrop-blur-sm">
                                Sudah Punya Akun
                            </a>
                        </div>
                    @else
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-crimson-700 font-bold rounded-xl hover:bg-gray-50 transition text-base shadow-2xl shadow-black/30">
                            Buka Dashboard
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>


    <!-- ╔══════════════════════════════════╗ -->
    <!-- ║              FOOTER              ║ -->
    <!-- ╚══════════════════════════════════╝ -->
    <footer class="footer-wave border-t border-white/[0.05]">
        <div class="max-w-7xl mx-auto px-6 lg:px-10 py-14">
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('assets/stmiklogo.png') }}" alt="Logo STMIK"
                            class="w-10 h-10 object-contain">
                        <div>
                            <p class="text-xs text-crimson-400 font-semibold tracking-widest uppercase">STMIK</p>
                            <p class="text-sm font-bold text-white -mt-0.5">Indonesia Banjarmasin</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Sekolah Tinggi Manajemen Informatika dan Komputer Indonesia Banjarmasin — mencetak SDM unggul di
                        bidang teknologi informasi.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h5 class="text-xs font-bold tracking-widest uppercase text-gray-500 mb-4">Navigasi</h5>
                    <ul class="space-y-2">
                        <li><a href="#tentang" class="text-sm text-gray-400 hover:text-white transition">Tentang</a>
                        </li>
                        <li><a href="#statistik"
                                class="text-sm text-gray-400 hover:text-white transition">Statistik</a></li>
                        <li><a href="#jurusan" class="text-sm text-gray-400 hover:text-white transition">Jurusan</a>
                        </li>
                        <li><a href="#matakuliah" class="text-sm text-gray-400 hover:text-white transition">Mata
                                Kuliah</a></li>
                    </ul>
                </div>

                <!-- Info -->
                <div>
                    <h5 class="text-xs font-bold tracking-widest uppercase text-gray-500 mb-4">Informasi</h5>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2 text-sm text-gray-400">
                            <svg class="w-4 h-4 text-crimson-500 mt-0.5 flex-shrink-0" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Banjarmasin, Kalimantan Selatan
                        </li>
                        @if ($semesterAktif)
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <svg class="w-4 h-4 text-crimson-500 mt-0.5 flex-shrink-0" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>
                                    <strong class="text-white">{{ $semesterAktif->nama_semester }}</strong><br>
                                    {{ \Carbon\Carbon::parse($semesterAktif->tanggal_mulai)->format('d M Y') }} —
                                    {{ \Carbon\Carbon::parse($semesterAktif->tanggal_selesai)->format('d M Y') }}
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Divider & copyright -->
            <div
                class="mt-12 pt-8 border-t border-white/[0.06] flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-600">
                    &copy; {{ date('Y') }} STMIK Indonesia Banjarmasin. Hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-1 text-xs text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse-soft"></span>
                    Sistem berjalan normal
                </div>
            </div>
        </div>
    </footer>

    <!-- Smooth scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll-triggered fade-up for sections
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeUp 0.6s ease forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12
        });

        document.querySelectorAll('section > div').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    </script>
</body>

</html>
