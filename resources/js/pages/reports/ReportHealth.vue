<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Plafon Kesehatan</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Pemanfaatan Plafon Kesehatan Karyawan
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Monitoring kuota plafon tahunan rawat jalan/kesehatan (Rp 10.000.000/tahun per karyawan) dan mitigasi batas limit
        </p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <select
          v-model="selectedYear"
          @change="fetchData"
          class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-2xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option :value="2026">Tahun 2026 (Aktif)</option>
          <option :value="2025">Tahun 2025</option>
          <option :value="2024">Tahun 2024</option>
        </select>

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

    <!-- Policy Banner -->
    <div class="p-5 rounded-3xl bg-teal-900 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="space-y-1">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-teal-800 text-teal-200 text-[10px] font-bold tracking-wider uppercase">
          Kebijakan Plafon Medis {{ selectedYear }}
        </div>
        <h3 class="text-base font-bold">Plafon Kesehatan Standar EDP: Rp {{ formatNumber(healthData.annual_limit || 10000000) }} / Tahun</h3>
        <p class="text-xs text-teal-200/80 leading-relaxed max-w-xl">
          Total klaim yang diajukan tidak dapat melebihi sisa plafon tahun berjalan. Sistem memantau realisasi lunas serta klaim yang sedang dalam antrean verifikasi.
        </p>
      </div>

      <div class="bg-teal-800/80 p-4 rounded-2xl border border-teal-700 text-center shrink-0">
        <p class="text-[10px] uppercase font-bold text-teal-300">Karyawan Mengajukan</p>
        <p class="text-2xl font-black text-white font-mono mt-0.5">{{ usersList.length }} Karyawan</p>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="h-64 bg-slate-200/60 rounded-3xl animate-pulse"></div>

    <!-- Utilization Table -->
    <div v-else class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
          Rincian Pemanfaatan Plafon per Karyawan (Tahun {{ selectedYear }})
        </h3>
        <span class="text-xs text-slate-400 font-medium">Berdasarkan nomor identitas & riwayat klaim</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Karyawan</th>
              <th class="pb-3 text-right">Plafon Tahunan</th>
              <th class="pb-3 text-right">Terbayar (Lunas)</th>
              <th class="pb-3 text-right">Pending / Disetujui</th>
              <th class="pb-3 text-right">Sisa Plafon</th>
              <th class="pb-3 text-center">Penggunaan</th>
              <th class="pb-3 text-center">Status Limit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="usersList.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400">Belum ada pemanfaatan klaim kesehatan pada tahun {{ selectedYear }}.</td>
            </tr>
            <tr v-for="u in usersList" :key="u.user_id" class="hover:bg-slate-50">
              <td class="py-3.5">
                <p class="font-bold text-slate-800">{{ u.name }}</p>
                <p class="text-[10px] text-slate-400 font-mono">{{ u.email }}</p>
              </td>
              <td class="py-3.5 text-right font-mono text-slate-600">Rp {{ formatNumber(u.annual_limit) }}</td>
              <td class="py-3.5 text-right font-mono font-bold text-emerald-600">Rp {{ formatNumber(u.paid) }}</td>
              <td class="py-3.5 text-right font-mono text-amber-600">Rp {{ formatNumber(u.approved + u.pending) }}</td>
              <td class="py-3.5 text-right font-mono font-extrabold text-indigo-600">Rp {{ formatNumber(u.remaining) }}</td>
              <td class="py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-16 bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :class="u.utilization_percent >= 90 ? 'bg-rose-500' : (u.utilization_percent >= 75 ? 'bg-amber-500' : 'bg-teal-500')"
                      :style="{ width: `${Math.min(u.utilization_percent, 100)}%` }"
                    ></div>
                  </div>
                  <span class="text-[11px] font-bold font-mono text-slate-700">{{ u.utilization_percent }}%</span>
                </div>
              </td>
              <td class="py-3.5 text-center">
                <span
                  v-if="u.utilization_percent >= 90"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                >
                  Limit Kritis
                </span>
                <span
                  v-else-if="u.utilization_percent >= 75"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                >
                  Perhatian
                </span>
                <span
                  v-else
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200"
                >
                  Aman
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
import { computed, onMounted, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const selectedYear = ref(2026);
const healthData = ref({});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

const usersList = computed(() => {
  return healthData.value.users || [];
});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get(`/reports/health?year=${selectedYear.value}`);
    healthData.value = res.data.data || {};
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
