<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    kelas: { type: Object, required: true },
    penilaians: { type: Array, default: () => [] },
});

const selectedPenilaianId = ref(props.penilaians?.[0]?.id ?? null);

const selectedPenilaian = computed(
    () =>
        props.penilaians?.find((p) => p.id === selectedPenilaianId.value) ??
        null,
);

// dummy data statis
const rows = ref([
    {
        id: 1,
        nama: "Andi Pratama",
        nim: "202201001",
        status: "Sudah mengumpulkan",
        nilai: 85,
        updated_at: "07/02/2026 14.20",
        jawaban: "Jawaban esai contoh dari Andi...",
    },
    {
        id: 2,
        nama: "Siti Aulia",
        nim: "202201002",
        status: "Belum mengumpulkan",
        nilai: null,
        updated_at: "—",
        jawaban: "—",
    },
]);

const activeRowId = ref(rows.value[0]?.id ?? null);

const activeRow = computed(
    () => rows.value.find((r) => r.id === activeRowId.value) ?? null,
);

const nilaiInput = ref(activeRow.value?.nilai ?? null);
const catatan = ref("");

const selectRow = (r) => {
    activeRowId.value = r.id;
    nilaiInput.value = r.nilai;
    catatan.value = "";
};

const saveDummy = () => {
    // statis dulu: update lokal saja
    if (!activeRow.value) return;
    const idx = rows.value.findIndex((x) => x.id === activeRow.value.id);
    if (idx === -1) return;
    rows.value[idx].nilai = nilaiInput.value;
    rows.value[idx].status = "Dinilai (dummy)";
    rows.value[idx].updated_at = "08/02/2026 10.00";
    alert("Tersimpan (dummy). Nanti tinggal sambungkan ke backend.");
};
</script>

<template>
    <div class="space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4">
            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900">
                        Jawaban Mahasiswa
                    </div>
                    <div class="text-sm text-gray-600">
                        Pilih penilaian lalu beri nilai (statis dulu).
                    </div>
                </div>

                <div class="w-full sm:w-80">
                    <label
                        class="block text-xs font-semibold text-gray-600 mb-1"
                    >
                        Penilaian
                    </label>
                    <select
                        v-model="selectedPenilaianId"
                        class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option
                            v-for="p in penilaians"
                            :key="p.id"
                            :value="p.id"
                        >
                            {{ p.judul }}
                        </option>
                        <option v-if="!penilaians.length" :value="null">
                            (Belum ada penilaian)
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="selectedPenilaian" class="mt-3 text-xs text-gray-500">
                Menampilkan jawaban untuk:
                <b class="text-gray-700">{{ selectedPenilaian.judul }}</b>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- List mahasiswa -->
            <div
                class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white overflow-hidden"
            >
                <div class="px-4 py-3 border-b bg-gray-50">
                    <div
                        class="text-xs font-bold tracking-wide text-gray-700 uppercase"
                    >
                        Daftar Mahasiswa
                    </div>
                </div>

                <div class="divide-y">
                    <button
                        v-for="r in rows"
                        :key="r.id"
                        type="button"
                        class="w-full text-left px-4 py-3 hover:bg-gray-50 transition"
                        :class="
                            activeRowId === r.id ? 'bg-blue-50' : 'bg-white'
                        "
                        @click="selectRow(r)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div
                                    class="text-sm font-semibold text-gray-900 truncate"
                                >
                                    {{ r.nama }}
                                </div>
                                <div class="text-xs text-gray-600">
                                    NIM {{ r.nim }} •
                                    <span class="font-semibold">{{
                                        r.status
                                    }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Update: {{ r.updated_at }}
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-xs text-gray-500">Nilai</div>
                                <div class="text-sm font-bold text-gray-900">
                                    {{ r.nilai != null ? r.nilai : "—" }}
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Detail + form nilai -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                <div class="text-sm font-semibold text-gray-900">
                    Detail Jawaban
                </div>

                <div v-if="!activeRow" class="mt-2 text-sm text-gray-500">
                    Pilih mahasiswa terlebih dahulu.
                </div>

                <div v-else class="mt-3 space-y-4">
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 p-3"
                    >
                        <div class="text-xs font-semibold text-gray-700">
                            {{ activeRow.nama }} ({{ activeRow.nim }})
                        </div>
                        <div
                            class="mt-2 text-sm text-gray-800 whitespace-pre-line"
                        >
                            {{ activeRow.jawaban }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 mb-1"
                        >
                            Nilai (0-100)
                        </label>
                        <input
                            v-model.number="nilaiInput"
                            type="number"
                            min="0"
                            max="100"
                            class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="mis. 80"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 mb-1"
                        >
                            Catatan (opsional)
                        </label>
                        <textarea
                            v-model="catatan"
                            rows="3"
                            class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Catatan untuk mahasiswa..."
                        />
                    </div>

                    <button
                        type="button"
                        class="w-full rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                        @click="saveDummy"
                    >
                        Simpan (Dummy)
                    </button>

                    <div class="text-[11px] text-gray-500">
                        Ini masih statis. Nanti tinggal ganti `rows` dengan data
                        backend + submit pakai Inertia.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
