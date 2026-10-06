<template>
    <div class="min-h-screen bg-slate-100 p-2 sm:p-6 print:p-0 print:bg-white text-slate-900">
        <!-- Interactive Toolbar (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 print:hidden">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button
                        @click="goBack"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Detail Klaim
                    </button>
                    <span class="text-xs text-slate-400">|</span>
                    <span class="text-xs font-semibold text-slate-600">
                        Pratinjau Cetak: Form Rekap Biaya Operasional Kendaraan & Lampiran Struk
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="triggerPrint"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs transition flex items-center gap-2 shadow-md shadow-indigo-600/20 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Dokumen / Simpan PDF
                    </button>
                </div>
            </div>

            <!-- Helpful tip banner -->
            <div class="mt-2.5 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-[11px] text-indigo-800 flex items-center justify-between">
                <span>💡 Tip: Pada jendela cetak peramban, pilih orientasi <strong>Landscape</strong> atau <strong>Portrait</strong> dengan opsi <em>Background graphics</em> dicentang.</span>
                <span class="font-bold">Standar PT. Indomarco Prismatama</span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-5xl mx-auto py-20 text-center text-slate-400 print:hidden">
            <div class="inline-flex items-center gap-2 font-medium text-sm">
                <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Menyiapkan format cetak klaim...
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="!claim" class="max-w-5xl mx-auto py-20 text-center text-slate-500 print:hidden">
            Data klaim tidak ditemukan.
        </div>

        <!-- ================= PAGE 1: REKAP BIAYA OPERATIONAL KENDARAAN ================= -->
        <div
            v-else
            class="max-w-5xl mx-auto bg-white p-6 sm:p-10 border border-slate-300 shadow-lg rounded-2xl print:border-none print:shadow-none print:p-0 print:max-w-none print:rounded-none page-rekap"
        >
            <!-- Corporate Header -->
            <div class="border-b-2 border-black pb-2 mb-4">
                <h1 class="text-base sm:text-lg font-black tracking-wide uppercase text-black leading-tight">
                    PT.INDOMARCO PRISMATAMA
                </h1>
                <h2 class="text-sm sm:text-base font-black tracking-wide uppercase text-black leading-tight">
                    CABANG YOGYAKARTA
                </h2>
                <div class="text-center mt-3 mb-1">
                    <h3 class="text-base sm:text-lg font-black tracking-wider uppercase text-black underline underline-offset-4">
                        REKAP BIAYA OPERATIONAL KENDARAAN
                    </h3>
                    <p class="text-[10px] text-slate-500 font-mono mt-1 print:hidden">
                        No. Referensi Klaim: {{ claim.claim_number }}
                    </p>
                </div>
            </div>

            <!-- Metadata Information Table / Grid -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs font-bold text-black mb-4 py-1">
                <div class="space-y-1">
                    <div class="flex">
                        <span class="w-32 uppercase">NAMA PEMAKAI</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 uppercase font-black">{{ claim.requester?.name || '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 uppercase">JABATAN</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 uppercase">{{ requesterJabatan }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 uppercase">NIK</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 font-mono uppercase">{{ claim.requester?.nik || '-' }}</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="flex">
                        <span class="w-36 uppercase">JENIS KENDARAAN</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 uppercase font-black">{{ vehicleTypeTitle }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-36 uppercase">NO POL</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 font-mono uppercase font-black">{{ vehiclePlateNumber }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-36 uppercase">PERIODE</span>
                        <span class="w-4">:</span>
                        <span class="flex-1 uppercase font-bold">{{ formattedPeriode }}</span>
                    </div>
                </div>
            </div>

            <!-- Table Matrix (Rekap Bahan Bakar, Rasio, Parkir/Tol, Keterangan) -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-black text-[11px] leading-tight text-black">
                    <thead>
                        <tr class="bg-slate-100 print:bg-transparent">
                            <th rowspan="2" class="border border-black px-1.5 py-2 text-center w-8 uppercase">NO</th>
                            <th rowspan="2" class="border border-black px-2 py-2 text-center w-20 uppercase">TGL</th>
                            <th colspan="5" class="border border-black px-2 py-1.5 text-center uppercase font-black">
                                BAHAN BAKAR
                            </th>
                            <th rowspan="2" class="border border-black px-2 py-2 text-center w-14 uppercase">RASIO</th>
                            <th rowspan="2" class="border border-black px-2 py-2 text-center w-24 uppercase">PARKIR/TOL</th>
                            <th rowspan="2" class="border border-black px-2 py-2 text-center uppercase min-w-[180px]">KETERANGAN</th>
                        </tr>
                        <tr class="bg-slate-50 print:bg-transparent text-[10px]">
                            <th class="border border-black px-1.5 py-1.5 text-center w-16 uppercase">KM AWAL</th>
                            <th class="border border-black px-1.5 py-1.5 text-center w-16 uppercase">KM AKHIR</th>
                            <th class="border border-black px-1.5 py-1.5 text-center w-16 uppercase">SELISIH KM</th>
                            <th class="border border-black px-1.5 py-1.5 text-center w-14 uppercase">LITER</th>
                            <th class="border border-black px-2 py-1.5 text-center w-20 uppercase">RP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Actual Data Rows -->
                        <tr v-for="(row, idx) in tableRows" :key="idx" class="h-7">
                            <td class="border border-black px-1.5 py-1 text-center font-mono">{{ idx + 1 }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono whitespace-nowrap">{{ row.tgl }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono">{{ row.kmAwal }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono">{{ row.kmAkhir }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono font-bold">{{ row.selisihKm }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono">{{ row.liter }}</td>
                            <td class="border border-black px-2 py-1 text-right font-mono font-semibold">{{ row.rpBbm }}</td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono font-bold">{{ row.rasio }}</td>
                            <td class="border border-black px-2 py-1 text-right font-mono">{{ row.parkirTol }}</td>
                            <td class="border border-black px-2 py-1 text-left truncate max-w-[220px]" :title="row.keterangan">{{ row.keterangan }}</td>
                        </tr>

                        <!-- Empty Filler Rows to guarantee at least 8 rows like the original corporate template -->
                        <tr v-for="emptyIdx in emptyRowsCount" :key="'empty-' + emptyIdx" class="h-6">
                            <td class="border border-black px-1.5 py-1 text-center font-mono text-slate-400">
                                {{ tableRows.length + emptyIdx }}
                            </td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                            <td class="border border-black px-1.5 py-1 text-center">&nbsp;</td>
                        </tr>

                        <!-- Sub Total Row -->
                        <tr class="font-bold bg-slate-50 print:bg-transparent h-7">
                            <td colspan="4" class="border border-black px-3 py-1 text-center uppercase tracking-wider">
                                SUB TOTAL
                            </td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono font-black">
                                {{ totalSelisihKm > 0 ? totalSelisihKm : '' }}
                            </td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono font-black">
                                {{ totalLiterBbm > 0 ? totalLiterBbm : '' }}
                            </td>
                            <td class="border border-black px-2 py-1 text-right font-mono font-black">
                                {{ totalRpBbm > 0 ? formatNumber(totalRpBbm) : '' }}
                            </td>
                            <td class="border border-black px-1.5 py-1 text-center font-mono font-bold">
                                {{ overallRasio }}
                            </td>
                            <td class="border border-black px-2 py-1 text-right font-mono font-black">
                                {{ totalParkirTol > 0 ? formatNumber(totalParkirTol) : '' }}
                            </td>
                            <td class="border border-black px-2 py-1 text-center text-slate-400">-</td>
                        </tr>

                        <!-- Total Row -->
                        <tr class="font-black bg-slate-100 print:bg-transparent h-8">
                            <td colspan="4" class="border border-black px-3 py-1 text-center uppercase tracking-widest text-xs">
                                TOTAL
                            </td>
                            <td colspan="6" class="border border-black px-4 py-1 text-left font-mono text-xs">
                                Rp {{ formatNumber(claim.total_amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section (Menyetujui, Mengetahui, Dibuat) -->
            <div class="mt-6 pt-2 text-xs font-bold text-black break-inside-avoid">
                <div class="flex justify-end mb-4">
                    <p class="font-bold">
                        {{ branchNameCapitalized }}, {{ formattedDateSignature }}
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <!-- Menyetujui -->
                    <div class="flex flex-col justify-between h-28">
                        <p class="uppercase font-bold">Menyetujui,</p>
                        <div class="mt-auto">
                            <p class="uppercase font-bold tracking-wider">( GAM / SPV )</p>
                        </div>
                    </div>

                    <!-- Mengetahui -->
                    <div class="flex flex-col justify-between h-28">
                        <p class="uppercase font-bold">Mengetahui,</p>
                        <div class="mt-auto flex justify-around">
                            <span class="uppercase font-bold tracking-wider">( MGR DEPT. )</span>
                            <span class="uppercase font-bold tracking-wider">( SPV DEPT. )</span>
                        </div>
                    </div>

                    <!-- Dibuat -->
                    <div class="flex flex-col justify-between h-28">
                        <p class="uppercase font-bold">Dibuat,</p>
                        <div class="mt-auto">
                            <p class="font-bold uppercase underline">
                                ( {{ claim.requester?.name || '..................................' }} )
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mini Audit Note on bottom of Page 1 -->
            <div class="mt-8 pt-2 border-t border-slate-300 flex items-center justify-between text-[10px] text-slate-500 font-mono">
                <span>Dokumen dicetak dari Sistem Klaim EDP • Status: {{ claim.current_state }}</span>
                <span>No. Klaim: {{ claim.claim_number }}</span>
            </div>
        </div>

        <!-- ================= PAGE 2: LAMPIRAN BUKTI TRANSAKSI & FOTO STRUK ================= -->
        <div
            v-if="claim"
            class="max-w-5xl mx-auto bg-white p-6 sm:p-10 border border-slate-300 shadow-lg rounded-2xl mt-8 print:mt-0 print:border-none print:shadow-none print:p-0 print:max-w-none print:rounded-none page-lampiran"
        >
            <!-- Header Lampiran -->
            <div class="border-b-2 border-black pb-3 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs sm:text-sm font-black uppercase text-black tracking-wider">
                            PT.INDOMARCO PRISMATAMA — CABANG {{ branchName }}
                        </h2>
                        <h3 class="text-sm sm:text-base font-black uppercase tracking-wider text-black mt-0.5">
                            LAMPIRAN BUKTI TRANSAKSI & FOTO STRUK / NOTA
                        </h3>
                    </div>

                    <div class="text-right text-[11px] font-mono font-bold text-slate-700">
                        <p>No: {{ claim.claim_number }}</p>
                        <p>{{ claim.claim_type }} — {{ formatDate(claim.claim_date) }}</p>
                    </div>
                </div>

                <div class="mt-2 text-xs text-slate-600 flex items-center gap-4 flex-wrap">
                    <span><strong>Pemohon:</strong> {{ claim.requester?.name }} (NIK: {{ claim.requester?.nik || '-' }})</span>
                    <span><strong>Kendaraan:</strong> {{ vehicleTypeTitle }} ({{ vehiclePlateNumber }})</span>
                    <span><strong>Total Klaim:</strong> Rp {{ formatNumber(claim.total_amount) }}</span>
                </div>
            </div>

            <!-- List Foto Struk yang Diunggah via HP -->
            <div v-if="receiptPhotos.length > 0" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div
                        v-for="(item, idx) in receiptPhotos"
                        :key="idx"
                        class="border border-slate-300 rounded-xl p-3.5 bg-slate-50/50 print:bg-white print:border-black break-inside-avoid space-y-3"
                    >
                        <!-- Header Nota Info -->
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2 text-xs">
                            <div>
                                <span class="font-black text-black uppercase">
                                    Bukti #{{ idx + 1 }}: {{ item.receipt?.merchant || item.doc.original_filename }}
                                </span>
                                <p class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    Tgl Transaksi: {{ item.receipt?.transaction_date ? formatDate(item.receipt.transaction_date) : '-' }}
                                </p>
                            </div>
                            <span class="font-black font-mono text-sm text-indigo-700 print:text-black">
                                Rp {{ formatNumber(item.receipt?.amount || claim.total_amount) }}
                            </span>
                        </div>

                        <!-- Foto Struk Asli dari HP -->
                        <div class="flex items-center justify-center bg-white border border-slate-200 rounded-lg p-2 overflow-hidden min-h-[220px]">
                            <img
                                v-if="isImage(item.doc)"
                                :src="getDocViewUrl(item.doc)"
                                :alt="'Struk ' + (item.receipt?.merchant || 'Nota')"
                                class="max-h-[380px] w-auto max-w-full object-contain rounded shadow-2xs print:shadow-none"
                            />
                            <div v-else class="text-center p-6 text-slate-400">
                                <span class="text-3xl block mb-2">📄</span>
                                <span class="text-xs font-bold text-slate-700 block">{{ item.doc.original_filename }}</span>
                                <span class="text-[10px] text-slate-400 block mt-1">(Dokumen PDF Lampiran Nota)</span>
                            </div>
                        </div>

                        <!-- Metadata Struk Footer with GPS & Timestamp -->
                        <div class="text-[10px] text-slate-600 font-mono space-y-1 pt-1.5 border-t border-slate-200">
                            <!-- GPS Location Coordinates -->
                            <div v-if="item.doc.latitude && item.doc.longitude" class="flex items-center justify-between flex-wrap gap-1">
                                <span class="font-bold text-emerald-800 flex items-center gap-1">
                                    <span>📍</span>
                                    <span>GPS: {{ Number(item.doc.latitude).toFixed(6) }}, {{ Number(item.doc.longitude).toFixed(6) }}</span>
                                    <span v-if="item.doc.gps_accuracy_meters" class="text-slate-400 font-normal">
                                        (±{{ Math.round(item.doc.gps_accuracy_meters) }}m)
                                    </span>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        ✓ Lokasi SPBU
                                    </span>
                                    <a
                                        :href="'https://www.google.com/maps?q=' + item.doc.latitude + ',' + item.doc.longitude"
                                        target="_blank"
                                        class="text-indigo-600 hover:underline font-bold print:hidden"
                                        title="Buka titik koordinat di Google Maps"
                                    >
                                        Buka Peta ↗
                                    </a>
                                </div>
                            </div>
                            <div v-else class="text-slate-400 flex items-center gap-1">
                                <span>📍</span>
                                <span>GPS: Tidak terdeteksi saat foto diambil</span>
                            </div>

                            <!-- Time & File Info -->
                            <div class="flex items-center justify-between text-slate-500 pt-0.5">
                                <span>
                                    ⏰ Waktu: {{ formatDateTime(item.doc.captured_at || item.doc.created_at) }}
                                </span>
                                <span>{{ item.doc.original_filename }} ({{ (item.doc.file_size / 1024).toFixed(0) }} KB)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jika belum ada foto struk terlampir -->
            <div v-else class="py-12 text-center text-xs text-slate-400 border-2 border-dashed border-slate-200 rounded-2xl print:border-black">
                <span class="text-2xl block mb-1">📸</span>
                <p class="font-bold text-slate-600">Tidak ada lampiran foto struk yang diunggah.</p>
                <p class="text-[11px] text-slate-400 mt-1">Nota transaksi dicatat secara langsung di sistem.</p>
            </div>

            <!-- Footer Lampiran -->
            <div class="mt-8 pt-3 border-t border-slate-300 flex items-center justify-between text-[10px] text-slate-500 font-mono">
                <span>Halaman Lampiran Struk & Dokumen Pendukung</span>
                <span>Dicetak pada: {{ printedAtString }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api/client";

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const claim = ref(null);
const token = localStorage.getItem("token") || "";

const printedAtString = new Date().toLocaleString("id-ID", {
    dateStyle: "full",
    timeStyle: "short",
});

function goBack() {
    if (claim.value?.id) {
        router.push(`/claims/${claim.value.id}`);
    } else {
        router.push("/claims");
    }
}

function triggerPrint() {
    window.print();
}

async function loadClaimData() {
    loading.value = true;
    try {
        const id = route.params.id;
        const res = await api.get(`/claims/${id}`);
        claim.value = res.data.data;
    } catch (err) {
        alert("Gagal memuat rincian klaim untuk dicetak.");
    } finally {
        loading.value = false;
    }
}

const branchName = computed(() => {
    if (claim.value?.requester?.wilayah) {
        return claim.value.requester.wilayah.toUpperCase();
    }
    return "YOGYAKARTA";
});

const branchNameCapitalized = computed(() => {
    const b = branchName.value;
    return b.charAt(0) + b.slice(1).toLowerCase();
});

const requesterJabatan = computed(() => {
    const roles = claim.value?.requester?.roles;
    if (Array.isArray(roles) && roles.length > 0) {
        const first = roles[0];
        const rName = typeof first === "object" ? first.name : first;
        if (rName === "EDP_OPERASIONAL") return "EDP Operasional";
        if (rName === "EDP_ADM") return "Adm EDP";
        if (rName === "EDP_SUPERVISOR") return "Supervisor EDP";
        if (rName === "EDP_MANAGER") return "Manager EDP";
        return rName;
    }
    return "EDP Operasional";
});

const bbmDetail = computed(() => {
    return claim.value?.bbm_detail || claim.value?.bbmDetail || null;
});

const vehicle = computed(() => {
    return bbmDetail.value?.vehicle || null;
});

const vehicleTypeTitle = computed(() => {
    const v = vehicle.value;
    if (!v) return "SEPEDA MOTOR";
    const type = v.vehicle_type === "CAR" ? "MOBIL" : "SEPEDA MOTOR";
    return `${type} (${v.name})`;
});

const vehiclePlateNumber = computed(() => {
    return vehicle.value?.plate_number || "-";
});

const formattedPeriode = computed(() => {
    if (!claim.value?.claim_date) return "-";
    const d = new Date(claim.value.claim_date);
    return d.toLocaleDateString("id-ID", { month: "long", year: "numeric" }).toUpperCase();
});

const formattedDateSignature = computed(() => {
    if (!claim.value?.claim_date) {
        return new Date().toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
    }
    const d = new Date(claim.value.claim_date);
    return d.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
});

// Construct Data Rows for the Corporate Spreadsheet Table
// Strict mapping:
// - KM AWAL = last_odometer_km
// - KM AKHIR = current_odometer_km
// - SELISIH KM = current_odometer_km - last_odometer_km
// - LITER = fuel_liter
// - RASIO = actual_km_per_liter
// - KETERANGAN = claims.description
const tableRows = computed(() => {
    if (!claim.value) return [];

    const rows = [];
    const isBbm = claim.value.claim_type === "BBM";
    const bbm = bbmDetail.value;
    const receipts = claim.value.receipts || [];

    if (isBbm && bbm) {
        // km awal diambil dari last_odometer_km
        const kmAwal = bbm.last_odometer_km != null ? Number(bbm.last_odometer_km) : null;
        // km akhir diambil dari current_odometer_km
        const kmAkhir = bbm.current_odometer_km != null ? Number(bbm.current_odometer_km) : null;
        // untuk selisih adalah current_odometer_km dikurangi last_odometer_km
        let selisihKm = null;
        if (kmAkhir != null && kmAwal != null) {
            selisihKm = Math.round((kmAkhir - kmAwal) * 10) / 10;
        } else if (bbm.distance_km != null) {
            selisihKm = Number(bbm.distance_km);
        }

        // Liter diambil dari fuel_liter
        const liter = bbm.fuel_liter != null ? Number(bbm.fuel_liter) : null;

        // rasio diambil actual_km_per_liter
        const rasio = bbm.actual_km_per_liter != null ? Number(bbm.actual_km_per_liter) : null;

        // Keterangan diambil dari table claims field description
        const keterangan = claim.value.description || "-";

        // TGL diambil dari claim_date
        const tgl = formatDate(claim.value.claim_date);

        // RP diambil dari total_amount atau receipt amount
        const rp = formatNumber(claim.value.total_amount);

        rows.push({
            tgl,
            kmAwal: kmAwal != null ? kmAwal : "-",
            kmAkhir: kmAkhir != null ? kmAkhir : "-",
            selisihKm: selisihKm != null ? selisihKm : "-",
            liter: liter != null ? liter : "-",
            rpBbm: rp,
            rasio: rasio != null ? rasio : "-",
            parkirTol: "-",
            keterangan,
        });

        // If there are additional receipts (e.g. Tol / Parkir attached to the claim):
        if (receipts.length > 1) {
            receipts.slice(1).forEach((r) => {
                rows.push({
                    tgl: formatDate(r.transaction_date || claim.value.claim_date),
                    kmAwal: "-",
                    kmAkhir: "-",
                    selisihKm: "-",
                    liter: "-",
                    rpBbm: "-",
                    rasio: "-",
                    parkirTol: formatNumber(r.amount),
                    keterangan: r.description || r.merchant || keterangan,
                });
            });
        }
    } else {
        // Tol or Health claim
        const keterangan = claim.value.description || "-";
        if (receipts.length > 0) {
            receipts.forEach((r) => {
                rows.push({
                    tgl: formatDate(r.transaction_date || claim.value.claim_date),
                    kmAwal: "-",
                    kmAkhir: "-",
                    selisihKm: "-",
                    liter: "-",
                    rpBbm: "-",
                    rasio: "-",
                    parkirTol: formatNumber(r.amount),
                    keterangan: r.description || keterangan || r.merchant,
                });
            });
        } else {
            rows.push({
                tgl: formatDate(claim.value.claim_date),
                kmAwal: "-",
                kmAkhir: "-",
                selisihKm: "-",
                liter: "-",
                rpBbm: "-",
                rasio: "-",
                parkirTol: formatNumber(claim.value.total_amount),
                keterangan,
            });
        }
    }

    return rows;
});

const emptyRowsCount = computed(() => {
    const minRows = 8;
    const current = tableRows.value.length;
    return current < minRows ? minRows - current : 0;
});

const totalSelisihKm = computed(() => {
    const bbm = bbmDetail.value;
    if (bbm?.current_odometer_km != null && bbm?.last_odometer_km != null) {
        return Math.round((Number(bbm.current_odometer_km) - Number(bbm.last_odometer_km)) * 10) / 10;
    }
    return bbm?.distance_km || 0;
});

const totalLiterBbm = computed(() => {
    return bbmDetail.value?.fuel_liter != null ? Number(bbmDetail.value.fuel_liter) : 0;
});

const totalRpBbm = computed(() => {
    if (claim.value?.claim_type === "BBM") {
        return claim.value.total_amount || 0;
    }
    return 0;
});

const overallRasio = computed(() => {
    if (bbmDetail.value?.actual_km_per_liter != null) {
        return bbmDetail.value.actual_km_per_liter;
    }
    return "-";
});

const totalParkirTol = computed(() => {
    if (claim.value?.claim_type === "TOL") {
        return claim.value.total_amount || 0;
    }
    return 0;
});

// Receipts Photo Attachments list
const receiptPhotos = computed(() => {
    if (!claim.value) return [];
    const list = [];
    const docs = claim.value.documents || [];
    const receipts = claim.value.receipts || [];

    docs.forEach((doc) => {
        // Match receipt if claim_receipt_id is linked
        const linkedReceipt = receipts.find((r) => r.id === doc.claim_receipt_id);
        list.push({
            doc,
            receipt: linkedReceipt || receipts[0] || null,
        });
    });

    return list;
});

function isImage(doc) {
    if (!doc) return false;
    if (doc.mime_type && doc.mime_type.startsWith("image/")) return true;
    const fn = (doc.original_filename || "").toLowerCase();
    return fn.endsWith(".jpg") || fn.endsWith(".jpeg") || fn.endsWith(".png") || fn.endsWith(".webp");
}

function getDocViewUrl(doc) {
    if (!doc) return "";
    return `/api/documents/${doc.id}/view?token=${encodeURIComponent(token)}`;
}

function formatNumber(num) {
    return new Intl.NumberFormat("id-ID").format(num || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }) + " " + d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }) + " WIB";
}

onMounted(() => {
    loadClaimData();
});
</script>

<style scoped>
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-size: 11px;
    }

    .page-rekap {
        page-break-after: always;
        break-after: page;
    }

    .page-lampiran {
        page-break-before: always;
        break-before: page;
    }

    table {
        border-collapse: collapse !important;
    }

    th, td {
        border: 1px solid #000000 !important;
    }
}
</style>
