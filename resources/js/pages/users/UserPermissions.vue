<template>
  <div class="space-y-6 max-w-5xl mx-auto pb-16">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/users" class="hover:text-indigo-600 transition">Pengguna</router-link>
          <span>/</span>
          <span class="text-slate-600 font-bold truncate max-w-xs">{{ user?.name || 'Memuat...' }}</span>
          <span>/</span>
          <span class="text-indigo-600">Atur Hak Akses</span>
        </nav>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Pengaturan Hak Akses: {{ user?.name }}
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Tentukan halaman menu apa saja yang dapat dibuka serta wewenang workflow untuk akun ini
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

    <!-- Alert Notifications -->
    <div v-if="successMessage" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between animate-in fade-in duration-200">
      <div class="flex items-center gap-2">
        <span>✓</span>
        <span>{{ successMessage }}</span>
      </div>
      <button @click="successMessage = ''" class="text-emerald-600 hover:text-emerald-800 font-bold">✕</button>
    </div>

    <div v-if="errorMessage" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between animate-in fade-in duration-200">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="text-rose-600 hover:text-rose-800 font-bold">✕</button>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs">
      <div class="flex items-center justify-center gap-2">
        <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Memuat data hak akses pengguna...</span>
      </div>
    </div>

    <form v-else @submit.prevent="savePermissions" class="space-y-6">
      <!-- Section 1: User Profile & Role Card -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
              {{ user.name.charAt(0) }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-extrabold text-base text-slate-900">{{ user.name }}</h3>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                  :class="user.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                >
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>
              <p class="text-xs text-slate-400 font-mono mt-0.5">{{ user.email }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Status Akun:</span>
            <button
              type="button"
              @click="form.is_active = !form.is_active"
              class="px-3 py-1.5 rounded-xl text-xs font-bold transition border"
              :class="form.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'"
            >
              {{ form.is_active ? '✓ Akun Aktif' : '✕ Akun Nonaktif' }}
            </button>
          </div>
        </div>

        <!-- Editable Account Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">
              NIK (Nomor Induk Karyawan)
            </label>
            <input
              v-model="form.nik"
              type="text"
              placeholder="Contoh: EDP-1001"
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
              placeholder="Contoh: Jakarta Barat / Surabaya"
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block font-bold text-xs text-slate-700 mb-1.5">Role Jabatan Utama *</label>
            <select
              v-model="form.role"
              required
              class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
            >
              <option v-for="r in roles" :key="r.name" :value="r.name">
                {{ r.display_name }} ({{ r.name }})
              </option>
            </select>
          </div>
        </div>

        <div class="pt-1">
          <label class="block font-bold text-xs text-slate-700 mb-1.5">
            Ganti Password Akun <span class="text-[10px] text-slate-400 font-normal">(Kosongkan jika tidak ingin diubah)</span>
          </label>
          <input
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            :minlength="form.password ? 6 : undefined"
            placeholder="Minimal 6 karakter baru"
            class="w-full sm:w-1/2 px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
          />
        </div>
      </div>

      <!-- Section 2: Permissions Assignment Matrix -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
          <div>
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
              <span>🔑</span>
              <span>Hak Akses Halaman & Fitur Pengguna</span>
            </h3>
            <p class="text-[11px] text-slate-400 mt-0.5">
              Centang halaman dan otorisasi yang ingin diberikan langsung kepada pengguna ini
            </p>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-2 self-start sm:self-auto">
            <button
              type="button"
              @click="applyRoleDefaults"
              class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] transition shadow-xs"
              title="Reset dan terapkan izin bawaan sesuai role jabatan pengguna saat ini"
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

        <!-- Special Banner if User is Super Admin -->
        <div v-if="form.role === 'ADMIN'" class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-start gap-3">
          <span class="text-base">★</span>
          <div>
            <p class="font-bold">Role Administrator Sistem (Super Admin)</p>
            <p class="text-[11px] text-purple-700 mt-0.5">
              Pengguna dengan role <strong>ADMIN</strong> secara otomatis memiliki akses tidak terbatas ke seluruh halaman dan semua tindakan di sistem KlaimEDP.
            </p>
          </div>
        </div>

        <!-- Category A: Hak Akses Halaman Menu -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
              <span>Hak Akses Menu Navigasi & Halaman Aplikasi (Routing Access)</span>
            </p>
            <span class="text-[10px] font-mono text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full">
              {{ form.permissions.filter(p => p.startsWith('page.')).length }} / {{ pagePermissions.length }} Halaman Diizinkan
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <label
              v-for="p in pagePermissions"
              :key="p.name"
              class="p-3.5 rounded-2xl border transition flex items-start gap-3 cursor-pointer hover:bg-slate-50/80"
              :class="form.permissions.includes(p.name) ? 'border-indigo-500 bg-indigo-50/40 shadow-xs' : 'border-slate-200'"
            >
              <input
                type="checkbox"
                :value="p.name"
                v-model="form.permissions"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5 w-4 h-4 shrink-0"
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

        <!-- Category B: Workflow Action Permissions -->
        <div class="space-y-3 pt-5 border-t border-slate-100">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span>
              <span>Otorisasi Tindakan Workflow & Manajemen Klaim</span>
            </p>
            <span class="text-[10px] font-mono text-violet-600 font-bold bg-violet-50 px-2 py-0.5 rounded-full">
              {{ form.permissions.filter(p => !p.startsWith('page.')).length }} / {{ actionPermissions.length }} Tindakan Diizinkan
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <label
              v-for="p in actionPermissions"
              :key="p.name"
              class="p-3.5 rounded-2xl border transition flex items-start gap-3 cursor-pointer hover:bg-slate-50/80"
              :class="form.permissions.includes(p.name) ? 'border-violet-500 bg-violet-50/40 shadow-xs' : 'border-slate-200'"
            >
              <input
                type="checkbox"
                :value="p.name"
                v-model="form.permissions"
                class="rounded border-slate-300 text-violet-600 focus:ring-violet-500 mt-0.5 w-4 h-4 shrink-0"
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

      <!-- Sticky Save Bar -->
      <div class="sticky bottom-4 z-20 bg-slate-900 text-white p-4 rounded-2xl shadow-xl flex items-center justify-between gap-4 border border-slate-800 animate-in slide-in-from-bottom-2 duration-200">
        <div class="flex items-center gap-2 text-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Total <strong>{{ form.permissions.length }}</strong> izin dipilih untuk <strong>{{ form.name }}</strong></span>
        </div>

        <div class="flex items-center gap-2">
          <router-link
            to="/users"
            class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold transition"
          >
            Batal
          </router-link>

          <button
            type="submit"
            :disabled="isSaving"
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition disabled:opacity-50 flex items-center gap-2"
          >
            <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Perubahan Hak Akses' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api/client';

const route = useRoute();
const router = useRouter();

const userId = route.params.id;
const user = ref(null);
const roles = ref([]);
const allPermissions = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = reactive({
  name: '',
  email: '',
  nik: '',
  wilayah: '',
  password: '',
  role: '',
  is_active: true,
  permissions: [],
});

const pagePermissions = computed(() => {
  return allPermissions.value.filter(p => p.name.startsWith('page.'));
});

const actionPermissions = computed(() => {
  return allPermissions.value.filter(p => !p.name.startsWith('page.'));
});

async function loadData() {
  isLoading.value = true;
  try {
    const [resUser, resRoles, resPerms] = await Promise.all([
      api.get(`/users/${userId}`),
      api.get('/users/roles'),
      api.get('/users/permissions'),
    ]);

    user.value = resUser.data.data;
    roles.value = resRoles.data.data;
    allPermissions.value = resPerms.data.data;

    // Populate form
    form.name = user.value.name;
    form.email = user.value.email;
    form.nik = user.value.nik || '';
    form.wilayah = user.value.wilayah || '';
    form.is_active = !!user.value.is_active;
    form.role = user.value.roles?.[0]?.name || 'EDP_OPERASIONAL';

    // If user has direct permissions, use them; otherwise use their effective permissions
    if (user.value.direct_permissions && user.value.direct_permissions.length > 0) {
      form.permissions = [...user.value.direct_permissions];
    } else {
      form.permissions = [...(user.value.all_permissions || [])];
    }
  } catch (e) {
    errorMessage.value = 'Gagal memuat data pengguna: ' + (e.response?.data?.message || e.message);
  } finally {
    isLoading.value = false;
  }
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

async function savePermissions() {
  isSaving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const payload = {
      name: form.name,
      nik: form.nik || null,
      wilayah: form.wilayah || null,
      role: form.role,
      is_active: form.is_active,
      permissions: form.permissions,
    };

    if (form.password && form.password.trim() !== '') {
      payload.password = form.password;
    }

    const res = await api.put(`/users/${userId}`, payload);
    user.value = res.data.data;
    form.password = '';
    successMessage.value = `Hak akses dan data akun ${user.value.name} berhasil diperbarui.`;

    // Scroll smoothly to top to show confirmation
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menyimpan perubahan hak akses.';
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>
