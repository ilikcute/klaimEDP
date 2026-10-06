<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Daftar Klaim Reimbursement
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Rekap seluruh pengajuan klaim operasional, BBM, tol, dan kesehatan
        </p>
      </div>

      <router-link
        to="/claims/create"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Buat Klaim Baru
      </router-link>
    </div>

    <!-- Quick Status / Role Info & Tab Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <button
          @click="setTab('ALL')"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
          :class="activeTab === 'ALL' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          Semua Klaim
        </button>
        <button
          @click="setTab('PENDING')"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5"
          :class="activeTab === 'PENDING' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/60'"
        >
          <span>⚡ Perlu Tindakan Saya</span>
        </button>
        <button
          @click="setTab('COMPLETED')"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
          :class="activeTab === 'COMPLETED' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          Selesai (Paid)
        </button>
      </div>

      <div class="text-[11px] text-slate-500 flex items-center gap-1.5 self-end sm:self-auto">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Login: <strong class="text-slate-800">{{ authStore.user?.name }}</strong> ({{ authStore.primaryRole }})</span>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Pencarian</label>
          <div class="relative">
            <input
              v-model="filters.search"
              @input="debouncedFetch"
              type="text"
              placeholder="No. klaim / nama / keterangan..."
              class="w-full text-xs rounded-xl border border-slate-200 pl-8 pr-3 py-2 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
            />
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Claim Type Filter -->
        <div>
          <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Jenis Klaim</label>
          <select
            v-model="filters.claim_type"
            @change="fetchClaims"
            class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
          >
            <option value="">Semua Jenis (BBM, Tol, Health)</option>
            <option value="BBM">BBM (Bahan Bakar Minyak)</option>
            <option value="TOL">Tol & Parkir</option>
            <option value="HEALTH">Kesehatan</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Workflow</label>
          <select
            v-model="filters.status"
            @change="fetchClaims"
            class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
          >
            <option value="">Semua Status</option>
            <option value="DRAFT">Draft</option>
            <option value="SUBMITTED">Diajukan (Submitted)</option>
            <option value="EDP_REVIEW">Review EDP Adm</option>
            <option value="SUPERVISOR_VERIFICATION">Verifikasi SPV</option>
            <option value="MANAGER_VERIFICATION">Verifikasi Manager</option>
            <option value="FINANCE_VERIFICATION">Verifikasi Finance</option>
            <option value="MANAGEMENT_APPROVAL">Approval DBM/BM</option>
            <option value="APPROVED">Disetujui (Approved)</option>
            <option value="PAYMENT_PROCESS">Proses Pembayaran</option>
            <option value="PAID">Lunas (Paid)</option>
            <option value="NEED_REVISION">Perlu Revisi</option>
            <option value="REJECTED">Ditolak</option>
          </select>
        </div>

        <!-- Reset Button -->
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
          >
            Reset Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Desktop Data Table -->
    <div class="hidden md:block bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
          <tr>
            <th @click="toggleSort('claim_number')" class="py-3.5 px-5 cursor-pointer hover:text-indigo-600 transition select-none">
              <div class="flex items-center gap-1.5">
                <span>No. Klaim</span>
                <span class="text-indigo-600 font-extrabold text-[10px]">
                  {{ filters.sort_by === 'claim_number' && filters.sort_order === 'asc' ? '▲ (Asc)' : (filters.sort_by === 'claim_number' ? '▼ (Desc)' : '↕') }}
                </span>
              </div>
            </th>
            <th class="py-3.5 px-4">Tanggal</th>
            <th class="py-3.5 px-4">Requester</th>
            <th class="py-3.5 px-4">Jenis</th>
            <th class="py-3.5 px-4">Detail BBM (KM & Liter)</th>
            <th @click="toggleSort('total_amount')" class="py-3.5 px-4 cursor-pointer hover:text-indigo-600 transition select-none">
              <div class="flex items-center gap-1.5">
                <span>Total Rupiah</span>
                <span v-if="filters.sort_by === 'total_amount'" class="text-indigo-600 font-extrabold text-[10px]">
                  {{ filters.sort_order === 'asc' ? '▲' : '▼' }}
                </span>
              </div>
            </th>
            <th class="py-3.5 px-4">Status Workflow</th>
            <th class="py-3.5 px-6 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading">
            <td colspan="8" class="py-12 text-center text-slate-400">
              <div class="inline-flex items-center gap-2 font-medium">
                <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Memuat data klaim...
              </div>
            </td>
          </tr>

          <tr v-else-if="claims.length === 0">
            <td colspan="8" class="py-12 text-center text-slate-400">
              Tidak ada data klaim yang sesuai kriteria pencarian.
            </td>
          </tr>

          <tr
            v-for="c in claims"
            :key="c.id"
            class="hover:bg-slate-50/70 transition"
            :class="isPendingAction(c) ? 'bg-amber-50/30' : ''"
          >
            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
              <span>{{ c.claim_number }}</span>
              <div v-if="isPendingAction(c)" class="mt-0.5">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300">
                  ⚡ Perlu Tindakan
                </span>
              </div>
            </td>
            <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
              {{ formatDate(c.claim_date) }}
            </td>
            <td class="py-3.5 px-4">
              <p class="font-semibold text-slate-800">{{ c.requester?.name }}</p>
              <p v-if="c.requester?.wilayah" class="text-[10px] text-slate-400 truncate max-w-[130px]">
                {{ c.requester.wilayah }}
              </p>
            </td>
            <td class="py-3.5 px-4">
              <span
                class="inline-flex px-2 py-0.5 rounded font-bold text-[10px]"
                :class="c.claim_type === 'BBM' ? 'bg-blue-50 text-blue-700 border border-blue-200' : (c.claim_type === 'TOL' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
              >
                {{ c.claim_type }}
              </span>
            </td>

            <!-- Detail BBM: KM Klaim, Liter Bensin, Rasio & Odometer -->
            <td class="py-3.5 px-4">
              <div v-if="c.claim_type === 'BBM' && (c.bbm_detail || c.bbmDetail)" class="space-y-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-bold font-mono text-[11px] border border-emerald-200" title="Jarak Tempuh (KM Klaim)">
                    🛣️ {{ (c.bbm_detail || c.bbmDetail).distance_km }} KM
                  </span>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-800 font-bold font-mono text-[11px] border border-indigo-200" title="Bensin Sesuai Nota">
                    ⛽ {{ (c.bbm_detail || c.bbmDetail).fuel_liter }} Liter
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                  <span>Rasio: <strong :class="Number((c.bbm_detail || c.bbmDetail).variance_km_per_liter) >= 0 ? 'text-emerald-700 font-bold' : 'text-amber-700 font-bold'">{{ (c.bbm_detail || c.bbmDetail).actual_km_per_liter }} KM/L</strong></span>
                  <span v-if="(c.bbm_detail || c.bbmDetail).last_odometer_km != null && (c.bbm_detail || c.bbmDetail).current_odometer_km != null" class="text-slate-400">
                    ({{ (c.bbm_detail || c.bbmDetail).last_odometer_km }}→{{ (c.bbm_detail || c.bbmDetail).current_odometer_km }})
                  </span>
                </div>
              </div>
              <div v-else-if="c.claim_type === 'BBM'" class="text-slate-400 text-[11px] italic">
                -
              </div>
              <div v-else class="text-slate-400 text-[11px] truncate max-w-[150px]">
                {{ c.description || '-' }}
              </div>
            </td>

            <!-- Rupiah Total -->
            <td class="py-3.5 px-4 font-black text-slate-900 font-mono text-xs whitespace-nowrap">
              Rp {{ formatNumber(c.total_amount) }}
            </td>

            <!-- Status Workflow -->
            <td class="py-3.5 px-4">
              <StatusBadge :status="c.current_state" />
              <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[130px]">
                {{ getNextActionText(c.current_state) }}
              </p>
            </td>

            <!-- Aksi -->
            <td class="py-3.5 px-6 text-right whitespace-nowrap">
              <div class="inline-flex items-center gap-1.5">
                <router-link
                  :to="`/claims/${c.id}/print`"
                  target="_blank"
                  class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 border border-slate-200/80 transition inline-flex items-center shadow-2xs"
                  title="Cetak Form Rekap & Lampiran Nota"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                </router-link>

                <router-link
                  :to="`/claims/${c.id}`"
                  class="px-3 py-1.5 rounded-xl font-bold transition inline-flex items-center gap-1.5 text-xs shadow-xs"
                  :class="isPendingAction(c) ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                >
                  <span v-if="isPendingAction(c)">⚡ Periksa & Putuskan →</span>
                  <span v-else>Lihat Detail →</span>
                </router-link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Desktop Pagination -->
      <div v-if="pagination.total > 0" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <div>
          Menampilkan <strong class="text-slate-800">{{ claims.length }}</strong> dari <strong class="text-slate-800">{{ pagination.total }}</strong> klaim
        </div>
        <div class="flex items-center gap-2">
          <button
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 transition font-medium"
          >
            ← Sebelumnya
          </button>
          <span class="font-bold text-slate-700">
            Hal {{ pagination.current_page }} dari {{ pagination.last_page }}
          </span>
          <button
            :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 transition font-medium"
          >
            Selanjutnya →
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Card List View -->
    <div class="md:hidden space-y-3">
      <div v-if="loading" class="p-8 text-center text-xs text-slate-400 bg-white rounded-2xl border">
        Memuat data klaim...
      </div>

      <div v-else-if="claims.length === 0" class="p-8 text-center text-xs text-slate-400 bg-white rounded-2xl border">
        Tidak ada data klaim.
      </div>

      <div
        v-for="c in claims"
        :key="c.id"
        class="p-4 rounded-2xl bg-white border shadow-xs hover:border-indigo-300 transition space-y-3"
        :class="isPendingAction(c) ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80'"
      >
        <div class="flex items-center justify-between">
          <div>
            <span class="font-mono font-bold text-xs text-slate-900">{{ c.claim_number }}</span>
            <span v-if="isPendingAction(c)" class="ml-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-amber-100 text-amber-800">
              ⚡ Perlu Aksi
            </span>
          </div>
          <StatusBadge :status="c.current_state" />
        </div>

        <div class="flex items-center justify-between text-xs">
          <div>
            <p class="font-semibold text-slate-800">{{ c.requester?.name }}</p>
            <p class="text-[10px] text-slate-400">{{ formatDate(c.claim_date) }} • {{ c.claim_type }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400">Total Rupiah</p>
            <p class="font-black text-slate-900 font-mono text-sm">Rp {{ formatNumber(c.total_amount) }}</p>
          </div>
        </div>

        <!-- Rincian BBM Khusus jika jenis BBM -->
        <div v-if="c.claim_type === 'BBM' && (c.bbm_detail || c.bbmDetail)" class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs font-mono">
          <div class="flex items-center gap-2">
            <span class="font-bold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded-lg text-[11px]">
              🛣️ {{ (c.bbm_detail || c.bbmDetail).distance_km }} KM
            </span>
            <span class="font-bold text-indigo-800 bg-indigo-100/70 px-2 py-0.5 rounded-lg text-[11px]">
              ⛽ {{ (c.bbm_detail || c.bbmDetail).fuel_liter }} Liter
            </span>
          </div>
          <span class="text-[11px] font-bold text-slate-600">
            {{ (c.bbm_detail || c.bbmDetail).actual_km_per_liter }} KM/L
          </span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-100">
          <span>{{ getNextActionText(c.current_state) }}</span>
          <div class="flex items-center gap-1.5">
            <router-link
              :to="`/claims/${c.id}/print`"
              target="_blank"
              class="p-1.5 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 border border-slate-200/80 transition inline-flex items-center"
              title="Cetak Form Rekap"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </router-link>
            <router-link
              :to="`/claims/${c.id}`"
              class="px-3 py-1.5 rounded-xl font-bold transition inline-flex items-center gap-1 text-xs"
              :class="isPendingAction(c) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700'"
            >
              {{ isPendingAction(c) ? '⚡ Periksa & Putuskan →' : 'Detail →' }}
            </router-link>
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
const claims = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const activeTab = ref('ALL');

const filters = reactive({
  search: '',
  claim_type: '',
  status: '',
  sort_by: 'claim_number',
  sort_order: 'asc',
  page: 1,
});

function toggleSort(column) {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.sort_by = column;
    filters.sort_order = 'asc';
  }
  filters.page = 1;
  fetchClaims();
}

function isPendingAction(c) {
  const role = authStore.primaryRole;
  if (authStore.hasRole('ADMIN')) {
    return !['PAID', 'REJECTED', 'CANCELLED', 'DRAFT'].includes(c.current_state);
  }
  if (role === 'EDP_ADM') {
    return ['SUBMITTED', 'EDP_REVIEW'].includes(c.current_state);
  }
  if (role === 'EDP_SUPERVISOR') {
    return c.current_state === 'SUPERVISOR_VERIFICATION';
  }
  if (role === 'EDP_MANAGER') {
    return c.current_state === 'MANAGER_VERIFICATION';
  }
  if (role === 'FINANCE') {
    return ['FINANCE_VERIFICATION', 'APPROVED', 'PAYMENT_PROCESS'].includes(c.current_state);
  }
  if (['DBM', 'BM'].includes(role)) {
    return c.current_state === 'MANAGEMENT_APPROVAL';
  }
  if (role === 'EDP_OPERASIONAL') {
    return c.current_state === 'NEED_REVISION' && c.requester_id === authStore.user?.id;
  }
  return false;
}

function getNextActionText(state) {
  switch (state) {
    case 'DRAFT': return 'Draft pemohon';
    case 'SUBMITTED': return 'Menunggu review Adm EDP';
    case 'EDP_REVIEW': return 'Sedang direview Adm EDP';
    case 'SUPERVISOR_VERIFICATION': return 'Menunggu verifikasi SPV';
    case 'MANAGER_VERIFICATION': return 'Menunggu verifikasi Manager';
    case 'FINANCE_VERIFICATION': return 'Menunggu verifikasi Finance';
    case 'MANAGEMENT_APPROVAL': return 'Menunggu approval DBM/BM';
    case 'APPROVED': return 'Disetujui, siap bayar';
    case 'PAYMENT_PROCESS': return 'Sedang transfer pembayaran';
    case 'PAID': return 'Lunas ditransfer';
    case 'NEED_REVISION': return 'Menunggu perbaikan requester';
    case 'REJECTED': return 'Klaim ditolak';
    default: return state;
  }
}

function setTab(tab) {
  activeTab.value = tab;
  if (tab === 'PENDING') {
    const role = authStore.primaryRole;
    if (role === 'EDP_ADM') filters.status = 'SUBMITTED';
    else if (role === 'EDP_SUPERVISOR') filters.status = 'SUPERVISOR_VERIFICATION';
    else if (role === 'EDP_MANAGER') filters.status = 'MANAGER_VERIFICATION';
    else if (role === 'FINANCE') filters.status = 'FINANCE_VERIFICATION';
    else if (['DBM', 'BM'].includes(role)) filters.status = 'MANAGEMENT_APPROVAL';
    else if (role === 'EDP_OPERASIONAL') filters.status = 'NEED_REVISION';
    else filters.status = '';
  } else if (tab === 'COMPLETED') {
    filters.status = 'PAID';
  } else {
    filters.status = '';
  }
  filters.page = 1;
  fetchClaims();
}

let debounceTimer = null;
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    filters.page = 1;
    fetchClaims();
  }, 300);
}

function resetFilters() {
  filters.search = '';
  filters.claim_type = '';
  filters.status = '';
  filters.sort_by = 'claim_number';
  filters.sort_order = 'asc';
  filters.page = 1;
  activeTab.value = 'ALL';
  fetchClaims();
}

function changePage(newPage) {
  filters.page = newPage;
  fetchClaims();
}

async function fetchClaims() {
  loading.value = true;
  try {
    const res = await api.get('/claims', { params: filters });
    claims.value = res.data.data;
    pagination.value = res.data.pagination;
  } catch (e) {
    // ignore
  } finally {
    loading.value = false;
  }
}

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

onMounted(() => {
  fetchClaims();
});
</script>
