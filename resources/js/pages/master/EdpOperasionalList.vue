<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 mb-1">
          <span>Master Data</span>
          <span>/</span>
          <span>Petugas Lapangan</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Master Data EDP Operasional
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola data petugas EDP lapangan dan penugasan kendaraan dinas operasional untuk klaim BBM
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        + Tambah EDP Operasional
      </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="relative w-full sm:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, atau NIK EDP..."
          class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <select
          v-model="statusFilter"
          class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option value="">Semua Status</option>
          <option value="1">Aktif</option>
          <option value="0">Nonaktif</option>
        </select>

        <button
          @click="fetchData"
          :disabled="isLoading"
          class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition disabled:opacity-50"
          title="Segarkan data"
        >
          <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-bold uppercase text-[10px]">
              <th class="p-4">Petugas EDP Lapangan</th>
              <th class="p-4">NIK</th>
              <th class="p-4">Status</th>
              <th class="p-4">Kendaraan Terhubung</th>
              <th class="p-4 text-right">Volume Klaim</th>
              <th class="p-4 text-right">Total Akumulasi</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading">
              <td colspan="7" class="p-8 text-center text-slate-400">Memuat data petugas EDP...</td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-400">Belum ada data EDP Operasional ditemukan.</td>
            </tr>
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50 transition">
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ user.name.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-900">{{ user.name }}</p>
                    <p class="text-[11px] text-slate-400 font-mono">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span v-if="user.nik" class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono font-bold text-[11px] border border-slate-200">
                  {{ user.nik }}
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
              </td>
              <td class="p-4">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="user.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                >
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="p-4">
                <div v-if="user.assigned_vehicles && user.assigned_vehicles.length > 0" class="space-y-1">
                  <div
                    v-for="v in user.assigned_vehicles"
                    :key="v.id"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-medium"
                  >
                    <span>{{ v.vehicle_type === 'MOTOR' ? '🛵' : '🚗' }}</span>
                    <strong>{{ v.name }}</strong>
                    <span class="font-mono text-slate-500">({{ v.plate_number }})</span>
                  </div>
                </div>
                <span v-else class="text-slate-400 italic text-[11px]">Belum ditugaskan</span>
              </td>
              <td class="p-4 text-right font-mono font-medium text-slate-700">
                {{ user.claims_count || 0 }} berkas
              </td>
              <td class="p-4 text-right font-mono font-bold text-slate-900">
                Rp {{ formatNumber(user.total_claim_amount) }}
              </td>
              <td class="p-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openEditModal(user)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 text-indigo-600 transition"
                    title="Ubah Data / Kendaraan"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(user)"
                    class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-600 transition"
                    title="Hapus / Nonaktifkan"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-black text-slate-900 text-sm">
            {{ isEditing ? 'Ubah Data Petugas EDP' : 'Tambah Petugas EDP Operasional' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
            ✕
          </button>
        </div>

        <form @submit.prevent="saveUser" class="space-y-3 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
            <input
              v-model="modalForm.name"
              type="text"
              required
              placeholder="Contoh: Budi Lapangan"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">NIK (Nomor Induk Karyawan)</label>
            <input
              v-model="modalForm.nik"
              type="text"
              placeholder="Contoh: EDP-1001"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 font-mono"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Email Resmi (Login) *</label>
            <input
              v-model="modalForm.email"
              type="email"
              required
              placeholder="budi.lapangan@klaimedp.com"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 font-mono"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">
              {{ isEditing ? 'Password Baru (Kosongkan bila tidak diganti)' : 'Password Akun *' }}
            </label>
            <input
              v-model="modalForm.password"
              type="password"
              autocomplete="new-password"
              :required="!isEditing"
              :minlength="isEditing && !modalForm.password ? undefined : 6"
              placeholder="Minimal 6 karakter"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Penugasan Kendaraan Dinas</label>
            <select
              v-model="modalForm.assigned_vehicle_id"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 bg-white"
            >
              <option :value="null">-- Tidak Ditugaskan (Pool Umum) --</option>
              <option
                v-for="v in availableVehicles"
                :key="v.id"
                :value="v.id"
              >
                {{ v.vehicle_type === 'MOTOR' ? '🛵' : '🚗' }} {{ v.name }} ({{ v.plate_number }})
              </option>
            </select>
            <p class="text-[10px] text-slate-400 mt-1">
              Kendaraan ini akan otomatis terhubung saat petugas membuat klaim BBM.
            </p>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input
              v-model="modalForm.is_active"
              type="checkbox"
              id="is_active"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="is_active" class="font-medium text-slate-700">Akun Aktif (Bisa Login & Ajukan Klaim)</label>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition disabled:opacity-50"
            >
              {{ isSaving ? 'Menyimpan...' : 'Simpan Petugas' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const isSaving = ref(false);
const users = ref([]);
const vehicles = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');

const showModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);

const modalForm = reactive({
  name: '',
  email: '',
  nik: '',
  password: '',
  is_active: true,
  assigned_vehicle_id: null,
});

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const q = searchQuery.value.toLowerCase().trim();
    const matchesSearch = !q ||
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      (u.nik && u.nik.toLowerCase().includes(q));

    const matchesStatus = statusFilter.value === '' ||
      (statusFilter.value === '1' ? u.is_active : !u.is_active);

    return matchesSearch && matchesStatus;
  });
});

const availableVehicles = computed(() => {
  return vehicles.value.filter(v => v.is_active);
});

async function fetchData() {
  isLoading.value = true;
  try {
    const [userRes, vehicleRes] = await Promise.all([
      api.get('/master/edp-operasional'),
      api.get('/master/vehicles'),
    ]);
    users.value = userRes.data.data || [];
    vehicles.value = vehicleRes.data.data || [];
  } catch (e) {
    // handled
  } finally {
    isLoading.value = false;
  }
}

function openCreateModal() {
  isEditing.value = false;
  editingUserId.value = null;
  modalForm.name = '';
  modalForm.email = '';
  modalForm.nik = '';
  modalForm.password = '';
  modalForm.is_active = true;
  modalForm.assigned_vehicle_id = null;
  showModal.value = true;
}

function openEditModal(user) {
  isEditing.value = true;
  editingUserId.value = user.id;
  modalForm.name = user.name;
  modalForm.email = user.email;
  modalForm.nik = user.nik || '';
  modalForm.password = '';
  modalForm.is_active = Boolean(user.is_active);
  modalForm.assigned_vehicle_id = user.assigned_vehicles && user.assigned_vehicles.length > 0
    ? user.assigned_vehicles[0].id
    : null;
  showModal.value = true;
}

async function saveUser() {
  isSaving.value = true;
  try {
    const payload = { ...modalForm };
    if (isEditing.value && (!payload.password || payload.password.trim() === '')) {
      delete payload.password;
    }

    if (isEditing.value) {
      await api.put(`/master/edp-operasional/${editingUserId.value}`, payload);
    } else {
      await api.post('/master/edp-operasional', payload);
    }
    showModal.value = false;
    await fetchData();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data petugas.');
  } finally {
    isSaving.value = false;
  }
}

async function confirmDelete(user) {
  if (confirm(`Yakin ingin menghapus / menonaktifkan petugas ${user.name}?`)) {
    try {
      await api.delete(`/master/edp-operasional/${user.id}`);
      await fetchData();
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus petugas.');
    }
  }
}

onMounted(() => {
  fetchData();
});
</script>
