<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">
        Antrean Tugas Verifikasi & Approval
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Klaim yang sedang menunggu tindakan atau keputusan Anda berdasarkan wewenang role: <strong class="text-indigo-600">{{ authStore.primaryRole }}</strong>
      </p>
    </div>

    <!-- Task List -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden p-6 space-y-4">
      <div v-if="loading" class="py-12 text-center text-slate-400 text-xs">
        Memuat antrean tugas...
      </div>

      <div v-else-if="tasks.length === 0" class="py-12 text-center text-slate-400 text-xs">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="font-bold text-slate-600 text-sm">Tidak Ada Tugas Tertunda</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Semua pengajuan telah Anda proses atau belum ada tugas baru.</p>
      </div>

      <div v-else class="divide-y divide-slate-100">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 p-2 rounded-2xl transition"
        >
          <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono font-bold text-sm text-slate-900">{{ task.claim_number }}</span>
              <StatusBadge :status="task.current_state" />
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                {{ task.claim_type }}
              </span>
            </div>

            <p class="text-xs text-slate-600">
              Pemohon: <strong class="text-slate-800">{{ task.requester?.name }}</strong> • Diajukan: {{ formatDate(task.claim_date) }}
            </p>

            <p class="text-xs text-slate-500 italic">
              "{{ task.description || 'Tidak ada deskripsi' }}"
            </p>
          </div>

          <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0">
            <div class="text-right">
              <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Nilai</p>
              <p class="text-base font-black text-indigo-600">Rp {{ formatNumber(task.total_amount) }}</p>
            </div>

            <router-link
              :to="`/claims/${task.id}`"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
            >
              Periksa & Putuskan →
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../api/client';
import StatusBadge from '../components/Common/StatusBadge.vue';

const authStore = useAuthStore();
const tasks = ref([]);
const loading = ref(true);

async function fetchTasks() {
  loading.value = true;
  try {
    const res = await api.get('/dashboard');
    tasks.value = res.data.data.pending_tasks || [];
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
  fetchTasks();
});
</script>
