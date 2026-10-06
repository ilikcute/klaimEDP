<template>
  <span :class="badgeClasses" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide border">
    <span :class="dotClasses" class="w-1.5 h-1.5 rounded-full"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const config = computed(() => {
  switch (props.status) {
    case 'DRAFT':
      return {
        label: 'Draft',
        classes: 'bg-slate-50 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
      };
    case 'SUBMITTED':
      return {
        label: 'Diajukan',
        classes: 'bg-blue-50 text-blue-700 border-blue-200',
        dot: 'bg-blue-500',
      };
    case 'EDP_REVIEW':
      return {
        label: 'Review EDP Adm',
        classes: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        dot: 'bg-indigo-500',
      };
    case 'SUPERVISOR_VERIFICATION':
      return {
        label: 'Verif SPV',
        classes: 'bg-purple-50 text-purple-700 border-purple-200',
        dot: 'bg-purple-500',
      };
    case 'MANAGER_VERIFICATION':
      return {
        label: 'Verif Manager',
        classes: 'bg-violet-50 text-violet-700 border-violet-200',
        dot: 'bg-violet-500',
      };
    case 'FINANCE_VERIFICATION':
      return {
        label: 'Verif Finance',
        classes: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
      };
    case 'MANAGEMENT_APPROVAL':
      return {
        label: 'Approval DBM/BM',
        classes: 'bg-orange-50 text-orange-700 border-orange-200',
        dot: 'bg-orange-500 animate-pulse',
      };
    case 'APPROVED':
      return {
        label: 'Disetujui',
        classes: 'bg-teal-50 text-teal-700 border-teal-200',
        dot: 'bg-teal-500',
      };
    case 'PAYMENT_PROCESS':
      return {
        label: 'Proses Bayar',
        classes: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        dot: 'bg-cyan-500 animate-pulse',
      };
    case 'PAID':
      return {
        label: 'Lunas (Paid)',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        dot: 'bg-emerald-500',
      };
    case 'NEED_REVISION':
      return {
        label: 'Perlu Revisi',
        classes: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
      };
    case 'REJECTED':
      return {
        label: 'Ditolak',
        classes: 'bg-red-50 text-red-700 border-red-200',
        dot: 'bg-red-500',
      };
    case 'CANCELLED':
      return {
        label: 'Dibatalkan',
        classes: 'bg-zinc-100 text-zinc-600 border-zinc-200',
        dot: 'bg-zinc-400',
      };
    default:
      return {
        label: props.status,
        classes: 'bg-slate-100 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
      };
  }
});

const label = computed(() => config.value.label);
const badgeClasses = computed(() => config.value.classes);
const dotClasses = computed(() => config.value.dot);
</script>
