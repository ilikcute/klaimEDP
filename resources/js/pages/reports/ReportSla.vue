<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">SLA & Latensi</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan SLA & Analisis Latensi Workflow
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Monitoring kepatuhan target durasi layanan, deteksi bottleneck per tahapan, dan kalkulasi P90 tail latency
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

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 animate-pulse">
      <div v-for="i in 4" :key="i" class="h-24 bg-slate-200/60 rounded-2xl"></div>
    </div>

    <!-- SLA KPI Metrics Grid -->
    <div v-else class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tugas Terproses</p>
          <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ slaData.metrics?.total_tasks || 0 }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Siklus SLA instance</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">On Time %</p>
          <p class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ slaData.metrics?.on_time_percent || 0 }}%</p>
          <p class="text-[11px] text-slate-400 mt-1">Tepat sebelum batas waktu</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-amber-500">At Risk %</p>
          <p class="text-2xl font-black text-amber-500 mt-1 font-mono">{{ slaData.metrics?.at_risk_percent || 0 }}%</p>
          <p class="text-[11px] text-slate-400 mt-1">Sisa durasi &le; 25%</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Breached %</p>
          <p class="text-2xl font-black text-rose-600 mt-1 font-mono">{{ slaData.metrics?.breached_percent || 0 }}%</p>
          <p class="text-[11px] text-slate-400 mt-1">Melebihi target SLA</p>
        </div>
      </div>

      <!-- Latency Distribution Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Durasi (Mean)</p>
          <p class="text-2xl font-black text-indigo-600 mt-1 font-mono">
            {{ slaData.metrics?.average_duration_hours || 0 }} <span class="text-xs text-slate-400 font-normal">Jam</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-1">Durasi keseluruhan dibagi jumlah tugas</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Median (P50)</p>
          <p class="text-2xl font-black text-slate-800 mt-1 font-mono">
            {{ slaData.metrics?.median_duration_hours || 0 }} <span class="text-xs text-slate-400 font-normal">Jam</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-1">Nilai tengah kecepatan pemrosesan</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <div class="flex items-center justify-between">
            <p class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Tail Latency (P90)</p>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">Audit Metric</span>
          </div>
          <p class="text-2xl font-black text-purple-700 mt-1 font-mono">
            {{ slaData.metrics?.p90_duration_hours || 0 }} <span class="text-xs text-slate-400 font-normal">Jam</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-1">90% tugas diselesaikan dalam batas ini</p>
        </div>
      </div>
    </div>

    <!-- Bottleneck Detection per Workflow Stage -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
            Analisis Durasi per Tahapan Workflow (Bottleneck Detection)
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">
            Mendeteksi tahap verifikasi atau persetujuan yang memakan waktu terlama dan memiliki rasio keterlambatan tertinggi.
          </p>
        </div>
        <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-xl self-start sm:self-auto">
          Workflow V1
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Tahap Workflow</th>
              <th class="pb-3 text-right">Volume Klaim</th>
              <th class="pb-3 text-right">Rata-rata Waktu</th>
              <th class="pb-3 text-right">SLA Terlewat (Breached)</th>
              <th class="pb-3 text-center">Status Bottleneck</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="!isLoading && (!slaData.stage_breakdown || slaData.stage_breakdown.length === 0)">
              <td colspan="5" class="py-8 text-center text-slate-400">Belum ada riwayat tahap workflow tercatat.</td>
            </tr>
            <tr v-for="s in slaData.stage_breakdown" :key="s.stage_code" class="hover:bg-slate-50">
              <td class="py-3.5">
                <p class="font-bold text-slate-800">{{ s.stage_name }}</p>
                <p class="text-[10px] text-slate-400 font-mono">{{ s.stage_code }}</p>
              </td>
              <td class="py-3.5 text-right font-mono text-slate-700">{{ s.total }} tugas</td>
              <td class="py-3.5 text-right font-mono font-bold text-indigo-600">
                {{ formatDuration(s.avg_duration) }}
              </td>
              <td class="py-3.5 text-right font-mono font-bold" :class="s.breached_count > 0 ? 'text-rose-600' : 'text-slate-400'">
                {{ s.breached_count }}
              </td>
              <td class="py-3.5 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                  :class="s.breached_count > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                >
                  {{ s.breached_count > 0 ? 'Potensi Kendala' : 'Lancar' }}
                </span>
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
const slaData = ref({});

function formatDuration(minutes) {
  if (!minutes) return '0 mnt';
  const m = Math.round(minutes);
  if (m >= 60) {
    const hours = (m / 60).toFixed(1);
    return `${hours} jam (${m} mnt)`;
  }
  return `${m} mnt`;
}

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/sla');
    slaData.value = res.data.data || {};
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
