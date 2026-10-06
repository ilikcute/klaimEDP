<template>
  <span :class="badgeClasses" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold tracking-wider uppercase border">
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'ON_TIME',
  },
});

const config = computed(() => {
  switch (props.status) {
    case 'ON_TIME':
      return {
        label: 'SLA Tepat Waktu',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200',
      };
    case 'AT_RISK':
      return {
        label: 'SLA Berisiko',
        classes: 'bg-amber-50 text-amber-800 border-amber-300 animate-pulse',
      };
    case 'BREACHED':
      return {
        label: 'SLA Terlewat (Breach)',
        classes: 'bg-rose-50 text-rose-700 border-rose-300',
      };
    default:
      return {
        label: props.status,
        classes: 'bg-slate-50 text-slate-600 border-slate-200',
      };
  }
});

const label = computed(() => config.value.label);
const badgeClasses = computed(() => config.value.classes);
</script>
