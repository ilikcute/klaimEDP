<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900">
    <div class="max-w-md w-full">
      <!-- Brand Card -->
      <div class="text-center mb-8">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-500 items-center justify-center text-white text-2xl font-black shadow-xl shadow-indigo-500/30 mb-3">
          K
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">
          KlaimEDP
        </h1>
        <p class="text-xs text-slate-400 mt-1 font-medium">
          Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP
        </p>
      </div>

      <!-- Login Form Card -->
      <div class="bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-white/20">
        <h2 class="text-lg font-bold text-slate-800 mb-1">
          Selamat Datang
        </h2>
        <p class="text-xs text-slate-500 mb-6">
          Silakan masuk dengan akun kredensial Anda.
        </p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Email
            </label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-3 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="nama@klaimedp.com"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Password
            </label>
            <input
              v-model="password"
              type="password"
              autocomplete="current-password"
              required
              class="w-full text-sm rounded-xl border border-slate-200 p-3 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="••••••••"
            />
          </div>

          <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium">
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2 disabled:opacity-60"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Masuk ke Sistem
          </button>
        </form>

        <!-- Quick Demo Accounts -->
        <div class="mt-8 pt-6 border-t border-slate-100">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 text-center">
            Akses Cepat Demo Sesuai Role (1-Klik)
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              v-for="u in demoList"
              :key="u.email"
              @click="quickLogin(u.email)"
              type="button"
              class="p-2 rounded-xl border border-slate-200/80 bg-slate-50 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 text-left transition"
            >
              <p class="font-bold text-[11px] truncate">{{ u.role }}</p>
              <p class="text-[10px] text-slate-400 truncate">{{ u.name }}</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('operasional@klaimedp.com');
const password = ref('password');
const loading = ref(false);
const errorMessage = ref('');

const demoList = [
  { role: 'EDP Operasional', name: 'Budi Operasional', email: 'operasional@klaimedp.com' },
  { role: 'EDP Adm (Review)', name: 'Siti Admin EDP', email: 'adm@klaimedp.com' },
  { role: 'Supervisor (Verif 1)', name: 'Ahmad SPV', email: 'supervisor@klaimedp.com' },
  { role: 'Manager (Verif 2)', name: 'Rendra Manager', email: 'manager@klaimedp.com' },
  { role: 'Finance (Bayar)', name: 'Dewi Finance', email: 'finance@klaimedp.com' },
  { role: 'DBM (Approver)', name: 'Hendra DBM', email: 'dbm@klaimedp.com' },
  { role: 'BM (Branch Mgr)', name: 'Bambang BM', email: 'bm@klaimedp.com' },
  { role: 'Admin Sistem', name: 'Super Admin', email: 'admin@klaimedp.com' },
];

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';
  try {
    await authStore.login(email.value, password.value);
    router.push('/');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Login gagal. Periksa kembali email dan password.';
  } finally {
    loading.value = false;
  }
}

async function quickLogin(demoEmail) {
  email.value = demoEmail;
  password.value = 'password';
  await handleLogin();
}
</script>
