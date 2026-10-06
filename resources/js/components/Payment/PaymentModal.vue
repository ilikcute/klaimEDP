<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50/70 flex items-center justify-between">
        <div class="flex items-center gap-2 text-emerald-800 font-bold text-base">
          <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Proses Pembayaran Reimbursement
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <!-- Summary Box -->
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-500 font-medium">Nomor Klaim</p>
            <p class="text-sm font-bold text-slate-800">{{ claim.claim_number }}</p>
          </div>
          <div class="text-right">
            <p class="text-xs text-slate-500 font-medium">Total Disetujui</p>
            <p class="text-base font-extrabold text-emerald-600">Rp {{ formatNumber(claim.total_amount) }}</p>
          </div>
        </div>

        <!-- Nominal Transfer -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Nominal Transfer (Rp) <span class="text-rose-500">*</span>
          </label>
          <input
            v-model.number="form.amount"
            type="number"
            min="1"
            :max="claim.total_amount"
            required
            class="w-full text-base font-semibold rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
          />
          <p v-if="form.amount > claim.total_amount" class="text-xs text-rose-600 mt-1 font-medium">
            Nominal tidak boleh melebihi total klaim yang disetujui.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Bank -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Nama Bank <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.bank_name"
              type="text"
              placeholder="Contoh: BCA / Mandiri"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
            />
          </div>

          <!-- Tanggal Transfer -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Tanggal Transfer <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.payment_date"
              type="date"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Nomor Rekening -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              No. Rekening Tujuan <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.account_number"
              type="text"
              placeholder="Contoh: 1234567890"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono"
            />
          </div>

          <!-- Nama Pemilik Rekening -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Atas Nama Rekening <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.account_name"
              type="text"
              placeholder="Nama sesuai buku tabungan"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
            />
          </div>
        </div>

        <!-- No Referensi Transfer -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Nomor Referensi / ID Transaksi Bank (Opsional)
          </label>
          <input
            v-model="form.reference_number"
            type="text"
            placeholder="Contoh: TRX-202610-09887"
            class="w-full text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono"
          />
        </div>

        <!-- Finalized Status Check -->
        <div class="pt-2">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              v-model="form.is_final"
              type="checkbox"
              class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300"
            />
            <span class="text-xs font-semibold text-slate-700">
              Tandai pembayaran telah selesai (Status klaim berubah menjadi <span class="text-emerald-600 font-bold">PAID</span>)
            </span>
          </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading || form.amount > claim.total_amount"
            class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-200 transition flex items-center gap-2 disabled:opacity-50"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Simpan & Proses Bayar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const props = defineProps({
  show: Boolean,
  claim: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'confirm']);

const loading = ref(false);

const form = reactive({
  amount: props.claim?.total_amount || 0,
  bank_name: 'BCA',
  account_number: '1234567890',
  account_name: props.claim?.requester?.name || '',
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  is_final: true,
  idempotency_key: 'PAY-' + Math.random().toString(36).substring(2, 12).toUpperCase(),
});

onMounted(() => {
  if (props.claim?.payment) {
    form.amount = props.claim.payment.amount;
    form.bank_name = props.claim.payment.bank_name;
    form.account_number = props.claim.payment.bank_account_number;
    form.account_name = props.claim.payment.bank_account_name;
    form.reference_number = props.claim.payment.transfer_reference || '';
  }
});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

function submit() {
  emit('confirm', { ...form });
}
</script>
