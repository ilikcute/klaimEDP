<template>
  <div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
          <router-link to="/reports" class="hover:text-indigo-600 transition">Pusat Laporan</router-link>
          <span>/</span>
          <span class="text-indigo-600 font-bold">Rasio BBM</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Laporan Rasio Konsumsi BBM & Kepatuhan
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Audit konsumsi bahan bakar operasional EDP, perbandingan rasio aktual terhadap standar kendaraan, dan audit variansi
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

    <!-- Summary KPI Cards -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total KM Tempuh</p>
        <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ bbmData.summary?.total_km || 0 }} <span class="text-xs font-normal text-slate-400">KM</span></p>
        <p class="text-[11px] text-slate-400 mt-1">Akumulasi jarak odometer</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Liter Bahan Bakar</p>
        <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ bbmData.summary?.total_liter || 0 }} <span class="text-xs font-normal text-slate-400">Liter</span></p>
        <p class="text-[11px] text-slate-400 mt-1">Total volume pembelian bensin</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Konsumsi</p>
        <p class="text-2xl font-black text-indigo-600 mt-1 font-mono">{{ bbmData.summary?.average_km_per_liter || 0 }} <span class="text-xs font-normal text-slate-400">KM/L</span></p>
        <p class="text-[11px] text-slate-400 mt-1">Efisiensi bahan bakar rata-rata</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Tingkat Kepatuhan</p>
        <p class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ bbmData.summary?.compliant_percent || 0 }}%</p>
        <p class="text-[11px] text-slate-400 mt-1">Transaksi sesuai / irit</p>
      </div>
    </div>

    <!-- Filter & Detailed Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
            Rincian Transaksi Klaim BBM & Selisih Variansi terhadap Standar
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">
            Setiap klaim BBM diverifikasi terhadap standar rasio konsumsi (Motor: 40 KM/L, Mobil: 13 KM/L).
          </p>
        </div>

        <div class="relative w-full sm:w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari klaim, requester, atau nopol..."
            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">No. Klaim</th>
              <th class="pb-3">Requester</th>
              <th class="pb-3">Kendaraan</th>
              <th class="pb-3 text-right">Jarak (KM)</th>
              <th class="pb-3 text-right">Volume (L)</th>
              <th class="pb-3 text-right">Rasio Aktual</th>
              <th class="pb-3 text-right">Standar</th>
              <th class="pb-3 text-right">Selisih</th>
              <th class="pb-3 text-center">Status Kepatuhan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="!isLoading && filteredDetails.length === 0">
              <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada data klaim BBM yang cocok.</td>
            </tr>
            <tr v-for="b in filteredDetails" :key="b.claim_number" class="hover:bg-slate-50">
              <td class="py-3.5">
                <router-link :to="`/claims`" class="font-mono font-bold text-indigo-600 hover:underline">
                  {{ b.claim_number }}
                </router-link>
              </td>
              <td class="py-3.5 font-medium text-slate-800">{{ b.requester }}</td>
              <td class="py-3.5 text-slate-600">{{ b.vehicle }}</td>
              <td class="py-3.5 text-right font-mono text-slate-700">{{ b.distance_km }} KM</td>
              <td class="py-3.5 text-right font-mono text-slate-700">{{ b.fuel_liter }} L</td>
              <td class="py-3.5 text-right font-mono font-bold text-slate-900">{{ b.actual_km_per_liter }} KM/L</td>
              <td class="py-3.5 text-right font-mono text-slate-400">{{ b.standard_km_per_liter }} KM/L</td>
              <td class="py-3.5 text-right font-mono font-bold" :class="b.variance_km_per_liter >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                {{ b.variance_km_per_liter > 0 ? '+' : '' }}{{ b.variance_km_per_liter }}
              </td>
              <td class="py-3.5 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="b.compliance_status === 'COMPLIANT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ b.compliance_status === 'COMPLIANT' ? 'Sesuai Standar' : 'Di Bawah Standar' }}
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
const bbmData = ref({});
const searchQuery = ref('');

const filteredDetails = computed(() => {
  const details = bbmData.value.details || [];
  if (!searchQuery.value) return details;
  const q = searchQuery.value.toLowerCase();
  return details.filter(b => 
    b.claim_number?.toLowerCase().includes(q) ||
    b.requester?.toLowerCase().includes(q) ||
    b.vehicle?.toLowerCase().includes(q)
  );
});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/reports/bbm');
    bbmData.value = res.data.data || {};
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
