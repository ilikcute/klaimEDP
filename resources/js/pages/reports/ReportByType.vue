<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Kategori Klaim</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Rekap Kategori Klaim
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Distribusi pengajuan dan realisasi anggaran berdasarkan jenis klaim (BBM, Tol, Kesehatan)
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

        <a
          href="/api/reports/claims/export"
          target="_blank"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Ekspor CSV
        </a>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-4 animate-pulse">
      <div v-for="i in 3" :key="i" class="h-36 bg-slate-200/60 rounded-3xl"></div>
    </div>

    <!-- Summary Category Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="item in byTypeData"
        :key="item.type"
        class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4 hover:border-slate-300 transition"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm" :class="getTypeBadgeClass(item.type)">
              {{ getTypeIcon(item.type) }}
            </span>
            <div>
              <p class="text-xs font-bold text-slate-800">{{ item.label }}</p>
              <p class="text-[10px] text-slate-400 font-mono">{{ item.type }}</p>
            </div>
          </div>
          <span class="text-xs font-black px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-mono">
            {{ item.percentage }}%
          </span>
        </div>

        <div>
          <p class="text-[11px] text-slate-400 font-medium">Total Nominal Terkumpul</p>
          <p class="text-2xl font-black text-slate-900 font-mono mt-0.5">
            Rp {{ formatNumber(item.total_amount) }}
          </p>
        </div>

        <!-- Progress Proportion Bar -->
        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="getProgressBarClass(item.type)"
            :style="{ width: `${Math.max(item.percentage, 4)}%` }"
          ></div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
          <span><strong>{{ item.count }}</strong> Pengajuan</span>
          <span>Rata-rata: <strong class="font-mono text-slate-800">Rp {{ formatNumber(item.average_amount) }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
          Tabel Komparasi Pengeluaran per Kategori
        </h3>
        <span class="text-xs text-slate-400 font-medium">Berdasarkan klaim aktif & disetujui</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Kategori Klaim</th>
              <th class="pb-3 text-right">Volume Transaksi</th>
              <th class="pb-3 text-right">Total Akumulasi</th>
              <th class="pb-3 text-right">Rata-rata Nominal</th>
              <th class="pb-3 text-right">Porsi Anggaran (%)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="!isLoading && byTypeData.length === 0">
              <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data klaim terdaftar.</td>
            </tr>
            <tr v-for="item in byTypeData" :key="item.type" class="hover:bg-slate-50">
              <td class="py-3.5 font-bold text-slate-800 flex items-center gap-2">
                <span>{{ getTypeIcon(item.type) }}</span>
                <span>{{ item.label }}</span>
              </td>
              <td class="py-3.5 text-right font-mono text-slate-700">{{ item.count }} Klaim</td>
              <td class="py-3.5 text-right font-mono font-bold text-slate-900">Rp {{ formatNumber(item.total_amount) }}</td>
              <td class="py-3.5 text-right font-mono text-slate-600">Rp {{ formatNumber(item.average_amount) }}</td>
              <td class="py-3.5 text-right font-mono font-black text-indigo-600">{{ item.percentage }}%</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="border-t-2 border-slate-100 font-bold text-slate-900 bg-slate-50/50">
              <td class="py-3.5">TOTAL KESELURUHAN</td>
              <td class="py-3.5 text-right font-mono">{{ totalVolume }} Klaim</td>
              <td class="py-3.5 text-right font-mono text-indigo-700">Rp {{ formatNumber(grandTotal) }}</td>
              <td class="py-3.5 text-right font-mono text-slate-500">-</td>
              <td class="py-3.5 text-right font-mono">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const byTypeData = ref([]);

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

function getTypeIcon(type) {
  if (type === 'BBM') return '⛽';
  if (type === 'TOL') return '🛣️';
  if (type === 'HEALTH') return '🏥';
  return '📄';
}

function getTypeBadgeClass(type) {
  if (type === 'BBM') return 'bg-amber-100 text-amber-700';
  if (type === 'TOL') return 'bg-blue-100 text-blue-700';
  if (type === 'HEALTH') return 'bg-teal-100 text-teal-700';
  return 'bg-slate-100 text-slate-700';
}

function getProgressBarClass(type) {
  if (type === 'BBM') return 'bg-amber-500';
  if (type === 'TOL') return 'bg-blue-500';
  if (type === 'HEALTH') return 'bg-teal-500';
  return 'bg-indigo-600';
}

const grandTotal = computed(() => {
  return byTypeData.value.reduce((acc, curr) => acc + (parseFloat(curr.total_amount) || 0), 0);
});

const totalVolume = computed(() => {
  return byTypeData.value.reduce((acc, curr) => acc + (parseInt(curr.count) || 0), 0);
});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/by-type');
    byTypeData.value = res.data.data || [];
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
