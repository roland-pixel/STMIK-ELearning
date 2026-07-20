<script setup>
import { Head, useForm } from '@inertiajs/vue3';
// PERBAIKAN: Mengembalikan import ke 'vue', bukan 'react'
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    kelas: Object,
    penilaian: Object,
    pengumpulan: Object
});

const storageKey = `stmik_elearning_penilaian_${props.penilaian.id}_pengumpulan_${props.pengumpulan.id}`;

// State local untuk mengontrol status loading saat bypass chunk upload
const customProcessing = ref(false);

const baseForm = useForm({
    jawaban: props.penilaian.pertanyaans.map(p => ({
        pertanyaan_id: p.id,
        opsi_jawaban_id: null,
        text_jawaban: '',
        file: null,
        file_path: null // Menyimpan string path sukses dari MinIO
    }))
});

// Proxy wrapper agar template mendeteksi loading upload tanpa mengubah kode HTML
const form = new Proxy(baseForm, {
    get(target, prop) {
        if (prop === 'processing') {
            return target.processing || customProcessing.value;
        }
        const value = Reflect.get(target, prop);
        return typeof value === 'function' ? value.bind(target) : value;
    },
    set(target, prop, value) {
        return Reflect.set(target, prop, value);
    }
});

const activeIndex = ref(0);
const showConfirm = ref(false);

const isSoalAnswered = (index) => {
    const j = form.jawaban[index];
    return j.opsi_jawaban_id !== null || j.text_jawaban.trim() !== '' || j.file !== null;
};

const answeredCount = computed(() =>
    form.jawaban.filter((_, i) => isSoalAnswered(i)).length
);
const totalSoal = props.penilaian.pertanyaans.length;
const progressPercent = computed(() => Math.round((answeredCount.value / totalSoal) * 100));

onMounted(() => {
    const savedJawaban = localStorage.getItem(storageKey);
    if (savedJawaban) {
        try {
            const parsed = JSON.parse(savedJawaban);
            form.jawaban.forEach(item => {
                const match = parsed.find(s => s.pertanyaan_id === item.pertanyaan_id);
                if (match) {
                    item.opsi_jawaban_id = match.opsi_jawaban_id;
                    item.text_jawaban = match.text_jawaban;
                }
            });
        } catch (e) {
            console.error('Gagal memulihkan jawaban:', e);
        }
    }
});

watch(
    () => form.jawaban,
    (newJawaban) => {
        const dataToStore = newJawaban.map(item => ({
            pertanyaan_id: item.pertanyaan_id,
            opsi_jawaban_id: item.opsi_jawaban_id,
            text_jawaban: item.text_jawaban
        }));
        localStorage.setItem(storageKey, JSON.stringify(dataToStore));
    },
    { deep: true }
);

const goToSoal = (index) => { activeIndex.value = index; };
const goPrev = () => { if (activeIndex.value > 0) activeIndex.value--; };
const goNext = () => { if (activeIndex.value < totalSoal - 1) activeIndex.value++; };

// Fungsi pemotong file & pengunggah potongan langsung ke MinIO via Presigned URL
const uploadFileInChunks = async (file) => {
    const CHUNK_SIZE = 10 * 1024 * 1024; // Potong per 10MB
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

    try {
        // 1. Inisialisasi Multipart Upload ke Laravel Mahasiswa Endpoint
        const initiateRes = await axios.post(route('mahasiswa.kelas.penilaian.online.initiate', [props.kelas.uuid, props.penilaian.uuid]), {
            filename: file.name,
            total_chunks: totalChunks
        });

        const { upload_id, key, urls } = initiateRes.data;
        const parts = [];

        // 2. Upload tiap chunk langsung ke MinIO (Bypass Laravel)
        for (let i = 1; i <= totalChunks; i++) {
            const start = (i - 1) * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunkBlob = file.slice(start, end);

            const uploadPartRes = await axios.put(urls[i], chunkBlob, {
                headers: { "Content-Type": "application/octet-stream" }
            });

            const etag = uploadPartRes.headers['etag'];
            parts.push({
                PartNumber: i,
                ETag: etag
            });
        }

        // 3. Gabungkan bagian-bagian chunk di MinIO via Laravel
        const completeRes = await axios.post(route('mahasiswa.kelas.penilaian.online.complete', [props.kelas.uuid, props.penilaian.uuid]), {
            upload_id: upload_id,
            key: key,
            parts: parts
        });

        if (completeRes.data && completeRes.data.status === true) {
            return completeRes.data.file_path;
        }
    } catch (error) {
        console.error("Gagal memproses fragmen file ke server storage MinIO:", error);
        throw error;
    }
    return null;
};

// Proses submit utama diubah menjadi Async untuk mengunggah file terlebih dahulu
const submitJawaban = async () => {
    showConfirm.value = false;
    customProcessing.value = true; // Nyalakan loading spinner di UI template

    try {
        // Loop seluruh baris jawaban untuk mencari file yang belum diupload
        for (let i = 0; i < form.jawaban.length; i++) {
            const item = form.jawaban[i];
            if (item.file && !item.file_path) {
                const uploadedPath = await uploadFileInChunks(item.file);
                if (uploadedPath) {
                    item.file_path = uploadedPath;
                } else {
                    alert(`Gagal mengunggah file pada soal nomor ${i + 1}. Silakan coba kembali.`);
                    customProcessing.value = false;
                    return;
                }
            }
        }

        // Kirim muatan data akhir ke backend laravel (Menyesuaikan dengan validasi string file_path)
        form.transform((data) => ({
            jawaban: data.jawaban.map(j => ({
                pertanyaan_id: j.pertanyaan_id,
                opsi_jawaban_id: j.opsi_jawaban_id,
                text_jawaban: j.text_jawaban,
                file_path: j.file_path || null // Mengirim path string, bukan objek File mentah
            }))
        })).post(route('mahasiswa.kelas.penilaian.online.submit', [props.kelas.uuid, props.penilaian.uuid]), {
            onSuccess: () => { 
                localStorage.removeItem(storageKey); 
            },
            onFinish: () => {
                customProcessing.value = false; // Matikan loading spinner
            }
        });

    } catch (error) {
        alert('Terjadi kendala saat memproses upload file jawaban lampiran.');
        customProcessing.value = false;
    }
};

const handleFileChange = (index, event) => {
    form.jawaban[index].file = event.target.files[0];
    form.jawaban[index].file_path = null; // Reset path jika mahasiswa mengganti file baru
};

const jenisBadge = (jenis) => ({
    'pilihan_ganda': { label: 'Pilihan Ganda', cls: 'badge-blue' },
    'essai': { label: 'Esai', cls: 'badge-amber' },
    'upload_file': { label: 'Upload File', cls: 'badge-purple' },
})[jenis] || { label: jenis, cls: 'badge-gray' };
</script>

<template>

    <Head title="Mengerjakan Soal" />

    <div class="exam-root">

        <header class="exam-header">
            <div class="header-inner">
                <div class="header-left">
                    <div class="header-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="header-title">{{ penilaian.judul }}</div>
                        <div class="header-sub">{{ $page.props.auth.user.nama_lengkap }}</div>
                    </div>
                </div>

                <div class="header-right">
                    <div class="progress-wrap">
                        <div class="progress-label">
                            <span>Progress</span>
                            <span class="progress-count">{{ answeredCount }}/{{ totalSoal }}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                    </div>
                    <button class="btn-submit" @click="showConfirm = true" :disabled="form.processing">
                        <svg v-if="!form.processing" width="15" height="15" fill="none" stroke="currentColor"
                            stroke-width="2.5" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <svg v-else class="spin" width="15" height="15" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="op25" />
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        {{ form.processing ? 'Mengirim…' : 'Kumpulkan' }}
                    </button>
                </div>
            </div>
        </header>

        <div class="exam-layout">

            <aside class="exam-sidebar">
                <div class="sidebar-section">
                    <div class="sidebar-heading">Navigasi Soal</div>
                    <div class="soal-grid">
                        <button v-for="(_, i) in penilaian.pertanyaans" :key="i" class="soal-btn" :class="{
                            'soal-active': activeIndex === i,
                            'soal-done': isSoalAnswered(i) && activeIndex !== i,
                        }" @click="goToSoal(i)">
                            {{ i + 1 }}
                            <span v-if="isSoalAnswered(i)" class="soal-check">
                                <svg width="8" height="8" fill="none" stroke="currentColor" stroke-width="3"
                                    viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="sidebar-legend">
                    <div class="legend-row"><span class="legend-dot dot-done"></span> Sudah dijawab</div>
                    <div class="legend-row"><span class="legend-dot dot-active"></span> Sedang dikerjakan</div>
                    <div class="legend-row"><span class="legend-dot dot-empty"></span> Belum dijawab</div>
                </div>

                <div class="sidebar-stat">
                    <div class="stat-ring-wrap">
                        <svg class="stat-ring" width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="32" fill="none" stroke="#E2E8F0" stroke-width="7" />
                            <circle cx="40" cy="40" r="32" fill="none" stroke="#2563EB" stroke-width="7"
                                stroke-dasharray="201" :stroke-dashoffset="201 - (201 * progressPercent / 100)"
                                stroke-linecap="round" transform="rotate(-90 40 40)"
                                style="transition: stroke-dashoffset 0.5s ease" />
                        </svg>
                        <div class="stat-ring-text">
                            <div class="stat-ring-num">{{ progressPercent }}%</div>
                        </div>
                    </div>
                    <div class="stat-info">
                        <div class="stat-row"><span class="stat-label">Terjawab</span><span
                                class="stat-val stat-blue">{{ answeredCount }}</span></div>
                        <div class="stat-row"><span class="stat-label">Belum</span><span class="stat-val stat-gray">{{
                            totalSoal - answeredCount }}</span></div>
                        <div class="stat-row"><span class="stat-label">Total</span><span class="stat-val">{{ totalSoal
                        }}</span></div>
                    </div>
                </div>
            </aside>

            <main class="exam-main">

                <div v-for="(soal, index) in penilaian.pertanyaans" :key="soal.id" v-show="activeIndex === index"
                    class="soal-card">
                    <div class="soal-header">
                        <div class="soal-num" :class="isSoalAnswered(index) ? 'num-done' : 'num-default'">
                            <template v-if="isSoalAnswered(index)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </template>
                            <template v-else>{{ index + 1 }}</template>
                        </div>
                        <div>
                            <span class="jenis-badge" :class="jenisBadge(soal.jenis_pertanyaan).cls">
                                {{ jenisBadge(soal.jenis_pertanyaan).label }}
                            </span>
                            <div class="soal-counter">Soal {{ index + 1 }} dari {{ totalSoal }}</div>
                        </div>
                    </div>

                    <div class="soal-text">{{ soal.text_pertanyaan }}</div>

                    <div v-if="soal.images?.length" class="soal-images">
                        <img v-for="img in soal.images" :src="img.url" :key="img.url" class="soal-img" />
                    </div>

                    <div v-if="soal.jenis_pertanyaan === 'pilihan_ganda'" class="opsi-list">
                        <label v-for="(opsi, oi) in soal.opsi_jawabans" :key="opsi.id" class="opsi-item"
                            :class="form.jawaban[index].opsi_jawaban_id === opsi.id ? 'opsi-selected' : ''">
                            <input type="radio" :name="'soal_' + soal.id" :value="opsi.id"
                                v-model="form.jawaban[index].opsi_jawaban_id" class="sr-only" />
                            <div class="opsi-radio"
                                :class="form.jawaban[index].opsi_jawaban_id === opsi.id ? 'radio-on' : ''">
                                <div v-if="form.jawaban[index].opsi_jawaban_id === opsi.id" class="radio-dot"></div>
                            </div>
                            <span class="opsi-letter">{{ String.fromCharCode(65 + oi) }}</span>
                            <span class="opsi-text">{{ opsi.teks_opsi }}</span>
                        </label>
                    </div>

                    <div v-else-if="soal.jenis_pertanyaan === 'essai'" class="essai-wrap">
                        <textarea v-model="form.jawaban[index].text_jawaban" rows="7"
                            placeholder="Tuliskan jawaban Anda dengan jelas dan lengkap…" class="essai-area"></textarea>
                        <div class="essai-footer">
                            <span class="essai-hint">Gunakan kalimat yang jelas dan sistematis</span>
                            <span class="essai-count">{{ form.jawaban[index].text_jawaban.length }} karakter</span>
                        </div>
                    </div>

                    <div v-else-if="soal.jenis_pertanyaan === 'upload_file'" class="upload-wrap">
                        <label class="upload-zone" :class="form.jawaban[index].file ? 'upload-done' : ''">
                            <input type="file" class="sr-only" @change="handleFileChange(index, $event)" />
                            <div v-if="!form.jawaban[index].file" class="upload-empty">
                                <div class="upload-icon-wrap">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5m0 0L7.5 12M12 7.5v9" />
                                    </svg>
                                </div>
                                <div class="upload-label">Klik atau seret file ke sini</div>
                                <div class="upload-hint">PDF, DOCX — maks. 10 MB</div>
                            </div>
                            <div v-else class="upload-file-info">
                                <div class="upload-check-icon">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="upload-file-name">{{ form.jawaban[index].file.name }}</div>
                                    <div class="upload-change">Klik untuk ganti file</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="soal-nav">
                        <button class="nav-btn nav-prev" @click="goPrev" :disabled="activeIndex === 0">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                            Sebelumnya
                        </button>
                        <div class="nav-dots">
                            <span v-for="(_, i) in penilaian.pertanyaans" :key="i" class="nav-dot"
                                :class="activeIndex === i ? 'nav-dot-active' : (isSoalAnswered(i) ? 'nav-dot-done' : '')">
                            </span>
                        </div>
                        <button v-if="activeIndex < totalSoal - 1" class="nav-btn nav-next" @click="goNext">
                            Berikutnya
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                        <button v-else class="nav-btn nav-finish" @click="showConfirm = true">
                            Selesai &amp; Kumpulkan
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </button>
                    </div>
                </div>

            </main>
        </div>

        <Teleport to="body">
            <div v-if="showConfirm" class="modal-backdrop" @click.self="showConfirm = false">
                <div class="modal-box">
                    <div class="modal-icon-wrap">
                        <svg width="28" height="28" fill="none" stroke="#2563EB" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <h3 class="modal-title">Kumpulkan jawaban?</h3>
                    <p class="modal-body">
                        Anda telah menjawab <strong>{{ answeredCount }} dari {{ totalSoal }}</strong> soal.
                        <template v-if="answeredCount < totalSoal">
                            <br><span class="modal-warn">{{ totalSoal - answeredCount }} soal masih belum
                                dijawab.</span>
                        </template>
                        Jawaban <strong>tidak dapat diubah</strong> setelah dikumpulkan.
                    </p>
                    <div class="modal-actions">
                        <button class="modal-cancel" @click="showConfirm = false">Batal, lanjut mengerjakan</button>
                        <button class="modal-confirm" @click="submitJawaban" :disabled="form.processing">
                            <svg v-if="form.processing" class="spin" width="14" height="14" fill="none"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="op25" />
                                <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            Ya, kumpulkan sekarang
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>

<style scoped>
/* ===== ROOT ===== */
.exam-root {
    min-height: 100vh;
    background: #F8FAFC;
    font-family: 'Plus Jakarta Sans', sans-serif;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
.exam-header {
    background: #fff;
    border-bottom: 1px solid #E2E8F0;
    position: sticky;
    top: 0;
    z-index: 30;
}

.header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.header-icon {
    width: 36px;
    height: 36px;
    background: #EFF6FF;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563EB;
    flex-shrink: 0;
}

.header-title {
    font-size: 14px;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
}

.header-sub {
    font-size: 12px;
    color: #94A3B8;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.progress-wrap {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 160px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #94A3B8;
}

.progress-count {
    font-weight: 700;
    color: #2563EB;
}

.progress-track {
    height: 5px;
    background: #E2E8F0;
    border-radius: 99px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #2563EB;
    border-radius: 99px;
    transition: width 0.4s ease;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #2563EB;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 9px 18px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
}

.btn-submit:hover {
    background: #1D4ED8;
}

.btn-submit:active {
    transform: scale(0.97);
}

.btn-submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* ===== LAYOUT ===== */
.exam-layout {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    padding: 1.5rem;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.25rem;
    align-items: start;
    flex: 1;
}

/* ===== SIDEBAR ===== */
.exam-sidebar {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem;
    position: sticky;
    top: 76px;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-heading {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94A3B8;
    margin-bottom: 0.75rem;
}

.sidebar-section {}

.soal-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 6px;
}

.soal-btn {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    border-radius: 8px;
    border: 1.5px solid #E2E8F0;
    background: #F8FAFC;
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.soal-btn:hover {
    border-color: #93C5FD;
    color: #2563EB;
    background: #EFF6FF;
}

.soal-active {
    border-color: #2563EB !important;
    background: #2563EB !important;
    color: #fff !important;
}

.soal-done {
    border-color: #BBF7D0;
    background: #F0FDF4;
    color: #16A34A;
}

.soal-check {
    position: absolute;
    top: 2px;
    right: 2px;
    color: #16A34A;
}

.sidebar-legend {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-top: 0.25rem;
    border-top: 1px solid #F1F5F9;
}

.legend-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #64748B;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
}

.dot-done {
    background: #F0FDF4;
    border: 1.5px solid #BBF7D0;
}

.dot-active {
    background: #2563EB;
    border: 1.5px solid #2563EB;
}

.dot-empty {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
}

.sidebar-stat {
    background: #F8FAFC;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-ring-wrap {
    position: relative;
    flex-shrink: 0;
}

.stat-ring {}

.stat-ring-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-ring-num {
    font-size: 14px;
    font-weight: 800;
    color: #1E293B;
}

.stat-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    font-size: 12px;
    color: #94A3B8;
}

.stat-val {
    font-size: 13px;
    font-weight: 700;
    color: #1E293B;
}

.stat-blue {
    color: #2563EB;
}

.stat-gray {
    color: #94A3B8;
}

/* ===== SOAL CARD ===== */
.exam-main {}

.soal-card {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.soal-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.soal-num {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
}

.num-default {
    background: #F1F5F9;
    color: #475569;
}

.num-done {
    background: #16A34A;
    color: #fff;
}

.jenis-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 6px;
    margin-bottom: 4px;
}

.badge-blue {
    background: #EFF6FF;
    color: #1D4ED8;
}

.badge-amber {
    background: #FFFBEB;
    color: #B45309;
}

.badge-purple {
    background: #F5F3FF;
    color: #6D28D9;
}

.badge-gray {
    background: #F1F5F9;
    color: #475569;
}

.soal-counter {
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
}

.soal-text {
    font-size: 15px;
    color: #1E293B;
    line-height: 1.75;
    font-weight: 500;
    white-space: pre-wrap;
}

.soal-images {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.soal-img {
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    max-height: 200px;
    object-fit: contain;
    width: 100%;
    background: #F8FAFC;
}

/* OPSI */
.opsi-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.opsi-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s;
    background: #FAFAFA;
}

.opsi-item:hover {
    border-color: #93C5FD;
    background: #F0F9FF;
}

.opsi-selected {
    border-color: #2563EB !important;
    background: #EFF6FF !important;
}

.opsi-radio {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #CBD5E1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
}

.radio-on {
    border-color: #2563EB;
    background: #2563EB;
}

.radio-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fff;
}

.opsi-letter {
    font-size: 12px;
    font-weight: 800;
    color: #94A3B8;
    width: 18px;
    flex-shrink: 0;
    text-align: center;
}

.opsi-selected .opsi-letter {
    color: #2563EB;
}

.opsi-text {
    font-size: 14px;
    color: #334155;
    line-height: 1.5;
    flex: 1;
}

.opsi-selected .opsi-text {
    color: #1E293B;
    font-weight: 500;
}

/* ESAI */
.essai-wrap {}

.essai-area {
    width: 100%;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 14px;
    color: #1E293B;
    line-height: 1.7;
    resize: vertical;
    outline: none;
    background: #FAFAFA;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: border-color 0.15s, background 0.15s;
    box-sizing: border-box;
}

.essai-area:focus {
    border-color: #2563EB;
    background: #fff;
    box-shadow: 0 0 0 3px #DBEAFE;
}

.essai-area::placeholder {
    color: #CBD5E1;
}

.essai-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}

.essai-hint {
    font-size: 12px;
    color: #CBD5E1;
}

.essai-count {
    font-size: 12px;
    color: #94A3B8;
    font-weight: 600;
}

/* UPLOAD */
.upload-zone {
    display: block;
    border: 2px dashed #CBD5E1;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.upload-zone:hover {
    border-color: #93C5FD;
}

.upload-done {
    border-color: #86EFAC !important;
    background: #F0FDF4 !important;
    border-style: solid !important;
}

.upload-empty {
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
}

.upload-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94A3B8;
}

.upload-zone:hover .upload-icon-wrap {
    background: #EFF6FF;
    color: #2563EB;
}

.upload-label {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}

.upload-zone:hover .upload-label {
    color: #2563EB;
}

.upload-hint {
    font-size: 12px;
    color: #CBD5E1;
}

.upload-file-info {
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 14px;
}

.upload-check-icon {
    width: 44px;
    height: 44px;
    background: #DCFCE7;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16A34A;
    flex-shrink: 0;
}

.upload-file-name {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
    word-break: break-all;
}

.upload-change {
    font-size: 12px;
    color: #86EFAC;
    margin-top: 2px;
}

/* NAV BAWAH */
.soal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.25rem;
    border-top: 1px solid #F1F5F9;
    margin-top: 0.5rem;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 10px;
    border: 1.5px solid #E2E8F0;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
}

.nav-btn:hover:not(:disabled) {
    border-color: #93C5FD;
    color: #2563EB;
    background: #F0F9FF;
}

.nav-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.nav-next {
    border-color: #BFDBFE;
    color: #2563EB;
    background: #EFF6FF;
}

.nav-next:hover {
    background: #DBEAFE !important;
    border-color: #93C5FD !important;
}

.nav-finish {
    border-color: #2563EB;
    color: #fff;
    background: #2563EB;
}

.nav-finish:hover {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
    color: #fff !important;
}

.nav-dots {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    max-width: 200px;
    justify-content: center;
}

.nav-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #E2E8F0;
    transition: all 0.2s;
}

.nav-dot-active {
    background: #2563EB;
    transform: scale(1.3);
}

.nav-dot-done {
    background: #86EFAC;
}

/* MODAL */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 1rem;
}

.modal-box {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
}

.modal-icon-wrap {
    width: 56px;
    height: 56px;
    background: #EFF6FF;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.modal-title {
    font-size: 18px;
    font-weight: 800;
    color: #1E293B;
    margin: 0 0 10px;
}

.modal-body {
    font-size: 14px;
    color: #64748B;
    line-height: 1.7;
    margin: 0 0 1.5rem;
}

.modal-warn {
    color: #DC2626;
    font-weight: 600;
}

.modal-actions {
    display: flex;
    gap: 10px;
}

.modal-cancel {
    flex: 1;
    padding: 11px;
    border-radius: 10px;
    border: 1.5px solid #E2E8F0;
    background: #fff;
    font-size: 13px;
    font-weight: 600;
    color: #64748B;
    cursor: pointer;
    transition: all 0.15s;
}

.modal-cancel:hover {
    background: #F8FAFC;
    border-color: #CBD5E1;
}

.modal-confirm {
    flex: 1;
    padding: 11px;
    border-radius: 10px;
    border: none;
    background: #2563EB;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: all 0.15s;
}

.modal-confirm:hover {
    background: #1D4ED8;
}

.modal-confirm:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* UTILS */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.op25 {
    opacity: 0.25;
}

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .exam-layout {
        grid-template-columns: 1fr;
    }

    .exam-sidebar {
        position: static;
    }

    .progress-wrap {
        display: none;
    }

    .header-title {
        max-width: 180px;
    }
}
</style>