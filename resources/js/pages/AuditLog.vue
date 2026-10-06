<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">
        Catatan Audit Trail (Immutable Audit Events)
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Rekaman seluruh aktivitas transisi, perubahan status, verifikasi, dan pembayaran yang bersifat append-only
      </p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-xs text-slate-400">
        Memuat audit log...
      </div>

      <div v-else-if="events.length === 0" class="p-12 text-center text-xs text-slate-400">
        Tidak ada event audit.
      </div>

      <div v-else class="divide-y divide-slate-100 text-xs">
        <div
          v-for="ev in events"
          :key="ev.id"
          class="p-4 hover:bg-slate-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3"
        >
          <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-slate-100 text-slate-800">
                {{ ev.event_type }}
              </span>
              <span class="font-bold text-slate-700">
                {{ ev.actor?.name || 'Sistem / Anonim' }}
              </span>
              <span class="text-[10px] text-slate-400 font-mono">
                IP: {{ ev.ip_address || '127.0.0.1' }}
              </span>
            </div>

            <p class="text-slate-500 text-[11px]">
              Entitas: <span class="font-mono text-slate-700">{{ ev.entity_type }} #{{ ev.entity_id }}</span>
            </p>

            <div v-if="ev.after_data" class="text-[11px] bg-slate-50 p-2 rounded-xl border border-slate-100 font-mono text-slate-600 mt-1 max-w-xl truncate">
              {{ JSON.stringify(ev.after_data) }}
            </div>
          </div>

          <div class="text-[11px] text-slate-400 whitespace-nowrap">
            {{ formatDateTime(ev.created_at) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../api/client';

const events = ref([]);
const loading = ref(true);

async function fetchAuditEvents() {
  loading.value = true;
  try {
    const res = await api.get('/audit-events');
    events.value = res.data.data || [];
  } catch (e) {
    // ignore
  } finally {
    loading.value = false;
  }
}

function formatDateTime(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
}

onMounted(() => {
  fetchAuditEvents();
});
</script>
