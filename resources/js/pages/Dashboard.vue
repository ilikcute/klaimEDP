<template>
  <div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-700 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-indigo-700/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-xs font-semibold mb-3 border border-white/15">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          Role: {{ authStore.primaryRole }}
        </div>
        <h2 class="text-xl md:text-2xl font-black tracking-tight">
          Halo, {{ authStore.user?.name }}!
        </h2>
        <p class="text-indigo-100 text-xs md:text-sm mt-1 max-w-xl">
          Selamat datang di platform terpusat Rekap, Pelacakan Workflow, dan Analisa Reimbursement EDP.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <router-link
          to="/claims/create"
          class="px-5 py-2.5 rounded-xl bg-white text-indigo-700 font-bold text-xs shadow-md hover:bg-indigo-50 transition flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          Buat Klaim Baru
        </router-link>
      </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Total Klaim -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Klaim</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900">{{ kpi.total_claims || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Rp {{ formatNumber(kpi.total_amount) }}</p>
      </div>

      <!-- Paid Amount -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Terbayar</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-xl md:text-2xl font-black text-emerald-600">Rp {{ formatNumber(kpi.paid_amount) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Realisasi pembayaran</p>
      </div>

      <!-- Pending Tasks -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Antrean Verifikasi</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-black text-amber-600">{{ kpi.pending_verification || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ kpi.pending_approval || 0 }} butuh approval DBM/BM</p>
      </div>

      <!-- SLA Status -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">SLA Status</span>
          <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-2xl font-black text-slate-900">{{ kpi.sla_breached || 0 }}</span>
          <span class="text-xs text-rose-500 font-bold">Terlewat</span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Rata-rata: {{ kpi.average_processing_days || 0 }} hari</p>
      </div>
    </div>

    <!-- Main Section: 2 Columns on Desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Pending Action Queue -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Tugas Butuh Tindakan Anda
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">Daftar klaim yang menunggu review, verifikasi, atau approval Anda</p>
            </div>
            <router-link to="/tasks" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
              Lihat Semua →
            </router-link>
          </div>

          <div v-if="pendingTasks.length === 0" class="py-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-xs font-bold text-slate-600">Tidak ada antrean tugas saat ini</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Semua klaim pada hak akses Anda telah selesai diproses.</p>
          </div>

          <div v-else class="divide-y divide-slate-100">
            <div
              v-for="task in pendingTasks"
              :key="task.id"
              class="py-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/80 px-2 rounded-xl transition"
            >
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <span class="text-xs font-bold text-slate-900 font-mono">{{ task.claim_number }}</span>
                  <StatusBadge :status="task.current_state" />
                  <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 font-semibold text-slate-600">
                    {{ task.claim_type }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 truncate">{{ task.description || 'Tidak ada keterangan' }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Oleh: {{ task.requester?.name }} • Rp {{ formatNumber(task.total_amount) }}</p>
              </div>

              <router-link
                :to="`/claims/${task.id}`"
                class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 rounded-xl text-xs font-bold transition shrink-0"
              >
                Proses →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Recent Claims Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Klaim Terbaru</h3>
              <p class="text-xs text-slate-400 mt-0.5">Klaim yang baru diajukan atau diproses</p>
            </div>
            <router-link to="/claims" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
              Lihat Rekap →
            </router-link>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                  <th class="pb-3">No. Klaim</th>
                  <th class="pb-3">Requester</th>
                  <th class="pb-3">Jenis</th>
                  <th class="pb-3">Total</th>
                  <th class="pb-3">Status</th>
                  <th class="pb-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="c in recentClaims" :key="c.id" class="hover:bg-slate-50/60 transition">
                  <td class="py-3 font-mono font-bold text-slate-800">{{ c.claim_number }}</td>
                  <td class="py-3 text-slate-600">{{ c.requester?.name }}</td>
                  <td class="py-3 font-semibold">{{ c.claim_type }}</td>
                  <td class="py-3 font-bold text-slate-900">Rp {{ formatNumber(c.total_amount) }}</td>
                  <td class="py-3"><StatusBadge :status="c.current_state" /></td>
                  <td class="py-3 text-right">
                    <router-link :to="`/claims/${c.id}`" class="text-indigo-600 font-bold hover:underline">
                      Detail
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Quick Info & Health / BBM Widgets -->
      <div class="space-y-6">
        <!-- Health Utilization Widget -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center justify-between mb-4">
            <span>Plafon Kesehatan 2026</span>
            <span class="text-emerald-600 text-[11px] font-bold">Limit Rp 10 Jt</span>
          </h3>

          <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center text-slate-600">
              <span>Terpakai (Lunas)</span>
              <span class="font-bold text-slate-800">Rp {{ formatNumber(healthSummary.paid) }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-600">
              <span>Menunggu Pembayaran</span>
              <span class="font-bold text-amber-600">Rp {{ formatNumber(healthSummary.approved) }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-600">
              <span>Sisa Plafon Tersedia</span>
              <span class="font-bold text-emerald-600">Rp {{ formatNumber(healthSummary.remaining) }}</span>
            </div>

            <!-- Progress bar -->
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mt-2">
              <div
                class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-2.5 rounded-full transition-all duration-500"
                :style="`width: ${healthSummary.utilization_percent || 0}%`"
              ></div>
            </div>
            <p class="text-[10px] text-right text-slate-400 font-medium">
              Penggunaan: {{ healthSummary.utilization_percent || 0 }}%
            </p>
          </div>
        </div>

        <!-- Management Approval Rule Callout -->
        <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent rounded-3xl border border-amber-200/70 p-6">
          <div class="flex items-center gap-2 text-amber-800 font-bold text-xs uppercase tracking-wider mb-2">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Kebijakan Approval DBM / BM
          </div>
          <p class="text-xs text-slate-600 leading-relaxed mb-3">
            Sistem menggunakan aturan formal <strong class="text-slate-800">ANY_OF (DBM OR BM)</strong> dengan kuorum minimal 1 persetujuan. Salah satu persetujuan langsung memenuhi gate manajemen tanpa duplikasi.
          </p>
          <div class="flex items-center gap-2 text-[11px] font-bold text-amber-900 bg-amber-100/70 px-3 py-1.5 rounded-xl border border-amber-200">
            <span>Kuorum: 1 Approver</span> • <span>Eligible: DBM & BM</span>
          </div>
        </div>

        <!-- BBM Standard Reference Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
            Standar Rasio Konsumsi BBM
          </h3>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-100">
              <span class="font-medium text-slate-600">Sepeda Motor</span>
              <span class="font-bold text-indigo-600">40.00 KM / Liter</span>
            </div>
            <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-100">
              <span class="font-medium text-slate-600">Mobil Operasional</span>
              <span class="font-bold text-indigo-600">13.00 KM / Liter</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../api/client';
import StatusBadge from '../components/Common/StatusBadge.vue';

const authStore = useAuthStore();

const kpi = ref({});
const recentClaims = ref([]);
const pendingTasks = ref([]);
const healthSummary = ref({});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

async function fetchDashboard() {
  try {
    const res = await api.get('/dashboard');
    kpi.value = res.data.data.kpi || {};
    recentClaims.value = res.data.data.recent_claims || [];
    pendingTasks.value = res.data.data.pending_tasks || [];

    const healthRes = await api.get('/health/summary');
    healthSummary.value = healthRes.data.data || {};
  } catch (e) {
    // ignore
  }
}

onMounted(() => {
  fetchDashboard();
});
</script>
