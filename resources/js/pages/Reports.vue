<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Pusat Laporan & Analisa Klaim EDP
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Monitoring kepatuhan, SLA, tren historis, plafon kesehatan, dan rasio konsumsi BBM
        </p>
      </div>

      <a
        href="/api/reports/claims/export"
        target="_blank"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Ekspor Data (CSV)
      </a>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-slate-200">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition"
        :class="activeTab === tab.key ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab 1: Kategori Klaim (BBM, Tol, Health) -->
    <div v-if="activeTab === 'by-type'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
          v-for="item in byTypeData"
          :key="item.type"
          class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ item.label }}</span>
            <span class="text-xs font-black px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
              {{ item.percentage }}%
            </span>
          </div>
          <p class="text-2xl font-black text-slate-900">Rp {{ formatNumber(item.total_amount) }}</p>
          <div class="pt-2 border-t border-slate-100 flex justify-between text-xs text-slate-500">
            <span>{{ item.count }} Pengajuan</span>
            <span>Rata-rata: Rp {{ formatNumber(item.average_amount) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 2: Tren Historis Bulanan -->
    <div v-if="activeTab === 'trend'" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
        Tren Historis Pengajuan vs Realisasi Pembayaran (6 Bulan Terakhir)
      </h3>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Periode</th>
              <th class="pb-3 text-right">Jumlah Klaim</th>
              <th class="pb-3 text-right">Total Klaim Diajukan</th>
              <th class="pb-3 text-right">Realisasi Dibayar (PAID)</th>
              <th class="pb-3 text-right">Rata-rata per Klaim</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="t in trendData" :key="t.period" class="hover:bg-slate-50">
              <td class="py-3 font-bold text-slate-800">{{ t.period }}</td>
              <td class="py-3 text-right font-mono">{{ t.claim_count }}</td>
              <td class="py-3 text-right font-bold text-slate-900 font-mono">Rp {{ formatNumber(t.claimed_amount) }}</td>
              <td class="py-3 text-right font-bold text-emerald-600 font-mono">Rp {{ formatNumber(t.paid_amount) }}</td>
              <td class="py-3 text-right text-slate-500 font-mono">Rp {{ formatNumber(t.average_claim) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 3: SLA & Analisis Latensi -->
    <div v-if="activeTab === 'sla'" class="space-y-6">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Total Tugas Terproses</p>
          <p class="text-2xl font-black text-slate-900 mt-1">{{ slaData.metrics?.total_tasks || 0 }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-emerald-600">On Time %</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ slaData.metrics?.on_time_percent || 0 }}%</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Rata-rata Waktu Proses</p>
          <p class="text-2xl font-black text-indigo-600 mt-1">{{ slaData.metrics?.average_duration_hours || 0 }} <span class="text-xs text-slate-400 font-normal">Jam</span></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Latency P90 (Tail SLA)</p>
          <p class="text-2xl font-black text-purple-600 mt-1">{{ slaData.metrics?.p90_duration_hours || 0 }} <span class="text-xs text-slate-400 font-normal">Jam</span></p>
        </div>
      </div>

      <!-- Stage breakdown table -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4">
          Analisis Waktu Proses Tiap Tahap (Bottleneck Detection)
        </h3>
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Tahap Workflow</th>
              <th class="pb-3 text-right">Volume</th>
              <th class="pb-3 text-right">Rata-rata (Menit)</th>
              <th class="pb-3 text-right">SLA Terlewat</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="s in slaData.stage_breakdown" :key="s.stage_code" class="hover:bg-slate-50">
              <td class="py-3 font-bold text-slate-800">{{ s.stage_name }}</td>
              <td class="py-3 text-right font-mono">{{ s.total }}</td>
              <td class="py-3 text-right font-mono text-indigo-600 font-bold">{{ Math.round(s.avg_duration || 0) }} mnt</td>
              <td class="py-3 text-right font-mono text-rose-500 font-bold">{{ s.breached_count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 4: BBM Compliance -->
    <div v-if="activeTab === 'bbm'" class="space-y-6">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Total KM Tempuh</p>
          <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ bbmData.summary?.total_km || 0 }} KM</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Total Liter Bensin</p>
          <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ bbmData.summary?.total_liter || 0 }} L</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-slate-400">Rata-rata Konsumsi</p>
          <p class="text-2xl font-black text-indigo-600 mt-1 font-mono">{{ bbmData.summary?.average_km_per_liter || 0 }} KM/L</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
          <p class="text-[10px] font-bold uppercase text-emerald-600">Tingkat Kepatuhan</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ bbmData.summary?.compliant_percent || 0 }}%</p>
        </div>
      </div>

      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs overflow-x-auto">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-4">
          Rincian Transaksi Klaim BBM & Selisih terhadap Standar
        </h3>
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">No. Klaim</th>
              <th class="pb-3">Requester</th>
              <th class="pb-3">Kendaraan</th>
              <th class="pb-3 text-right">Jarak</th>
              <th class="pb-3 text-right">Liter</th>
              <th class="pb-3 text-right">Aktual</th>
              <th class="pb-3 text-right">Standar</th>
              <th class="pb-3 text-right">Selisih</th>
              <th class="pb-3 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="b in bbmData.details" :key="b.claim_number" class="hover:bg-slate-50">
              <td class="py-3 font-mono font-bold text-slate-900">{{ b.claim_number }}</td>
              <td class="py-3 text-slate-600">{{ b.requester }}</td>
              <td class="py-3 text-slate-600">{{ b.vehicle }}</td>
              <td class="py-3 text-right font-mono">{{ b.distance_km }} KM</td>
              <td class="py-3 text-right font-mono">{{ b.fuel_liter }} L</td>
              <td class="py-3 text-right font-mono font-bold text-slate-800">{{ b.actual_km_per_liter }}</td>
              <td class="py-3 text-right font-mono text-slate-400">{{ b.standard_km_per_liter }}</td>
              <td class="py-3 text-right font-mono font-bold" :class="b.variance_km_per_liter >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                {{ b.variance_km_per_liter > 0 ? '+' : '' }}{{ b.variance_km_per_liter }}
              </td>
              <td class="py-3 text-center">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="b.compliance_status === 'COMPLIANT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ b.compliance_status === 'COMPLIANT' ? 'Sesuai' : 'Di Bawah' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 5: Health Plafon Utilization -->
    <div v-if="activeTab === 'health'" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
          Pemanfaatan Plafon Klaim Kesehatan per Karyawan (Tahun 2026)
        </h3>
        <span class="text-xs font-bold text-slate-500">Plafon: Rp 10.000.000 / Tahun</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px]">
              <th class="pb-3">Nama Karyawan</th>
              <th class="pb-3 text-right">Plafon Tahunan</th>
              <th class="pb-3 text-right">Terbayar (Lunas)</th>
              <th class="pb-3 text-right">Disetujui / Pending</th>
              <th class="pb-3 text-right">Sisa Plafon</th>
              <th class="pb-3 text-center">Penggunaan (%)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="u in healthData.users" :key="u.user_id" class="hover:bg-slate-50">
              <td class="py-3">
                <p class="font-bold text-slate-800">{{ u.name }}</p>
                <p class="text-[10px] text-slate-400">{{ u.email }}</p>
              </td>
              <td class="py-3 text-right font-mono">Rp {{ formatNumber(u.annual_limit) }}</td>
              <td class="py-3 text-right font-mono font-bold text-emerald-600">Rp {{ formatNumber(u.paid) }}</td>
              <td class="py-3 text-right font-mono text-amber-600">Rp {{ formatNumber(u.approved + u.pending) }}</td>
              <td class="py-3 text-right font-mono font-extrabold text-indigo-600">Rp {{ formatNumber(u.remaining) }}</td>
              <td class="py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                  {{ u.utilization_percent }}%
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 6: Management Approval Report -->
    <div v-if="activeTab === 'approvals'" class="space-y-6">
      <div class="p-6 bg-gradient-to-r from-indigo-900 to-slate-900 rounded-3xl text-white shadow-xl space-y-2">
        <div class="inline-flex px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-indigo-300">
          Persetujuan Manajemen DBM / BM
        </div>
        <h3 class="text-lg font-bold">Kebijakan Kuorum: {{ approvalData.rule }}</h3>
        <p class="text-xs text-slate-300 leading-relaxed max-w-xl">
          Setiap pengajuan klaim yang lolos Verifikasi Finance hanya membutuhkan salah satu persetujuan manajemen (DBM atau BM) untuk dinyatakan disetujui (APPROVED).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ approvalData.dbm?.role }}</p>
          <p class="text-3xl font-black text-slate-900">{{ approvalData.dbm?.approved_count || 0 }} <span class="text-xs text-slate-400 font-normal">Klaim</span></p>
          <p class="text-xs font-bold text-indigo-600">Total Rp {{ formatNumber(approvalData.dbm?.total_amount) }}</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ approvalData.bm?.role }}</p>
          <p class="text-3xl font-black text-slate-900">{{ approvalData.bm?.approved_count || 0 }} <span class="text-xs text-slate-400 font-normal">Klaim</span></p>
          <p class="text-xs font-bold text-indigo-600">Total Rp {{ formatNumber(approvalData.bm?.total_amount) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../api/client';

const activeTab = ref('by-type');

const tabs = [
  { key: 'by-type', label: '1. Kategori Klaim' },
  { key: 'trend', label: '2. Tren Historis' },
  { key: 'sla', label: '3. SLA & Latensi' },
  { key: 'bbm', label: '4. Rasio BBM' },
  { key: 'health', label: '5. Plafon Kesehatan' },
  { key: 'approvals', label: '6. Approval DBM/BM' },
];

const byTypeData = ref([]);
const trendData = ref([]);
const slaData = ref({});
const bbmData = ref({});
const healthData = ref({});
const approvalData = ref({});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

async function fetchReportData() {
  try {
    const [byType, trend, sla, bbm, health, apprv] = await Promise.all([
      api.get('/reports/by-type'),
      api.get('/reports/historical'),
      api.get('/reports/sla'),
      api.get('/reports/bbm'),
      api.get('/reports/health'),
      api.get('/reports/management-approval'),
    ]);

    byTypeData.value = byType.data.data || [];
    trendData.value = trend.data.data || [];
    slaData.value = sla.data.data || {};
    bbmData.value = bbm.data.data || {};
    healthData.value = health.data.data || {};
    approvalData.value = apprv.data.data || {};
  } catch (e) {
    // ignore
  }
}

onMounted(() => {
  fetchReportData();
});
</script>
