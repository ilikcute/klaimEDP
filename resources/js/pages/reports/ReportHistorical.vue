<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Tren Historis</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Tren Historis & Realisasi Pembayaran
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Perbandingan tren bulanan antara total nominal pengajuan klaim vs realisasi pencairan (PAID)
        </p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <!-- Months selector -->
        <select
          v-model="selectedMonths"
          @change="fetchData"
          class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-2xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option :value="3">3 Bulan Terakhir</option>
          <option :value="6">6 Bulan Terakhir</option>
          <option :value="12">12 Bulan Terakhir</option>
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

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-pulse">
      <div v-for="i in 3" :key="i" class="h-28 bg-slate-200/60 rounded-3xl"></div>
    </div>

    <!-- Summary KPI Cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Klaim Diajukan</p>
        <p class="text-2xl font-black text-slate-900 font-mono">Rp {{ formatNumber(totalClaimed) }}</p>
        <p class="text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">{{ totalCount }} total transaksi berkas</p>
      </div>

      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Realisasi Dibayar (PAID)</p>
        <p class="text-2xl font-black text-emerald-600 font-mono">Rp {{ formatNumber(totalPaid) }}</p>
        <p class="text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">Rasio cair: <strong class="text-emerald-700">{{ payoutRatio }}%</strong> dari pengajuan</p>
      </div>

      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Rata-rata per Klaim</p>
        <p class="text-2xl font-black text-indigo-600 font-mono">Rp {{ formatNumber(overallAverage) }}</p>
        <p class="text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">Berdasarkan data {{ selectedMonths }} bulan</p>
      </div>
    </div>

    <!-- Visual Bars Comparison per Period -->
    <div v-if="!isLoading && trendData.length > 0" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-5">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
        Grafik Komparasi Bulanan: Diajukan vs Dibayar
      </h3>

      <div class="space-y-4">
        <div v-for="t in trendData" :key="t.period" class="space-y-1.5">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-800">{{ t.period }}</span>
            <div class="flex items-center gap-4 text-[11px] font-mono">
              <span class="text-slate-600">Klaim: <strong>Rp {{ formatNumber(t.claimed_amount) }}</strong></span>
              <span class="text-emerald-600 font-bold">Cair: <strong>Rp {{ formatNumber(t.paid_amount) }}</strong></span>
            </div>
          </div>
          <!-- Bar stacks -->
          <div class="space-y-1">
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden flex">
              <div
                class="bg-indigo-500 h-full rounded-full transition-all duration-500"
                :style="{ width: `${getBarWidth(t.claimed_amount)}%` }"
                title="Nomor Pengajuan"
              ></div>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden flex">
              <div
                class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                :style="{ width: `${getBarWidth(t.paid_amount)}%` }"
                title="Nomor Pencairan"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-6 pt-3 border-t border-slate-100 text-xs text-slate-500">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-sm bg-indigo-500"></span>
          <span>Nominal Pengajuan (Claimed)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-sm bg-emerald-500"></span>
          <span>Realisasi Dicairkan (Paid)</span>
        </div>
      </div>
    </div>

    <!-- Historical Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
          Rincian Data Tabel Tren Historis
        </h3>
        <span class="text-xs text-slate-400 font-medium">Periode aktif: {{ selectedMonths }} bulan</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Periode Bulan</th>
              <th class="pb-3 text-right">Volume Berkas</th>
              <th class="pb-3 text-right">Total Klaim Diajukan</th>
              <th class="pb-3 text-right">Realisasi Dibayar (PAID)</th>
              <th class="pb-3 text-right">Rata-rata per Klaim</th>
              <th class="pb-3 text-right">Realisasi (%)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="!isLoading && trendData.length === 0">
              <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada riwayat transaksi pada rentang waktu ini.</td>
            </tr>
            <tr v-for="t in trendData" :key="t.period" class="hover:bg-slate-50">
              <td class="py-3 font-bold text-slate-800">{{ t.period }}</td>
              <td class="py-3 text-right font-mono text-slate-700">{{ t.claim_count }}</td>
              <td class="py-3 text-right font-mono font-bold text-slate-900">Rp {{ formatNumber(t.claimed_amount) }}</td>
              <td class="py-3 text-right font-mono font-bold text-emerald-600">Rp {{ formatNumber(t.paid_amount) }}</td>
              <td class="py-3 text-right font-mono text-slate-500">Rp {{ formatNumber(t.average_claim) }}</td>
              <td class="py-3 text-right font-mono font-bold" :class="t.claimed_amount > 0 && (t.paid_amount / t.claimed_amount) >= 0.8 ? 'text-emerald-600' : 'text-slate-600'">
                {{ t.claimed_amount > 0 ? Math.round((t.paid_amount / t.claimed_amount) * 100) : 0 }}%
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
const selectedMonths = ref(6);
const trendData = ref([]);

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

const totalClaimed = computed(() => {
  return trendData.value.reduce((acc, curr) => acc + (parseFloat(curr.claimed_amount) || 0), 0);
});

const totalPaid = computed(() => {
  return trendData.value.reduce((acc, curr) => acc + (parseFloat(curr.paid_amount) || 0), 0);
});

const totalCount = computed(() => {
  return trendData.value.reduce((acc, curr) => acc + (parseInt(curr.claim_count) || 0), 0);
});

const overallAverage = computed(() => {
  return totalCount.value > 0 ? Math.round(totalClaimed.value / totalCount.value) : 0;
});

const payoutRatio = computed(() => {
  return totalClaimed.value > 0 ? Math.round((totalPaid.value / totalClaimed.value) * 100) : 0;
});

const maxAmount = computed(() => {
  let max = 0;
  for (const t of trendData.value) {
    if (t.claimed_amount > max) max = t.claimed_amount;
    if (t.paid_amount > max) max = t.paid_amount;
  }
  return max > 0 ? max : 1;
});

function getBarWidth(amount) {
  if (!amount || maxAmount.value === 0) return 0;
  return Math.min(100, Math.round((amount / maxAmount.value) * 100));
}

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get(`/reports/historical?months=${selectedMonths.value}`);
    trendData.value = res.data.data || [];
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
