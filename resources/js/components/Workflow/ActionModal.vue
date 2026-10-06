<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between" :class="headerBg">
        <h3 class="font-bold text-base" :class="headerText">
          {{ title }}
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <p class="text-sm text-slate-600 leading-relaxed">
          {{ description }}
        </p>

        <!-- Reason selection if revision or reject -->
        <div v-if="needsReason">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Alasan <span class="text-rose-500">*</span>
          </label>
          <select
            v-model="form.reason"
            required
            class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
          >
            <option value="" disabled>-- Pilih Alasan --</option>
            <option v-for="opt in reasonOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </div>

        <!-- Notes / Remarks -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Catatan Tambahan {{ isNotesRequired ? '*' : '(Opsional)' }}
          </label>
          <textarea
            v-model="form.notes"
            :required="isNotesRequired"
            rows="3"
            class="w-full text-sm rounded-xl border border-slate-200 p-3 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            :placeholder="notesPlaceholder"
          ></textarea>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium">
          {{ errorMessage }}
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md transition flex items-center gap-2"
            :class="submitBtnClass"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            {{ submitLabel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';

const props = defineProps({
  show: Boolean,
  actionType: {
    type: String, // 'VERIFY', 'APPROVE', 'REQUEST_REVISION', 'REJECT', 'FORWARD', 'START_REVIEW'
    required: true,
  },
  claimNumber: String,
});

const emit = defineEmits(['close', 'confirm']);

const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
  reason: '',
  notes: '',
});

const needsReason = computed(() => ['REQUEST_REVISION', 'REJECT'].includes(props.actionType));
const isNotesRequired = computed(() => ['REQUEST_REVISION', 'REJECT'].includes(props.actionType));

const reasonOptions = computed(() => {
  if (props.actionType === 'REQUEST_REVISION') {
    return [
      'Nota fisik buram atau tidak terbaca',
      'Nominal nota dan input sistem tidak sesuai',
      'Lampiran bukti dokumen pendukung kurang lengkap',
      'Tanggal transaksi di luar periode klaim',
      'Penjelasan rute atau keperluan dinas belum detail',
      'Lainnya',
    ];
  }
  if (props.actionType === 'REJECT') {
    return [
      'Pelanggaran kebijakan reimbursement perusahaan',
      'Klaim ganda (duplikat transaksi)',
      'Dokumen nota fiktif atau tidak valid',
      'Melebihi batas toleransi/plafon tanpa persetujuan khusus',
      'Lainnya',
    ];
  }
  return [];
});

const title = computed(() => {
  switch (props.actionType) {
    case 'START_REVIEW': return 'Mulai Review Klaim';
    case 'FORWARD': return 'Teruskan ke Supervisor';
    case 'VERIFY': return 'Verifikasi Klaim';
    case 'APPROVE': return 'Persetujuan Manajemen (Approve)';
    case 'REQUEST_REVISION': return 'Minta Revisi Klaim';
    case 'REJECT': return 'Tolak Klaim';
    default: return 'Konfirmasi Tindakan';
  }
});

const description = computed(() => {
  switch (props.actionType) {
    case 'FORWARD':
      return `Klaim ${props.claimNumber} akan diteruskan ke tahap Verifikasi Supervisor.`;
    case 'VERIFY':
      return `Anda menyatakan bahwa dokumen dan nominal klaim ${props.claimNumber} telah diverifikasi dan valid.`;
    case 'APPROVE':
      return `Anda menyetujui klaim ${props.claimNumber} sebagai manajemen. Status akan menjadi APPROVED dan siap dibayar oleh Finance.`;
    case 'REQUEST_REVISION':
      return `Klaim akan dikembalikan ke pembuat klaim untuk diperbaiki sesuai alasan yang Anda tentukan.`;
    case 'REJECT':
      return `PERHATIAN: Klaim akan ditolak secara permanen. Tindakan ini tidak dapat dibatalkan.`;
    default:
      return `Lanjutkan proses pada klaim ${props.claimNumber}?`;
  }
});

const headerBg = computed(() => {
  if (props.actionType === 'REJECT') return 'bg-rose-50';
  if (props.actionType === 'REQUEST_REVISION') return 'bg-amber-50';
  if (props.actionType === 'APPROVE') return 'bg-emerald-50';
  return 'bg-indigo-50';
});

const headerText = computed(() => {
  if (props.actionType === 'REJECT') return 'text-rose-800';
  if (props.actionType === 'REQUEST_REVISION') return 'text-amber-800';
  if (props.actionType === 'APPROVE') return 'text-emerald-800';
  return 'text-indigo-800';
});

const submitBtnClass = computed(() => {
  if (props.actionType === 'REJECT') return 'bg-rose-600 hover:bg-rose-700 shadow-rose-200';
  if (props.actionType === 'REQUEST_REVISION') return 'bg-amber-600 hover:bg-amber-700 shadow-amber-200';
  if (props.actionType === 'APPROVE') return 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200';
  return 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200';
});

const submitLabel = computed(() => {
  switch (props.actionType) {
    case 'APPROVE': return 'Ya, Setujui Klaim';
    case 'VERIFY': return 'Ya, Verifikasi';
    case 'FORWARD': return 'Teruskan Klaim';
    case 'REQUEST_REVISION': return 'Kirim Permintaan Revisi';
    case 'REJECT': return 'Tolak Permanen';
    default: return 'Lanjutkan';
  }
});

const notesPlaceholder = computed(() => {
  if (props.actionType === 'REQUEST_REVISION') return 'Jelaskan bagian mana yang perlu diperbaiki oleh requester...';
  if (props.actionType === 'REJECT') return 'Jelaskan dasar penolakan klaim ini...';
  return 'Tambahkan catatan jika diperlukan...';
});

function submit() {
  emit('confirm', {
    action: props.actionType,
    reason: form.reason,
    notes: form.notes,
  });
}
</script>
