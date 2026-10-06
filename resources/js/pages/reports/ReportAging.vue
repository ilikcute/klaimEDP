<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Aging Klaim</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Aging Klaim (Umur Tunggakan)
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Monitoring distribusi usia berkas klaim yang sedang aktif berjalan di dalam pipeline proses
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

    <!-- Total Open Claims Counter -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Berkas Klaim Aktif (Open Claims)</p>
        <p class="text-3xl font-black text-slate-900 font-mono mt-1">{{ agingData.total_open || 0 }} <span class="text-xs text-slate-400 font-normal">Berkas</span></p>
        <p class="text-xs text-slate-500 mt-1">Belum mencapai status terminal (PAID, REJECTED, atau CANCELLED)</p>
      </div>

      <router-link
        to="/tasks"
        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-xs"
      >
        Tinjau Antrean Tugas &rarr;
      </router-link>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-5 gap-3 animate-pulse">
      <div v-for="i in 5" :key="i" class="h-28 bg-slate-200/60 rounded-2xl"></div>
    </div>

    <!-- Aging 5 Brackets Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <!-- 0-1 Hari -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">0 – 1 Hari</span>
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        </div>
        <p class="text-2xl font-black text-slate-900 font-mono">{{ agingData.brackets?.['0_1_day'] || 0 }}</p>
        <p class="text-[11px] text-slate-400">Sangat baru / Segar</p>
      </div>

      <!-- 2-3 Hari -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">2 – 3 Hari</span>
          <span class="w-2 h-2 rounded-full bg-blue-500"></span>
        </div>
        <p class="text-2xl font-black text-slate-900 font-mono">{{ agingData.brackets?.['2_3_days'] || 0 }}</p>
        <p class="text-[11px] text-slate-400">Proses wajar</p>
      </div>

      <!-- 4-7 Hari -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">4 – 7 Hari</span>
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
        </div>
        <p class="text-2xl font-black text-slate-900 font-mono">{{ agingData.brackets?.['4_7_days'] || 0 }}</p>
        <p class="text-[11px] text-slate-400">Perhatian verifikator</p>
      </div>

      <!-- 8-14 Hari -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-50 text-orange-700">8 – 14 Hari</span>
          <span class="w-2 h-2 rounded-full bg-orange-500"></span>
        </div>
        <p class="text-2xl font-black text-slate-900 font-mono">{{ agingData.brackets?.['8_14_days'] || 0 }}</p>
        <p class="text-[11px] text-slate-400">Peringatan keterlambatan</p>
      </div>

      <!-- >14 Hari -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">&gt; 14 Hari</span>
          <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
        </div>
        <p class="text-2xl font-black text-rose-600 font-mono">{{ agingData.brackets?.over_14_days || 0 }}</p>
        <p class="text-[11px] text-rose-500 font-medium">Kritis (Overdue)</p>
      </div>
    </div>

    <!-- Recommendations / Action Guidance -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-3">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
        Panduan Tindakan Manajemen Operasional
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600">
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
          <p class="font-bold text-slate-800">1. Prioritas Tunggakan &gt;7 Hari</p>
          <p class="text-[11px] text-slate-500">Lakukan eskalasi otomatis atau hubungi verifikator terkait jika berkas belum diverifikasi lebih dari 7 hari kerja.</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
          <p class="font-bold text-slate-800">2. Revisi Dokumen yang Terhambat</p>
          <p class="text-[11px] text-slate-500">Periksa apakah pemohon klaim telah menerima instruksi revisi kuitansi atau belum melengkapi dokumen pendukung.</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
          <p class="font-bold text-slate-800">3. Jadwal Batch Pembayaran</p>
          <p class="text-[11px] text-slate-500">Pastikan Finance segera memproses batch transfer untuk berkas yang telah mendapatkan persetujuan DBM / BM.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const agingData = ref({});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/aging');
    agingData.value = res.data.data || {};
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
