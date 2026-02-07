<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    open: { type: Boolean, default: false },
    kelas: { type: Object, required: true },
});
const emit = defineEmits(["close"]);

const form = useForm({
    nama_kelas: "",
    kode_gabung: "",
    deskripsi: "",
    persentase_tugas: 30,
    persentase_uts: 30,
    persentase_uas: 40,
});

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        form.nama_kelas = props.kelas?.nama_kelas ?? "";
        form.kode_gabung = props.kelas?.kode_gabung ?? "";
        form.deskripsi = props.kelas?.deskripsi ?? "";
        form.persentase_tugas = props.kelas?.persentase_tugas ?? 30;
        form.persentase_uts = props.kelas?.persentase_uts ?? 30;
        form.persentase_uas = props.kelas?.persentase_uas ?? 40;
        form.clearErrors();
    },
);

const total = computed(() => {
    const t = Number(form.persentase_tugas || 0);
    const u = Number(form.persentase_uts || 0);
    const a = Number(form.persentase_uas || 0);
    return t + u + a;
});

const totalOk = computed(() => total.value === 100);

/** generator kode gabung */
const randomJoinCode = (len = 8) => {
    // format aman: huruf besar + angka, tanpa karakter mirip (O/0, I/1)
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    let out = "";
    const arr = new Uint32Array(len);
    crypto.getRandomValues(arr);
    for (let i = 0; i < len; i++) {
        out += chars[arr[i] % chars.length];
    }
    return out;
};

const shuffleKode = () => {
    form.kode_gabung = randomJoinCode(8);
};

const submit = () => {
    form.patch(route("dosen.kelas.settings.update", props.kelas.uuid), {
        preserveScroll: true,
        onSuccess: () => emit("close"),
    });
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50">
        <!-- overlay -->
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
            @click="emit('close')"
        />

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="w-full max-w-xl rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 overflow-hidden max-h-[85vh] flex flex-col"
            >
                <!-- header -->
                <div
                    class="px-5 py-4 border-b border-gray-200/70 flex items-start justify-between gap-4 shrink-0"
                >
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900">
                            Setelan kelas
                        </div>
                        <div class="mt-1 text-xs text-gray-500 truncate">
                            Atur informasi kelas dan bobot penilaian
                        </div>
                    </div>

                    <button
                        class="shrink-0 w-10 h-10 rounded-2xl hover:bg-gray-100 grid place-items-center transition"
                        @click="emit('close')"
                        type="button"
                        aria-label="Tutup"
                    >
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <!-- FORM: wajib flex-col + min-h-0 biar body bisa overflow -->
                <form
                    class="flex-1 min-h-0 flex flex-col"
                    @submit.prevent="submit"
                >
                    <!-- BODY (scrollable): wajib flex-1 + min-h-0 + overflow-y-auto -->
                    <div
                        class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-5 space-y-5"
                    >
                        <!-- info kelas -->
                        <div
                            class="rounded-2xl border border-gray-200/70 bg-gray-50/60 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[11px] text-gray-500">
                                        Kelas
                                    </div>
                                    <div
                                        class="mt-1 font-semibold text-gray-900 truncate"
                                    >
                                        {{ kelas.nama_kelas }}
                                    </div>
                                </div>

                                <div
                                    class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 ring-1 ring-gray-200/70"
                                    :class="
                                        totalOk
                                            ? 'text-emerald-700'
                                            : 'text-amber-700'
                                    "
                                    title="Total persentase"
                                >
                                    <span class="text-xs font-semibold"
                                        >Total</span
                                    >
                                    <span class="text-xs font-black"
                                        >{{ total }}%</span
                                    >
                                </div>
                            </div>

                            <div
                                v-if="!totalOk"
                                class="mt-3 text-xs text-amber-700"
                            >
                                Total bobot harus <b>100%</b>.
                            </div>
                        </div>

                        <!-- NAMA KELAS -->
                        <div>
                            <label class="text-xs font-semibold text-gray-800">
                                Nama kelas
                            </label>
                            <div class="mt-2">
                                <input
                                    v-model="form.nama_kelas"
                                    class="w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                    placeholder="Contoh: Basis Data - A"
                                />
                            </div>
                            <div
                                v-if="form.errors.nama_kelas"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.nama_kelas }}
                            </div>
                        </div>

                        <!-- KODE GABUNG -->
                        <div>
                            <label class="text-xs font-semibold text-gray-800">
                                Kode gabung
                            </label>

                            <div class="mt-2 flex items-stretch gap-2">
                                <div class="relative flex-1">
                                    <input
                                        v-model="form.kode_gabung"
                                        class="w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 pr-24 font-mono tracking-wider focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                        placeholder="Contoh: 8CHARCODE"
                                    />
                                    <div
                                        class="absolute inset-y-0 right-3 flex items-center"
                                    >
                                        <span
                                            class="text-[11px] px-2 py-1 rounded-lg bg-gray-100 text-gray-600 ring-1 ring-gray-200"
                                        >
                                            unik
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="shrink-0 rounded-2xl px-4 text-sm font-semibold bg-gray-900 text-white hover:bg-black transition"
                                    @click="shuffleKode"
                                    title="Buat kode gabung baru secara acak"
                                >
                                    Ganti &amp; Acak
                                </button>
                            </div>

                            <div class="mt-2 text-[11px] text-gray-500">
                                Mahasiswa memakai kode ini untuk bergabung.
                            </div>

                            <div
                                v-if="form.errors.kode_gabung"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.kode_gabung }}
                            </div>
                        </div>

                        <!-- DESKRIPSI -->
                        <div>
                            <label class="text-xs font-semibold text-gray-800">
                                Deskripsi
                            </label>
                            <div class="mt-2">
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="4"
                                    class="w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition resize-y"
                                    placeholder="Opsional. Info singkat kelas, aturan, link penting, dll."
                                />
                            </div>
                            <div
                                v-if="form.errors.deskripsi"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.deskripsi }}
                            </div>
                        </div>

                        <!-- BOBOT -->
                        <div class="rounded-2xl border border-gray-200/70 p-4">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div>
                                    <div
                                        class="text-xs font-semibold text-gray-900"
                                    >
                                        Bobot penilaian
                                    </div>
                                    <div class="mt-1 text-[11px] text-gray-500">
                                        Pastikan total 100% (Tugas + UTS + UAS).
                                    </div>
                                </div>

                                <div
                                    class="text-[11px] font-semibold px-2.5 py-1.5 rounded-xl"
                                    :class="
                                        totalOk
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-amber-50 text-amber-700'
                                    "
                                >
                                    {{ totalOk ? "OK" : "Perlu 100%" }}
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div
                                    class="rounded-2xl bg-gray-50 p-3 ring-1 ring-gray-200/70"
                                >
                                    <label
                                        class="text-[11px] font-semibold text-gray-700"
                                        >Tugas (%)</label
                                    >
                                    <input
                                        v-model.number="form.persentase_tugas"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                    />
                                </div>

                                <div
                                    class="rounded-2xl bg-gray-50 p-3 ring-1 ring-gray-200/70"
                                >
                                    <label
                                        class="text-[11px] font-semibold text-gray-700"
                                        >UTS (%)</label
                                    >
                                    <input
                                        v-model.number="form.persentase_uts"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                    />
                                </div>

                                <div
                                    class="rounded-2xl bg-gray-50 p-3 ring-1 ring-gray-200/70"
                                >
                                    <label
                                        class="text-[11px] font-semibold text-gray-700"
                                        >UAS (%)</label
                                    >
                                    <input
                                        v-model.number="form.persentase_uas"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                    />
                                </div>
                            </div>

                            <div
                                v-if="form.errors.persentase_tugas"
                                class="mt-3 text-xs text-red-600"
                            >
                                {{ form.errors.persentase_tugas }}
                            </div>
                        </div>

                        <!-- spacer biar konten ga ketiban footer -->
                        <div class="h-8"></div>
                    </div>

                    <!-- FOOTER -->
                    <div
                        class="shrink-0 bg-white/95 backdrop-blur border-t border-gray-200/70 px-5 py-4 flex items-center justify-between"
                    >
                        <button
                            type="button"
                            class="rounded-2xl px-4 py-2.5 text-sm font-semibold bg-gray-100 hover:bg-gray-200 transition"
                            @click="emit('close')"
                        >
                            Batal
                        </button>

                        <div class="flex items-center gap-3">
                            <div
                                v-if="form.hasErrors"
                                class="text-xs text-red-600"
                            >
                                Periksa input yang masih salah.
                            </div>

                            <button
                                type="submit"
                                class="rounded-2xl px-5 py-2.5 text-sm font-semibold text-white transition disabled:opacity-60"
                                :class="
                                    totalOk
                                        ? 'bg-gray-900 hover:bg-black'
                                        : 'bg-gray-400 cursor-not-allowed'
                                "
                                :disabled="form.processing || !totalOk"
                            >
                                {{
                                    form.processing
                                        ? "Menyimpan..."
                                        : "Simpan perubahan"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
