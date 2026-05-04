<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kelas: Object,
    penilaian: Object,
    mahasiswas: Array,
    usedCategories: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const form = useForm({
    judul: props.penilaian?.judul || '',
    kategori: props.penilaian?.kategori || 'tugas',
    instruksi: props.penilaian?.instruksi || '',
    nilai_mahasiswa: [],
});

// ✅ CEK APAKAH KATEGORI DISABLED
const isKategoriDisabled = (kategori) => {
    // Jika kategori tersebut ada di usedCategories DAN bukan kategori asli penilaian ini, maka disable
    return props.usedCategories.includes(kategori) && kategori !== props.penilaian?.kategori;
};

// ✅ REACTIVE NILAI MAHASISWA
const nilaiMahasiswa = ref([]);

// ✅ INITIALIZE NILAI DARI PROPS
const initNilaiMahasiswa = () => {
    if (!props.mahasiswas?.length) return;

    nilaiMahasiswa.value = props.mahasiswas.map(m => ({
        id: m.id,
        nim: m.nim,
        nama: m.nama,
        nilai: Number(m.nilai) || 0,
    }));
    console.log('✅ Initialized mahasiswas:', nilaiMahasiswa.value);
};

// ✅ WATCH PROPS CHANGES
watch(() => props.mahasiswas, (newVal) => {
    if (newVal?.length) {
        initNilaiMahasiswa();
    }
}, { immediate: true });

// ✅ FILTER SEARCH
const filteredMahasiswa = computed(() => {
    if (!searchQuery.value) return nilaiMahasiswa.value;
    const query = searchQuery.value.toLowerCase();
    return nilaiMahasiswa.value.filter(m =>
        m.nama.toLowerCase().includes(query) ||
        m.nim.toLowerCase().includes(query)
    );
});

// ✅ VALIDASI REAL-TIME
const validateNilai = (nilai) => {
    const num = Number(nilai) || 0;
    return Math.max(0, Math.min(100, num));
};

// 🔥 FIXED SUBMIT
const submit = () => {
    // VALIDASI TERAKHIR
    nilaiMahasiswa.value = nilaiMahasiswa.value.map(m => ({
        ...m,
        nilai: validateNilai(m.nilai)
    }));

    // ✅ CLEAN DATA
    const cleanInstruksi = form.instruksi === '' ? null : form.instruksi?.trim();
    form.judul = form.judul?.trim() || '';
    form.instruksi = cleanInstruksi;
    form.nilai_mahasiswa = [...nilaiMahasiswa.value];

    // 🔥 KONFRIMASI HANYA UNTUK KATEGORI YANG BISA DIUBAH
    const kategoriAwal = props.penilaian?.kategori;
    const isChangingToUsed = form.kategori !== kategoriAwal &&
        props.usedCategories.includes(form.kategori);

    if (!isChangingToUsed && form.kategori !== kategoriAwal && ['uts', 'uas'].includes(form.kategori)) {
        if (!confirm(
            `⚠️ Yakin ubah kategori menjadi **${form.kategori.toUpperCase()}**?\n\n` +
            `Mengubah dari: **${kategoriAwal?.toUpperCase()}** → **${form.kategori.toUpperCase()}**`
        )) {
            return;
        }
    }

    console.log('🚀 Submitting:', {
        judul: form.judul,
        kategori: form.kategori,
        kategori_awal: kategoriAwal,
        usedCategories: props.usedCategories,
        mahasiswa_count: form.nilai_mahasiswa.length
    });

    form.put(route('dosen.kelas.penilaian.manual.update', {
        kelas: props.kelas.uuid,
        penilaian: props.penilaian.uuid
    }), {
        onSuccess: () => {
            console.log('✅ SUCCESS - Nilai & kategori tersimpan!');
            searchQuery.value = '';
        },
        onError: (errors) => {
            console.error('❌ Validation errors:', errors);
        }
    });
};
</script>

<template>

    <Head :title="`Edit Nilai Manual: ${penilaian?.judul || 'Loading...'}`" />

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- HEADER -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Nilai Manual</h1>
                <p class="text-gray-600 mt-1">
                    Kelas: {{ kelas.nama_kelas }} –
                    <span class="font-medium text-blue-600">{{ penilaian?.kategori?.toUpperCase() || 'LOADING' }}</span>
                </p>
            </div>
            <Link :href="route('dosen.kelas.show', kelas.uuid)"
                class="text-sm text-blue-600 hover:underline font-medium">
                ← Kembali ke Kelas
            </Link>
        </div>

        <!-- LOADING STATE -->
        <div v-if="!props.mahasiswas?.length" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-500">Loading data mahasiswa...</p>
        </div>

        <!-- FORM -->
        <form v-else @submit.prevent="submit" class="space-y-6">
            <!-- INFO PENILAIAN -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- JUDUL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Penilaian</label>
                        <input v-model="form.judul" type="text" placeholder="Contoh: Tugas Pertemuan 1"
                            :disabled="form.processing"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all" />
                        <div v-if="form.errors.judul" class="text-red-500 text-xs mt-2 p-2 bg-red-50 rounded">
                            {{ form.errors.judul }}
                        </div>
                    </div>

                    <!-- 🔥 KATEGORI - DISABLE OTOMATIS -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori
                            <span v-if="usedCategories.length" class="text-xs text-red-600 font-medium">
                                (ada {{ usedCategories.length }} kategori terpakai)
                            </span>
                            <span v-else class="text-xs text-amber-600">(Bisa diubah)</span>
                        </label>
                        <select v-model="form.kategori" :disabled="form.processing" class="w-full px-4 py-3 border-2 rounded-lg shadow-sm focus:ring-3 focus:ring-blue-500 transition-all
                                   bg-amber-50 border-amber-300 hover:border-amber-400">
                            <option value="tugas">📝 Tugas</option>
                            <option value="uts" :disabled="isKategoriDisabled('uts')">
                                📚 UTS {{ usedCategories.includes('uts') ? '(Sudah ada)' : '' }}
                            </option>
                            <option value="uas" :disabled="isKategoriDisabled('uas')">
                                🎓 UAS {{ usedCategories.includes('uas') ? '(Sudah ada)' : '' }}
                            </option>
                        </select>

                        <!-- ⚠️ INFO KATEGORI TERPAKAI -->
                        <div v-if="usedCategories.length"
                            class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg text-xs">
                            <div class="font-medium text-red-800 mb-1">⚠️ Kategori sudah digunakan:</div>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="k in usedCategories" :key="k"
                                    class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                    {{ k.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <!-- ⚠️ WARNING PERUBAHAN KATEGORI -->
                        <div v-if="form.kategori !== props.penilaian?.kategori"
                            class="mt-2 p-3 bg-amber-100 border-l-4 border-amber-400 rounded-lg text-sm shadow-sm">
                            <div class="font-semibold text-amber-800 mb-1 flex items-center">
                                ⚠️ Kategori akan diubah
                            </div>
                            <div class="text-amber-700 text-xs">
                                Dari <span class="font-bold bg-amber-200 px-1 rounded">{{
                                    props.penilaian?.kategori?.toUpperCase() }}</span>
                                menjadi <span class="font-bold bg-blue-200 px-1 rounded">{{ form.kategori?.toUpperCase()
                                    }}</span>
                            </div>
                        </div>

                        <!-- ERROR KATEGORI -->
                        <div v-if="form.errors.kategori" class="text-red-500 text-xs mt-2 p-2 bg-red-50 rounded">
                            {{ form.errors.kategori }}
                        </div>
                    </div>

                    <!-- INSTRUKSI -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi (Opsional)</label>
                        <textarea v-model="form.instruksi" rows="3" :disabled="form.processing"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all"
                            placeholder="Instruksi untuk mahasiswa..."></textarea>
                    </div>
                </div>
            </div>

            <!-- TABEL MAHASISWA -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Daftar Mahasiswa ({{ filteredMahasiswa.length }} / {{ nilaiMahasiswa.length }})
                            </h3>
                            <div v-if="form.processing"
                                class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                        </div>
                        <div class="w-full sm:w-80">
                            <input v-model="searchQuery" type="text" placeholder="🔍 Cari nama atau NIM..."
                                :disabled="form.processing"
                                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 transition-all" />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Mahasiswa</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    NIM</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Nilai (0-100)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="mhs in filteredMahasiswa" :key="mhs.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ mhs.nama }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ mhs.nim }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <input v-model.number="mhs.nilai" type="number" min="0" max="100" step="0.01"
                                            :disabled="form.processing"
                                            @input="mhs.nilai = validateNilai($event.target.value)" class="w-24 h-12 text-center text-lg font-bold border-2 rounded-lg shadow-sm focus:ring-3 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white
                                                   :class='{
                                                       'border-green-300 bg-green-50 text-green-800': mhs.nilai > 0,
                                                       'border-gray-300 text-gray-700': mhs.nilai === 0,
                                                       'border-red-300 bg-red-50 text-red-700': mhs.nilai > 100
                                                   }'" />
                                        <span class="ml-2 text-sm text-gray-500">/ 100</span>
                                    </div>
                                    <div v-if="form.errors[`nilai_mahasiswa.${mhs.id}`]"
                                        class="text-red-500 text-xs mt-1 px-2">
                                        {{ form.errors[`nilai_mahasiswa.${mhs.id}`] }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredMahasiswa.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-lg">📝</div>
                                    <p>Mahasiswa tidak ditemukan</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col sm:flex-row gap-4 justify-end items-center">
                <div class="text-sm text-gray-600">
                    Total: {{ filteredMahasiswa.length }} mahasiswa
                </div>
                <div class="flex gap-3">
                    <Link :href="route('dosen.kelas.show', kelas.uuid)"
                        class="px-6 py-3 bg-white border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm">
                        ← Kembali
                    </Link>
                    <button type="submit" :disabled="form.processing || !nilaiMahasiswa.length"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 border-2 border-transparent rounded-xl text-sm font-bold text-white shadow-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                        <span v-if="form.processing">
                            <svg class="w-4 h-4 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Menyimpan...
                        </span>
                        <span v-else>💾 Simpan {{ nilaiMahasiswa.length }} Nilai</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>