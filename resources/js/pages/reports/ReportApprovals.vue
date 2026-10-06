<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Persetujuan Manajemen</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Persetujuan Manajemen (DBM / BM)
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Statistik volume dan beban otorisasi manajemen di bawah regulasi persetujuan kuorum ANY_OF (DBM atau BM)
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

    <!-- Management Quorum Banner -->
    <div class="p-6 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl text-white shadow-xl space-y-2">
      <div class="inline-flex px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-indigo-300">
        Kebijakan Kuorum: {{ approvalData.rule || 'ANY_OF (DBM OR BM)' }}
      </div>
      <h3 class="text-lg font-bold">Persetujuan Tunggal Otoritas Manajemen Cabang</h3>
      <p class="text-xs text-slate-300 leading-relaxed max-w-2xl">
        Setiap berkas klaim yang lolos Verifikasi Finance hanya membutuhkan salah satu persetujuan manajemen (baik oleh Deputy Branch Manager atau Branch Manager) dengan kuorum = 1. Setelah disetujui oleh salah satu pihak, status berkas langsung berpindah ke APPROVED dan siap dibayar oleh Finance.
      </p>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-pulse">
      <div v-for="i in 2" :key="i" class="h-44 bg-slate-200/60 rounded-3xl"></div>
    </div>

    <!-- Comparative Side-by-Side Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- DBM Card -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center font-black text-indigo-700 text-sm">
              DBM
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">Deputy Branch Manager</h3>
              <p class="text-[11px] text-slate-400">Wakil Kepala Cabang</p>
            </div>
          </div>
          <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">
            {{ dbmShare }}% Porsi
          </span>
        </div>

        <div class="space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Klaim Disetujui</p>
          <p class="text-3xl font-black text-slate-900 font-mono">
            {{ approvalData.dbm?.approved_count || 0 }} <span class="text-xs text-slate-400 font-normal">Klaim</span>
          </p>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Nominal Disetujui</p>
          <p class="text-xl font-black text-indigo-600 font-mono">
            Rp {{ formatNumber(approvalData.dbm?.total_amount) }}
          </p>
        </div>
      </div>

      <!-- BM Card -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-violet-50 border border-violet-100 flex items-center justify-center font-black text-violet-700 text-sm">
              BM
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">Branch Manager</h3>
              <p class="text-[11px] text-slate-400">Kepala Cabang</p>
            </div>
          </div>
          <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-violet-50 text-violet-700">
            {{ bmShare }}% Porsi
          </span>
        </div>

        <div class="space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Klaim Disetujui</p>
          <p class="text-3xl font-black text-slate-900 font-mono">
            {{ approvalData.bm?.approved_count || 0 }} <span class="text-xs text-slate-400 font-normal">Klaim</span>
          </p>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Nominal Disetujui</p>
          <p class="text-xl font-black text-violet-600 font-mono">
            Rp {{ formatNumber(approvalData.bm?.total_amount) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Workload Distribution Bar -->
    <div v-if="!isLoading" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-3">
      <div class="flex items-center justify-between text-xs font-bold text-slate-800">
        <span>Distribusi Beban Otorisasi (DBM vs BM)</span>
        <span class="text-slate-400 font-normal">{{ totalApprovals }} total persetujuan</span>
      </div>

      <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden flex">
        <div
          class="bg-indigo-600 h-full transition-all duration-500"
          :style="{ width: `${dbmShare}%` }"
          title="Porsi DBM"
        ></div>
        <div
          class="bg-violet-600 h-full transition-all duration-500"
          :style="{ width: `${bmShare}%` }"
          title="Porsi BM"
        ></div>
      </div>

      <div class="flex items-center justify-between text-[11px] text-slate-500">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
          <span>DBM: {{ dbmShare }}%</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span>
          <span>BM: {{ bmShare }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const approvalData = ref({});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

const totalApprovals = computed(() => {
  const dbm = approvalData.value.dbm?.approved_count || 0;
  const bm = approvalData.value.bm?.approved_count || 0;
  return dbm + bm;
});

const dbmShare = computed(() => {
  if (totalApprovals.value === 0) return 50;
  const dbm = approvalData.value.dbm?.approved_count || 0;
  return Math.round((dbm / totalApprovals.value) * 100);
});

const bmShare = computed(() => {
  if (totalApprovals.value === 0) return 50;
  return 100 - dbmShare.value;
});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/management-approval');
    approvalData.value = res.data.data || {};
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
