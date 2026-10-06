<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Deteksi Duplikasi</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Deteksi Duplikasi Klaim (Advisory)
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Pendeteksian dini transaksi kuitansi dengan tanggal, nominal, atau merchant identik di antara berkas klaim
        </p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button
          @click="fetchData"
          :disabled="isLoading"
          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition disabled:opacity-50"
        >
          <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Segarkan
        </button>
      </div>
    </div>

    <!-- Advisory Warning Banner -->
    <div class="p-5 rounded-3xl bg-amber-500 text-slate-950 shadow-md flex items-start gap-4">
      <div class="p-2.5 rounded-2xl bg-black/10 shrink-0 mt-0.5">
        <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div class="space-y-1">
        <h3 class="text-sm font-black uppercase tracking-wide">Pemberitahuan Sistem (Advisory Detection)</h3>
        <p class="text-xs font-medium leading-relaxed opacity-90 max-w-3xl">
          Pendeteksian duplikasi ini bersifat advisory (peringatan awal) untuk membantu tim verifikator dan auditor internal mendeteksi potensi entri ganda atau manipulasi kuitansi. Verifikator disarankan memeriksa foto bukti fisik asli sebelum mengambil tindakan penolakan.
        </p>
      </div>
    </div>

    <!-- KPI Metric Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Potensi Kuitansi Ganda Ditemukan</p>
        <p class="text-3xl font-black text-slate-900 font-mono mt-1">
          {{ duplicateData.potential_duplicates_count || 0 }} <span class="text-xs text-slate-400 font-normal">Pasang Indikasi</span>
        </p>
      </div>
      <span
        class="px-3 py-1 rounded-full text-xs font-bold"
        :class="duplicateData.potential_duplicates_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
      >
        {{ duplicateData.potential_duplicates_count > 0 ? 'Membutuhkan Tinjauan' : 'Semua Bersih' }}
      </span>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="h-64 bg-slate-200/60 rounded-3xl animate-pulse"></div>

    <!-- Duplicate Table -->
    <div v-else class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
          Daftar Pasangan Transaksi dengan Kecocokan Parameter
        </h3>
        <span class="text-xs text-slate-400 font-medium">Kriteria: Nominal & Tanggal Transaksi Sama</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Nominal Transaksi</th>
              <th class="pb-3">Tanggal Nota</th>
              <th class="pb-3">Merchant / Toko</th>
              <th class="pb-3">Klaim Asal (1)</th>
              <th class="pb-3">Klaim Terindikasi (2)</th>
              <th class="pb-3">Requester</th>
              <th class="pb-3 text-center">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="!duplicateData.records || duplicateData.records.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400">
                Tidak ditemukan potensi kuitansi duplikat dalam database.
              </td>
            </tr>
            <tr v-for="(rec, idx) in duplicateData.records" :key="idx" class="hover:bg-slate-50">
              <td class="py-3.5 font-bold font-mono text-slate-900">
                Rp {{ formatNumber(rec.amount) }}
              </td>
              <td class="py-3.5 text-slate-600 font-mono">
                {{ rec.transaction_date }}
              </td>
              <td class="py-3.5 font-medium text-slate-800">
                {{ rec.merchant || '-' }}
              </td>
              <td class="py-3.5">
                <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                  {{ rec.claim1_number }}
                </span>
              </td>
              <td class="py-3.5">
                <span class="font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                  {{ rec.claim2_number }}
                </span>
              </td>
              <td class="py-3.5 text-slate-700">
                {{ rec.requester_name }}
              </td>
              <td class="py-3.5 text-center">
                <router-link
                  to="/claims"
                  class="text-indigo-600 hover:text-indigo-800 font-bold text-[11px] underline"
                >
                  Buka Klaim
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const duplicateData = ref({});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/duplicates');
    duplicateData.value = res.data.data || {};
  } catch (e) {
    // handled
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchData();
});
</script>
