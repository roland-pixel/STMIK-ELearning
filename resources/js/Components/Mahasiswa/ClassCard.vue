<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    kelas: { type: Object, required: true },
});

const hasCover = computed(() => !!props.kelas.cover);

const patternSvg = (variant = "grid") => {
    const svgs = {
        grid: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <defs>
            <pattern id='p' width='18' height='18' patternUnits='userSpaceOnUse'>
              <path d='M18 0H0V18' fill='none' stroke='rgba(255,255,255,.18)' stroke-width='1'/>
            </pattern>
            <radialGradient id='g' cx='70%' cy='15%' r='85%'>
              <stop offset='0' stop-color='rgba(255,255,255,.18)'/>
              <stop offset='1' stop-color='rgba(255,255,255,0)'/>
            </radialGradient>
          </defs>
          <rect width='420' height='180' fill='url(#p)'/>
          <circle cx='330' cy='40' r='130' fill='url(#g)'/>
          <path d='M330 -10c55 35 90 85 100 150c-55 30-120 40-200 28c-10-55 30-120 100-178z' fill='rgba(0,0,0,.10)'/>
        </svg>`,

        dots: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <defs>
            <pattern id='d' width='16' height='16' patternUnits='userSpaceOnUse'>
              <circle cx='2.5' cy='2.5' r='1.5' fill='rgba(255,255,255,.22)'/>
            </pattern>
          </defs>
          <rect width='420' height='180' fill='url(#d)'/>
          <path d='M420 0v100c-70 35-160 45-240 26c18-44 82-98 150-118h90z' fill='rgba(0,0,0,.12)'/>
        </svg>`,

        waves: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <path d='M0 110c60-34 120-34 180 0s120 34 240 0v70H0z' fill='rgba(0,0,0,.12)'/>
          <path d='M0 86c60-28 120-28 180 0s120 28 240 0' fill='none' stroke='rgba(255,255,255,.20)' stroke-width='2'/>
          <path d='M0 62c60-22 120-22 180 0s120 22 240 0' fill='none' stroke='rgba(255,255,255,.14)' stroke-width='2'/>
        </svg>`,
    };

    const svg = svgs[variant] ?? svgs.grid;
    return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
};

const headerPattern = computed(() => {
    if (hasCover.value) return props.kelas.cover;
    return patternSvg(props.kelas.pattern ?? "grid");
});

const patternStyle = computed(() => ({
    backgroundImage: `url("${headerPattern.value}")`,
    backgroundSize: "cover",
    backgroundPosition: "center",
    backgroundRepeat: "no-repeat",
}));

const chips = computed(() => {
    const mk = props.kelas.mata_kuliah ?? props.kelas.mk ?? null;
    const semester = props.kelas.semester ?? null;
    const mhs =
        props.kelas.jumlah_mahasiswa ?? props.kelas.total_mahasiswa ?? null;

    const items = [];
    if (mk) items.push({ label: mk, tone: "blue" });
    if (semester) items.push({ label: semester, tone: "slate" });
    if (mhs !== null && mhs !== undefined)
        items.push({ label: `${mhs} mahasiswa`, tone: "slate" });

    if (items.length === 0) {
        items.push({ label: "Kelas aktif", tone: "blue" });
        items.push({ label: "Siap digunakan", tone: "slate" });
    }

    return items.slice(0, 3);
});

const chipClass = (tone) => {
    if (tone === "blue")
        return "bg-white/85 text-blue-700 ring-1 ring-white/40";
    return "bg-white/80 text-gray-700 ring-1 ring-white/35";
};

const progress = computed(() => {
    const p = Number(props.kelas.progress);
    if (Number.isFinite(p)) return Math.max(0, Math.min(100, p));
    return null;
});

// statis dulu (buat ngecek tampil)
const detailHref = computed(() => {
    const uuid = props.kelas.uuid ?? props.kelas.id;
    return route("mahasiswa.kelas.show", uuid);
});

const descPayload = computed(() => {
    const d = (props.kelas.deskripsi ?? "").trim();
    if (d.length) {
        return { kind: "desc", text: d, author: null };
    }

    const q = page.props.weekly_quote;
    if (q?.text) {
        return {
            kind: "quote",
            text: q.text,
            author: q.author ?? null,
        };
    }

    return { kind: "empty", text: "-", author: null };
});

const dosenAvatar = computed(() => {
    return props.kelas.dosen_avatar ?? props.kelas.avatar ?? null;
});

const dosenAvatarUrl = computed(() => {
    if (!dosenAvatar.value) return null;
    if (/^https?:\/\//.test(dosenAvatar.value)) return dosenAvatar.value;
    return `/storage/${dosenAvatar.value}`;
});
</script>

<template>
    <Link
        :href="detailHref"
        class="block group focus:outline-none focus:ring-2 focus:ring-blue-700/20 rounded-2xl"
        preserve-scroll
    >
        <div
            class="relative overflow-hidden rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm transition group-hover:-translate-y-0.5 group-hover:shadow-xl flex flex-col h-[245px]"
        >
            <!-- HEADER -->
            <div class="relative p-4 text-white h-[120px]" :class="kelas.theme">
                <div
                    class="absolute inset-0 opacity-90"
                    :style="patternStyle"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/10 to-black/30"
                />

                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3
                                class="text-[17px] font-semibold leading-tight truncate drop-shadow-sm"
                            >
                                {{ kelas.nama }}
                            </h3>
                            <span
                                class="inline-flex h-2 w-2 rounded-full bg-white/70 ring-2 ring-white/15"
                                aria-hidden="true"
                            />
                        </div>

                        <p
                            class="mt-0.5 text-sm text-white/90 truncate drop-shadow-sm"
                        >
                            {{ kelas.dosen }}
                        </p>
                    </div>

                    <div class="shrink-0">
                        <div
                            class="h-10 w-10 rounded-2xl overflow-hidden bg-white/10 ring-1 ring-white/15 grid place-items-center backdrop-blur-sm"
                            aria-hidden="true"
                        >
                            <img
                                v-if="dosenAvatarUrl"
                                :src="dosenAvatarUrl"
                                alt="Foto dosen"
                                class="h-full w-full object-cover"
                            />
                            <span
                                v-else
                                class="text-xs font-semibold text-white/90"
                            >
                                {{
                                    (kelas.dosen ?? "D")
                                        .trim()
                                        .slice(0, 1)
                                        .toUpperCase()
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="(c, i) in chips"
                        :key="i"
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold"
                        :class="chipClass(c.tone)"
                    >
                        {{ c.label }}
                    </span>
                </div>
            </div>

            <!-- BODY -->
            <div class="border-t border-gray-200/60 px-4 py-4 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-xs text-gray-500">Deskripsi</div>

                        <div class="mt-0.5 text-sm font-semibold text-gray-800">
                            <p
                                class="h-[44px] leading-snug overflow-hidden line-clamp-2"
                                :title="descPayload.text"
                            >
                                <template v-if="descPayload.kind === 'quote'">
                                    “{{ descPayload.text }}”
                                </template>
                                <template v-else>
                                    {{ descPayload.text }}
                                </template>
                            </p>

                            <p
                                v-if="
                                    descPayload.kind === 'quote' &&
                                    descPayload.author
                                "
                                class="mt-1 text-[11px] font-semibold text-gray-500 truncate"
                                :title="descPayload.author"
                            >
                                — {{ descPayload.author }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100"
                        >
                            Aktif
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div v-if="progress !== null">
                        <div
                            class="flex items-center justify-between text-[11px] text-gray-500"
                        >
                            <span>Progress</span>
                            <span class="font-semibold text-gray-700"
                                >{{ progress }}%</span
                            >
                        </div>

                        <div
                            class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden"
                        >
                            <div
                                class="h-full rounded-full bg-blue-600/80 transition-all duration-300 group-hover:bg-blue-600"
                                :style="{ width: `${progress}%` }"
                            />
                        </div>
                    </div>

                    <div v-else class="h-[28px]" aria-hidden="true"></div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="border-t border-gray-200/60 bg-gray-50/50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">Lihat detail kelas</div>

                    <svg
                        class="h-4 w-4 text-gray-400 transition-transform duration-200 group-hover:translate-x-0.5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24c.29.29.29.77 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
            </div>
        </div>
    </Link>
</template>
