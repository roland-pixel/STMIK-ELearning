<script setup>
const props = defineProps({
    kelas: { type: Object, required: true },
    anggota: { type: Array, default: () => [] },
});
</script>

<template>
    <section
        class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden"
    >
        <div class="px-6 py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Orang</div>
            <div class="text-xs text-gray-500 mt-0.5">
                Dosen & mahasiswa dalam kelas
            </div>
        </div>

        <div class="p-6">
            <!-- Dosen -->
            <div class="mb-6">
                <div class="text-xs text-gray-500">Pengajar</div>

                <div
                    class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-3"
                >
                    <div
                        class="w-10 h-10 rounded-full bg-white ring-1 ring-gray-200/70 grid place-items-center font-bold text-gray-700"
                    >
                        {{
                            (kelas.dosen?.nama_lengkap ?? "D")
                                .slice(0, 1)
                                .toUpperCase()
                        }}
                    </div>

                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">
                            {{ kelas.dosen?.nama_lengkap ?? "—" }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ kelas.dosen?.email ?? "—" }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mahasiswa -->
            <div>
                <div class="text-xs text-gray-500">
                    Mahasiswa ({{
                        kelas.counts?.anggota ?? anggota.length ?? 0
                    }})
                </div>

                <div
                    v-if="anggota.length === 0"
                    class="mt-3 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-3 text-sm text-gray-600"
                >
                    Belum ada anggota.
                </div>

                <div v-else class="mt-3 space-y-2">
                    <div
                        v-for="a in anggota"
                        :key="a.id"
                        class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200/70 bg-white px-4 py-3"
                    >
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 truncate">
                                {{ a.mahasiswa?.nama_lengkap ?? "—" }}
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ a.mahasiswa?.nim ?? "—" }} •
                                {{ a.mahasiswa?.email ?? "—" }}
                            </div>
                        </div>

                        <div class="text-xs text-gray-400 shrink-0">
                            {{ a.tanggal_gabung ?? "—" }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
