<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="OuK7AMFWCR_8z9_ChgQM1OXnt0qfVLD7hrjR9a1NAvw" />
    <title>E-Classroom | STMIK Indonesia Banjarmasin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        html {
            scroll-behavior: smooth;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #f8fafc;
            overflow-x: hidden;
        }

        .blob {
            filter: blur(90px);
            animation: float 8s ease-in-out infinite;
            opacity: .45;
            z-index: -1;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) scale(1);
            }

            50% {
                transform: translateY(-35px) scale(1.05);
            }
        }

        .glass {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, .75);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .glass-nav {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, .85);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }

        .hero-text {
            animation: fadeUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
            top: 0;
            left: 0;
        }

        .text-gradient {
            background: linear-gradient(135deg, #e11d48, #be123c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="text-slate-700 selection:bg-rose-200 selection:text-rose-900">

    <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="glass-nav py-4 px-6 md:px-12 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3 cursor-pointer">
                <div class="w-10 h-10 flex items-center justify-center text-white ">
                    <img src="{{ asset('assets/stmiklogo.png') }}" alt="STMIK Indonesia Logo"
                        class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="font-bold text-xl text-slate-800 leading-tight">Classroom</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-wide">STMIK Indonesia</p>
                </div>
            </div>

            <div class="hidden md:flex gap-8 text-slate-600 font-medium items-center">
                <a href="#" class="hover:text-rose-600 transition-colors">Beranda</a>
                <a href="#fitur" class="hover:text-rose-600 transition-colors">Fitur</a>
                <a href="#statistik" class="hover:text-rose-600 transition-colors">Statistik</a>
                <a href="/login"
                    class="bg-slate-900 text-white px-6 py-2.5 rounded-full hover:bg-rose-600 transition-all duration-300 shadow-md hover:shadow-rose-500/30 hover:-translate-y-0.5">
                    Login Portal
                </a>
            </div>

            <button class="md:hidden text-slate-600 text-2xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <div id="particles-js"></div>

        <div class="blob bg-rose-300/50 w-96 h-96 rounded-full absolute top-20 left-0 md:left-20"></div>
        <div class="blob bg-pink-200/50 w-[30rem] h-[30rem] rounded-full absolute bottom-10 right-0 md:right-10"
            style="animation-delay: 2s;"></div>
        <div class="blob bg-purple-200/40 w-80 h-80 rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
            style="animation-delay: 4s;"></div>

        <div class="relative z-10 text-center max-w-5xl px-6 hero-text">
            <span
                class="inline-block py-1.5 px-4 rounded-full bg-rose-50 border border-rose-100 text-rose-600 font-semibold text-sm mb-6 shadow-sm">
                <i class="fas fa-rocket mr-2"></i>Campus Information System v2.0
            </span>

            <h1 class="text-5xl md:text-7xl font-extrabold leading-tight text-slate-800 mb-6">
                Membangun Masa Depan <br class="hidden md:block">
                <span class="text-gradient">Teknologi Terpadu</span>
            </h1>

            <p class="max-w-2xl mx-auto text-slate-500 text-lg md:text-xl leading-relaxed">
                Sistem akademik modern, interaktif, dan terintegrasi penuh untuk mengoptimalkan pengalaman dosen,
                mahasiswa, dan administrasi kampus.
            </p>

            <div class="mt-10 flex gap-4 justify-center flex-wrap">
                <a href="/login"
                    class="bg-gradient-to-r from-rose-600 to-rose-700 text-white font-semibold px-8 py-4 rounded-full shadow-xl shadow-rose-600/30 hover:shadow-rose-600/50 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2">
                    Mulai Sekarang <i class="fas fa-arrow-right"></i>
                </a>
                <button
                    class="glass px-8 py-4 rounded-full font-semibold text-slate-700 hover:bg-white transition-all duration-300 flex items-center gap-2 group">
                    <i class="fas fa-play text-rose-500 group-hover:scale-110 transition-transform"></i> Lihat Demo
                </button>
            </div>
        </div>
    </section>

    <section id="statistik" class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                <div class="glass rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-300"
                    data-aos="fade-up" data-aos-delay="0">
                    <div
                        class="w-14 h-14 mx-auto bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="counter text-4xl font-bold text-slate-800" data-target="1500">0</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500 uppercase tracking-wider">Mahasiswa Aktif</p>
                </div>

                <div class="glass rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-300"
                    data-aos="fade-up" data-aos-delay="100">
                    <div
                        class="w-14 h-14 mx-auto bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h2 class="counter text-4xl font-bold text-slate-800" data-target="80">0</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500 uppercase tracking-wider">Dosen Pengajar</p>
                </div>

                <div class="glass rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-300"
                    data-aos="fade-up" data-aos-delay="200">
                    <div
                        class="w-14 h-14 mx-auto bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-laptop-house"></i>
                    </div>
                    <h2 class="counter text-4xl font-bold text-slate-800" data-target="35">0</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500 uppercase tracking-wider">Kelas Berjalan</p>
                </div>

                <div class="glass rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-300"
                    data-aos="fade-up" data-aos-delay="300">
                    <div
                        class="w-14 h-14 mx-auto bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h2 class="counter text-4xl font-bold text-slate-800" data-target="5000">0</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500 uppercase tracking-wider">Total Alumni</p>
                </div>

            </div>
        </div>
    </section>

    <section id="fitur" class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-rose-600 font-bold tracking-widest uppercase text-sm mb-3">Keunggulan</h2>
                <h3 class="text-4xl md:text-5xl font-extrabold text-slate-800">Fitur Sistem Utama</h3>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="glass p-8 rounded-3xl group hover:-translate-y-2 hover:shadow-xl transition-all duration-300"
                    data-aos="zoom-in" data-aos-delay="0">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-rose-500 to-rose-700 rounded-xl flex items-center justify-center text-white mb-6 transform group-hover:rotate-12 transition-transform">
                        <i class="fas fa-book-open text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-800">Akademik Terpadu</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Manajemen KRS, pemetaan mata kuliah berdasarkan jurusan, manajemen semester, hingga kurikulum
                        yang dinamis.
                    </p>
                </div>

                <div class="glass p-8 rounded-3xl group hover:-translate-y-2 hover:shadow-xl transition-all duration-300"
                    data-aos="zoom-in" data-aos-delay="100">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-rose-500 to-rose-700 rounded-xl flex items-center justify-center text-white mb-6 transform group-hover:rotate-12 transition-transform">
                        <i class="fas fa-tasks text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-800">Penilaian Online</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Fitur pengerjaan tugas, UTS, dan UAS secara daring. Mendukung format pilihan ganda maupun essai
                        dengan skor otomatis.
                    </p>
                </div>

                <div class="glass p-8 rounded-3xl group hover:-translate-y-2 hover:shadow-xl transition-all duration-300"
                    data-aos="zoom-in" data-aos-delay="200">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-rose-500 to-rose-700 rounded-xl flex items-center justify-center text-white mb-6 transform group-hover:rotate-12 transition-transform">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-800">Monitoring & Log</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Sistem mencatat setiap perubahan data krusial melalui *Activity Logs* (IP Address & User Agent)
                        untuk keamanan maksimal.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-200 pt-16 pb-8 relative z-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-rose-600 rounded flex items-center justify-center text-white font-bold">
                            <i class="fas fa-graduation-cap text-sm"></i>
                        </div>
                        <h1 class="font-bold text-xl text-slate-800">E-Classroom STMIK</h1>
                    </div>
                    <p class="text-slate-500 mb-6 max-w-sm">
                        Platform pembelajaran elektronik resmi milik STMIK Indonesia Banjarmasin untuk mendukung
                        digitalisasi pendidikan tinggi.
                    </p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-rose-600 hover:text-white transition-colors"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-rose-600 hover:text-white transition-colors"><i
                                class="fab fa-instagram"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-rose-600 hover:text-white transition-colors"><i
                                class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 mb-6 uppercase text-sm tracking-wider">Tautan Cepat</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-slate-500 hover:text-rose-600 transition-colors">Beranda</a>
                        </li>
                        <li><a href="#fitur" class="text-slate-500 hover:text-rose-600 transition-colors">Fitur
                                Sistem</a></li>
                        <li><a href="#" class="text-slate-500 hover:text-rose-600 transition-colors">Panduan
                                Mahasiswa</a></li>
                        <li><a href="#" class="text-slate-500 hover:text-rose-600 transition-colors">Bantuan
                                Akademik</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 mb-6 uppercase text-sm tracking-wider">Kontak</h4>
                    <ul class="space-y-4 text-slate-500">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-rose-500"></i>
                            <span>Jl. Pangeran Hidayatullah, Banjarmasin, Kalimantan Selatan</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-rose-500"></i>
                            <span>info@stmik-indonesia.ac.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="border-t border-slate-200 pt-8 flex flex-col md:flex-row justify-between items-center text-slate-400 text-sm">
                <p>&copy; 2026 STMIK Indonesia Banjarmasin. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Sistem Informasi Akademik v2.0</p>
            </div>
        </div>
    </footer>

    <script>
        // Init AOS
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
        });

        // Particles JS Config
        particlesJS("particles-js", {
            particles: {
                number: {
                    value: 40,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: "#e11d48"
                },
                shape: {
                    type: "circle"
                },
                opacity: {
                    value: 0.3,
                    random: false
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#fecdd3",
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 1.5,
                    direction: "none",
                    random: true,
                    out_mode: "out"
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: {
                        enable: true,
                        mode: "grab"
                    },
                    onclick: {
                        enable: true,
                        mode: "push"
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 140,
                        line_linked: {
                            opacity: 0.8
                        }
                    },
                    push: {
                        particles_nb: 3
                    }
                }
            },
            retina_detect: true
        });

        // Smart Counter Animation (Hanya menghitung saat di-scroll ke view)
        const counters = document.querySelectorAll(".counter");

        const runCounter = (counter) => {
            counter.innerText = '0';
            const target = +counter.getAttribute('data-target');

            const updateCounter = () => {
                const current = +counter.innerText.replace('+', '');
                const increment = target / 40; // Kecepatan hitung

                if (current < target) {
                    counter.innerText = Math.ceil(current + increment);
                    setTimeout(updateCounter, 30);
                } else {
                    counter.innerText = target + "+";
                }
            };
            updateCounter();
        };

        // Menggunakan Intersection Observer agar counter berjalan saat element terlihat
        const observerOptions = {
            threshold: 0.5
        };
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    runCounter(entry.target);
                    observer.unobserve(entry.target); // Hanya hitung sekali
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            observer.observe(counter);
        });

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-md');
            } else {
                navbar.classList.remove('shadow-md');
            }
        });
    </script>
</body>

</html>
