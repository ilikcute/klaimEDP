<template>
  <div class="space-y-6 max-w-5xl mx-auto pb-12">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/users" class="hover:text-indigo-600 transition">Pengguna</router-link>
          <span>/</span>
          <span class="text-indigo-600">Tambah Pengguna Baru</span>
        </nav>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Tambah Pengguna & Tentukan Hak Akses
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Buat akun pengguna baru dan tentukan halaman serta fitur apa saja yang diizinkan untuk diakses
        </p>
      </div>

      <router-link
        to="/users"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Daftar Pengguna</span>
      </router-link>
    </div>

    <!-- Error Alert if any -->
    <div v-if="errorMessage" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 font-bold">✕</button>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Section 1: Data Profil & Kredensial -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
          <span class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-sm">
            👤
          </span>
          <div>
            <h3 class="font-bold text-sm text-slate-900">1. Data Akun & Login Pengguna</h3>
            <p class="text-[11px] text-slate-400">Informasi identitas dasar dan akun login</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Nama Lengkap Pengguna *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Budi Santoso"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Alamat Email Resmi (Login) *</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="nama@klaimedp.com"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 font-mono transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">
              NIK (Nomor Induk Karyawan)
            </label>
            <input
              v-model="form.nik"
              type="text"
              placeholder="Contoh: EDP-1005"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 font-mono transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">
              Wilayah Penugasan / Area
            </label>
            <input
              v-model="form.wilayah"
              type="text"
              placeholder="Contoh: Jakarta Barat / Cabang Surabaya"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Role Jabatan Utama *</label>
            <select
              v-model="form.role"
              required
              @change="onRoleChange"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            >
              <option v-for="r in roles" :key="r.name" :value="r.name">
                {{ r.display_name }} ({{ r.name }})
              </option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Kata Sandi (Password) *</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              required
              minlength="6"
              placeholder="Minimal 6 karakter"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>
        </div>

        <div class="pt-2 flex items-center gap-2">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="create_is_active"
            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
          />
          <label for="create_is_active" class="font-bold text-xs text-slate-700 cursor-pointer">
            Akun Aktif (Dapat langsung login dan mengakses sistem)
          </label>
        </div>
      </div>

      <!-- Section 2: Hak Akses Halaman & Fitur -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-sm">
              🔑
            </span>
            <div>
              <h3 class="font-bold text-sm text-slate-900">2. Hak Akses Halaman & Fitur (Page Permissions)</h3>
              <p class="text-[11px] text-slate-400">
                Pilih halaman dan fitur apa saja yang diizinkan untuk dibuka oleh pengguna ini
              </p>
            </div>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center gap-2 self-start sm:self-auto">
            <button
              type="button"
              @click="applyRoleDefaults"
              class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] transition shadow-xs"
              title="Terapkan paket izin bawaan sesuai role jabatan yang dipilih"
            >
              ↻ Terapkan Default Role
            </button>
            <button
              type="button"
              @click="selectAllPages"
              class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition"
            >
              Pilih Semua Halaman
            </button>
            <button
              type="button"
              @click="clearAllPermissions"
              class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition"
            >
              Kosongkan Semua
            </button>
          </div>
        </div>

        <!-- Category A: Page Menu Access -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
              <span>Hak Akses Menu Navigasi & Halaman Aplikasi (Page Routing)</span>
            </p>
            <span class="text-[10px] font-mono text-slate-400 font-bold">
              {{ form.permissions.filter(p => p.startsWith('page.')).length }} dipilih
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <label
              v-for="p in pagePermissions"
              :key="p.name"
              class="p-3 rounded-2xl border transition flex items-start gap-2.5 cursor-pointer hover:bg-slate-50/80"
              :class="form.permissions.includes(p.name) ? 'border-indigo-500 bg-indigo-50/40 shadow-xs' : 'border-slate-200'"
            >
              <input
                type="checkbox"
                :value="p.name"
                v-model="form.permissions"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5 w-4 h-4"
              />
              <div class="min-w-0">
                <p class="font-bold text-xs text-slate-900 leading-tight">
                  {{ p.display_name.replace('Halaman: ', '') }}
                </p>
                <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                  {{ p.description }}
                </p>
              </div>
            </label>
          </div>
        </div>

        <!-- Category B: Workflow & Action Permissions -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-violet-600"></span>
              <span>Otorisasi Tindakan Workflow & Manajemen Klaim (Action Permissions)</span>
            </p>
            <span class="text-[10px] font-mono text-slate-400 font-bold">
              {{ form.permissions.filter(p => !p.startsWith('page.')).length }} dipilih
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <label
              v-for="p in actionPermissions"
              :key="p.name"
              class="p-3 rounded-2xl border transition flex items-start gap-2.5 cursor-pointer hover:bg-slate-50/80"
              :class="form.permissions.includes(p.name) ? 'border-violet-500 bg-violet-50/40 shadow-xs' : 'border-slate-200'"
            >
              <input
                type="checkbox"
                :value="p.name"
                v-model="form.permissions"
                class="rounded border-slate-300 text-violet-600 focus:ring-violet-500 mt-0.5 w-4 h-4"
              />
              <div class="min-w-0">
                <p class="font-bold text-xs text-slate-900 leading-tight">
                  {{ p.display_name }}
                </p>
                <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                  {{ p.description }}
                </p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Action Buttons Bar -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <router-link
          to="/users"
          class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition"
        >
          Batal
        </router-link>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition disabled:opacity-50 flex items-center gap-2"
        >
          <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ isSubmitting ? 'Menyimpan Pengguna...' : 'Simpan Pengguna Baru' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/client';

const router = useRouter();
const roles = ref([]);
const allPermissions = ref([]);
const isSubmitting = ref(false);
const errorMessage = ref('');

const form = reactive({
  name: '',
  email: '',
  nik: '',
  wilayah: '',
  password: '',
  role: 'EDP_OPERASIONAL',
  is_active: true,
  permissions: [],
});

const pagePermissions = computed(() => {
  return allPermissions.value.filter(p => p.name.startsWith('page.'));
});

const actionPermissions = computed(() => {
  return allPermissions.value.filter(p => !p.name.startsWith('page.'));
});

async function loadInitialData() {
  try {
    const [resRoles, resPerms] = await Promise.all([
      api.get('/users/roles'),
      api.get('/users/permissions'),
    ]);
    roles.value = resRoles.data.data;
    allPermissions.value = resPerms.data.data;

    // Apply default role permissions initially
    applyRoleDefaults();
  } catch (e) {
    console.error('Failed to load roles and permissions:', e);
  }
}

function onRoleChange() {
  applyRoleDefaults();
}

function applyRoleDefaults() {
  const selectedRoleObj = roles.value.find(r => r.name === form.role);
  if (selectedRoleObj) {
    form.permissions = [...selectedRoleObj.permissions];
  }
}

function selectAllPages() {
  const pagePermNames = pagePermissions.value.map(p => p.name);
  const currentSet = new Set(form.permissions);
  pagePermNames.forEach(p => currentSet.add(p));
  form.permissions = Array.from(currentSet);
}

function clearAllPermissions() {
  form.permissions = [];
}

async function submitForm() {
  isSubmitting.value = true;
  errorMessage.value = '';

  try {
    await api.post('/users', {
      name: form.name,
      email: form.email,
      nik: form.nik || null,
      wilayah: form.wilayah || null,
      password: form.password,
      role: form.role,
      is_active: form.is_active,
      permissions: form.permissions,
    });

    router.push('/users');
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menambahkan pengguna. Silakan periksa kembali data Anda.';
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  loadInitialData();
});
</script>
