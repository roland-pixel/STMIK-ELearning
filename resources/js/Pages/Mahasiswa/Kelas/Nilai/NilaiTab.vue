<script setup>
defineProps({
    kelas: { type: Object, required: true },
});
</script>

<template>
    <section
        class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden"
    >
        <div class="px-6 py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Nilai</div>
            <div class="text-xs text-gray-500 mt-0.5">
                Bobot penilaian & ringkasan (placeholder)
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div
                    class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4"
                >
                    <div class="text-xs text-gray-500">Tugas</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_tugas ?? 0 }}%
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4"
                >
                    <div class="text-xs text-gray-500">UTS</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uts ?? 0 }}%
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4"
                >
                    <div class="text-xs text-gray-500">UAS</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uas ?? 0 }}%
                    </div>
                </div>
            </div>

            <div
                class="mt-5 rounded-2xl border border-gray-200/70 bg-white p-4"
            >
                <div class="text-sm font-semibold text-gray-900">Ringkasan</div>
                <div class="mt-1 text-sm text-gray-600">
                    Kamu bisa isi halaman ini nanti dari tabel
                    <span class="font-semibold">rekap_nilais</span>
                    (nilai akhir per mahasiswa).
                </div>
            </div>
        </div>
    </section>
</template>
