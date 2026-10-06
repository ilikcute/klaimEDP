<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
    <h3 class="text-sm font-bold text-slate-800 tracking-wide uppercase flex items-center gap-2 mb-6">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
      </svg>
      Tracking Alur Workflow & Verifikasi
    </h3>

    <!-- Step Progression Bar -->
    <div class="relative mb-8 px-2 overflow-x-auto">
      <div class="flex items-center justify-between min-w-[580px]">
        <div
          v-for="(step, idx) in mainSteps"
          :key="step.key"
          class="flex flex-col items-center relative z-10 flex-1"
        >
          <!-- Line connector behind -->
          <div
            v-if="idx < mainSteps.length - 1"
            class="absolute top-4 left-1/2 w-full h-0.5 -z-10"
            :class="isStepPassed(step.key) ? 'bg-indigo-600' : 'bg-slate-200'"
          ></div>

          <!-- Step Icon -->
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 shadow-xs"
            :class="getStepCircleClass(step.key)"
          >
            <svg v-if="isStepPassed(step.key)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span v-else>{{ idx + 1 }}</span>
          </div>

          <!-- Label -->
          <div class="text-center mt-2">
            <p class="text-xs font-semibold" :class="isStepActive(step.key) ? 'text-indigo-600 font-bold' : isStepPassed(step.key) ? 'text-slate-800' : 'text-slate-400'">
              {{ step.label }}
            </p>
            <p v-if="step.subtitle" class="text-[10px] text-slate-400 font-normal">
              {{ step.subtitle }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Status History Log -->
    <div class="border-t border-slate-100 pt-4">
      <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
        Catatan & Histori Transisi
      </h4>

      <div v-if="!histories || histories.length === 0" class="text-xs text-slate-400 italic py-2">
        Belum ada catatan transisi status.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(hist, i) in histories"
          :key="hist.id || i"
          class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/80 border border-slate-100 text-xs"
        >
          <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
              <span class="font-bold text-slate-800">
                {{ formatActionTitle(hist) }}
              </span>
              <span class="text-[11px] text-slate-400">
                {{ formatDateTime(hist.created_at) }}
              </span>
            </div>

            <p class="text-slate-600 mb-1">
              Oleh: <strong class="text-slate-700">{{ hist.actor?.name || 'Sistem' }}</strong>
            </p>

            <div v-if="hist.reason" class="text-rose-600 font-medium bg-rose-50 px-2 py-1 rounded inline-block mb-1">
              Alasan: {{ hist.reason }}
            </div>

            <p v-if="hist.notes" class="text-slate-600 bg-white p-2 rounded border border-slate-200/60 mt-1 italic">
              "{{ hist.notes }}"
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  currentState: {
    type: String,
    required: true,
  },
  histories: {
    type: Array,
    default: () => [],
  },
});

const mainSteps = [
  { key: 'SUBMITTED', label: 'Diajukan' },
  { key: 'EDP_REVIEW', label: 'Review Adm' },
  { key: 'SUPERVISOR_VERIFICATION', label: 'Verif SPV' },
  { key: 'MANAGER_VERIFICATION', label: 'Verif Manager' },
  { key: 'FINANCE_VERIFICATION', label: 'Verif Finance' },
  { key: 'MANAGEMENT_APPROVAL', label: 'Approval', subtitle: 'DBM / BM' },
  { key: 'PAYMENT_PROCESS', label: 'Pembayaran' },
  { key: 'PAID', label: 'Selesai' },
];

const stateHierarchy = {
  DRAFT: 0,
  SUBMITTED: 1,
  EDP_REVIEW: 2,
  SUPERVISOR_VERIFICATION: 3,
  MANAGER_VERIFICATION: 4,
  FINANCE_VERIFICATION: 5,
  MANAGEMENT_APPROVAL: 6,
  APPROVED: 6.5,
  PAYMENT_PROCESS: 7,
  PAID: 8,
  NEED_REVISION: 1,
  REJECTED: -1,
  CANCELLED: -1,
};

function getCurrentWeight() {
  return stateHierarchy[props.currentState] ?? 0;
}

function isStepPassed(stepKey) {
  const currentWeight = getCurrentWeight();
  const stepWeight = stateHierarchy[stepKey] ?? 0;
  return currentWeight > stepWeight;
}

function isStepActive(stepKey) {
  if (props.currentState === stepKey) return true;
  if (props.currentState === 'APPROVED' && stepKey === 'MANAGEMENT_APPROVAL') return true;
  return false;
}

function getStepCircleClass(stepKey) {
  if (isStepActive(stepKey)) {
    return 'bg-indigo-600 text-white ring-4 ring-indigo-100 ring-offset-1 scale-110';
  }
  if (isStepPassed(stepKey)) {
    return 'bg-emerald-500 text-white';
  }
  return 'bg-slate-100 text-slate-400 border border-slate-200';
}

function formatActionTitle(hist) {
  const actionLabels = {
    SUBMIT: 'Pengajuan Klaim',
    RESUBMIT: 'Pengajuan Ulang Revisi',
    START_REVIEW: 'Mulai Ditinjau EDP Adm',
    FORWARD: 'Diteruskan ke Supervisor',
    VERIFY: 'Verifikasi Berhasil',
    APPROVE: 'Persetujuan Manajemen (Disetujui)',
    REQUEST_REVISION: 'Permintaan Perbaikan / Revisi',
    REJECT: 'Klaim Ditolak',
    PROCESS_PAYMENT: 'Pembayaran Sedang Diproses',
    COMPLETE_PAYMENT: 'Pembayaran Berhasil Dilakukan',
    CANCEL: 'Klaim Dibatalkan',
  };
  return actionLabels[hist.action] || hist.action;
}

function formatDateTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
