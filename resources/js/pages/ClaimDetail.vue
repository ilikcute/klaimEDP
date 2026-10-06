<template>
  <div v-if="loading" class="py-20 text-center text-slate-400">
    <div class="inline-flex items-center gap-2 font-medium text-sm">
      <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      Memuat detail klaim...
    </div>
  </div>

  <div v-else-if="!claim" class="py-20 text-center text-slate-500">
    Klaim tidak ditemukan.
  </div>

  <div v-else class="space-y-6">
    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <router-link to="/claims" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition flex items-center gap-1 mb-1">
          ← Kembali ke Daftar Klaim
        </router-link>
        <div class="flex items-center gap-3 flex-wrap">
          <h2 class="text-xl md:text-2xl font-black text-slate-900 font-mono tracking-tight">
            {{ claim.claim_number }}
          </h2>
          <StatusBadge :status="claim.current_state" />
          <SlaBadge v-if="activeSla" :status="activeSla.status" />
        </div>
      </div>

      <!-- Action Panel Buttons -->
      <div class="flex items-center gap-2 flex-wrap">
        <!-- Print / Cetak Form Rekap Button -->
        <router-link
          :to="`/claims/${claim.id}/print`"
          target="_blank"
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold border border-slate-200 transition flex items-center gap-1.5 shadow-2xs hover:border-slate-300"
          title="Buka Lembar Rekap Biaya Operasional Kendaraan & Lampiran Struk"
        >
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Cetak Form Rekap & Nota
        </router-link>

        <!-- Submit Button for Requester -->
        <button
          v-if="allowedActions.includes('SUBMIT') || allowedActions.includes('RESUBMIT')"
          @click="openActionModal(allowedActions.includes('RESUBMIT') ? 'RESUBMIT' : 'SUBMIT')"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          {{ allowedActions.includes('RESUBMIT') ? 'Ajukan Ulang Revisi' : 'Ajukan Klaim (Submit)' }}
        </button>

        <!-- Start Review for EDP Adm -->
        <button
          v-if="allowedActions.includes('START_REVIEW')"
          @click="openActionModal('START_REVIEW')"
          class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition flex items-center gap-1.5 shadow-2xs"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Mulai Review Adm EDP
        </button>

        <!-- Forward for EDP Adm to SPV -->
        <button
          v-if="allowedActions.includes('FORWARD')"
          @click="openActionModal('FORWARD')"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
          Teruskan ke SPV →
        </button>

        <!-- Verify for SPV, Manager, Finance -->
        <button
          v-if="allowedActions.includes('VERIFY')"
          @click="openActionModal('VERIFY')"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          Verifikasi Klaim
        </button>

        <!-- Approve for DBM / BM -->
        <button
          v-if="allowedActions.includes('APPROVE')"
          @click="openActionModal('APPROVE')"
          class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Setujui (Approval DBM/BM)
        </button>

        <!-- Process Payment for Finance -->
        <button
          v-if="allowedActions.includes('PROCESS_PAYMENT') || allowedActions.includes('COMPLETE_PAYMENT')"
          @click="showPaymentModal = true"
          class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Proses Pembayaran
        </button>

        <!-- Request Revision -->
        <button
          v-if="allowedActions.includes('REQUEST_REVISION')"
          @click="openActionModal('REQUEST_REVISION')"
          class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-xl text-xs font-bold transition border border-amber-200"
        >
          Minta Revisi
        </button>

        <!-- Reject -->
        <button
          v-if="allowedActions.includes('REJECT')"
          @click="openActionModal('REJECT')"
          class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition border border-rose-200"
        >
          Tolak
        </button>
      </div>
    </div>

    <!-- Alert if need revision -->
    <div v-if="claim.current_state === 'NEED_REVISION'" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
      <span class="text-lg">⚠️</span>
      <div>
        <p class="font-bold">Klaim memerlukan perbaikan / revisi dari Anda.</p>
        <p class="mt-0.5 text-amber-800">
          Silakan lengkapi atau perbaiki data nota/dokumen di bawah ini, lalu klik tombol <strong>"Ajukan Ulang Revisi"</strong>.
        </p>
      </div>
    </div>

    <!-- Workflow Status & Actor Guidance Banner -->
    <div
      v-if="!['PAID', 'REJECTED', 'CANCELLED', 'DRAFT'].includes(claim.current_state)"
      class="p-4 rounded-2xl border text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs"
      :class="allowedActions.length > 0 ? 'bg-indigo-50/80 border-indigo-200 text-indigo-950' : 'bg-slate-50 border-slate-200 text-slate-700'"
    >
      <div class="flex items-start sm:items-center gap-3">
        <div
          class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
          :class="allowedActions.length > 0 ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'"
        >
          {{ allowedActions.length > 0 ? '⚡' : 'ℹ️' }}
        </div>
        <div>
          <p class="font-bold">
            <span v-if="allowedActions.length > 0">Tindakan Menunggu Keputusan Anda:</span>
            <span v-else>Status Antrean Workflow:</span>
            <span class="text-indigo-600 ml-1 font-extrabold">{{ getWorkflowStageTitle(claim.current_state) }}</span>
          </p>
          <p class="text-[11px] text-slate-500 mt-0.5">
            <span v-if="allowedActions.length > 0">
              Anda login sebagai <strong>{{ authStore.primaryRole }}</strong> dan memiliki hak akses untuk memproses klaim ini. Silakan gunakan tombol tindakan di sudut kanan atas (<strong>Teruskan ke SPV</strong>, <strong>Minta Revisi</strong>, dll).
            </span>
            <span v-else>
              Klaim ini sedang ditinjau oleh <strong>{{ getExpectedActorRole(claim.current_state) }}</strong>. Sebagai <strong>{{ authStore.primaryRole }}</strong> (Pemohon), Anda tidak dapat memverifikasi klaim Anda sendiri. Untuk menguji alur sebagai pemeriksa, gunakan menu dropdown <strong>Role</strong> di sudut kiri atas navbar untuk beralih ke akun <strong>{{ getExpectedActorRole(claim.current_state) }}</strong>.
            </span>
          </p>
        </div>
      </div>

      <div v-if="allowedActions.length === 0" class="shrink-0 text-right">
        <span class="text-[11px] font-bold px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 inline-block shadow-2xs">
          Menunggu: {{ getExpectedActorRole(claim.current_state) }}
        </span>
      </div>
    </div>

    <!-- Main Grid: Left 2 Cols (Details, Receipts, Documents), Right 1 Col (Timeline, Payment, Details) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Overview Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Informasi Klaim</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
              <p class="text-slate-400">Pemohon (Requester)</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ claim.requester?.name }}</p>
              <p class="text-[10px] text-slate-400">{{ claim.requester?.email }}</p>
            </div>
            <div>
              <p class="text-slate-400">Kategori</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ claim.claim_type }}</p>
            </div>
            <div>
              <p class="text-slate-400">Tanggal Pengajuan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ formatDate(claim.claim_date) }}</p>
            </div>
            <div>
              <p class="text-slate-400">Total Nilai Klaim</p>
              <p class="text-base font-black text-indigo-600 mt-0.5">Rp {{ formatNumber(claim.total_amount) }}</p>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-slate-100 text-xs">
            <p class="text-slate-400 font-medium">Keperluan / Keterangan:</p>
            <p class="font-semibold text-slate-700 mt-0.5">{{ claim.description || '-' }}</p>
          </div>
        </div>

        <!-- BBM Details Card if BBM -->
        <div v-if="claim.claim_type === 'BBM' && claim.bbm_detail" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
              <span>⛽</span> Analisa Konsumsi Bahan Bakar (BBM)
            </h3>
            <span
              class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
              :class="claim.bbm_detail.variance_km_per_liter >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
            >
              {{ claim.bbm_detail.variance_km_per_liter >= 0 ? '✓ Sesuai Standar (Compliant)' : 'Di Bawah Standar (Below)' }}
            </span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
              <p class="text-slate-400">Kendaraan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ claim.bbm_detail.vehicle?.name }}</p>
              <p class="text-[10px] text-slate-400">{{ claim.bbm_detail.vehicle?.plate_number }}</p>
            </div>
            <div>
              <p class="text-slate-400">Jarak Tempuh (KM)</p>
              <p class="font-bold text-slate-800 mt-0.5 font-mono">{{ claim.bbm_detail.distance_km }} KM</p>
              <p v-if="claim.bbm_detail.last_odometer_km != null && claim.bbm_detail.current_odometer_km != null" class="text-[10px] text-indigo-600 font-mono mt-0.5">
                (Spedo: {{ claim.bbm_detail.last_odometer_km }} → {{ claim.bbm_detail.current_odometer_km }})
              </p>
            </div>
            <div>
              <p class="text-slate-400">Bensin Sesuai Nota</p>
              <p class="font-bold text-slate-800 mt-0.5 font-mono">{{ claim.bbm_detail.fuel_liter }} Liter</p>
            </div>
            <div>
              <p class="text-slate-400">Rasio Aktual vs Standar</p>
              <p class="font-black text-indigo-700 mt-0.5 font-mono text-sm">
                {{ claim.bbm_detail.actual_km_per_liter }} <span class="text-[10px] text-slate-400 font-normal">/ {{ claim.bbm_detail.standard_km_per_liter }} KM/L</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Health Details Card if HEALTH -->
        <div v-if="claim.claim_type === 'HEALTH' && claim.health_detail" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">
            Plafon Kesehatan Tahunan
          </h3>
          <div class="grid grid-cols-3 gap-4 text-xs">
            <div>
              <p class="text-slate-400">Tahun Klaim</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ claim.health_detail.claim_year }}</p>
            </div>
            <div>
              <p class="text-slate-400">Limit Plafon Snapshot</p>
              <p class="font-bold text-slate-800 mt-0.5">Rp {{ formatNumber(claim.health_detail.policy_limit_snapshot) }}</p>
            </div>
            <div>
              <p class="text-slate-400">Klaim Ini</p>
              <p class="font-bold text-indigo-600 mt-0.5">Rp {{ formatNumber(claim.total_amount) }}</p>
            </div>
          </div>
        </div>

        <!-- Receipts Section -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
              Daftar Nota Transaksi ({{ claim.receipts?.length || 0 }})
            </h3>
            <button
              v-if="isEditable"
              @click="showAddReceipt = true"
              class="text-xs font-bold text-indigo-600 hover:text-indigo-700"
            >
              + Tambah Nota
            </button>
          </div>

          <!-- Add Receipt Inline Form -->
          <div v-if="showAddReceipt" class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
            <p class="text-xs font-bold text-indigo-900">Tambah Nota Baru</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <input v-model="newReceipt.merchant" placeholder="Nama Merchant *" class="text-xs p-2 rounded-xl border bg-white" />
              <input v-model.number="newReceipt.amount" type="number" placeholder="Nominal Rp *" class="text-xs p-2 rounded-xl border bg-white font-mono" />
              <input v-model="newReceipt.transaction_date" type="date" class="text-xs p-2 rounded-xl border bg-white" />
            </div>
            <div class="flex justify-end gap-2">
              <button @click="showAddReceipt = false" class="px-3 py-1 text-xs text-slate-500">Batal</button>
              <button @click="saveNewReceipt" class="px-4 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold">Simpan Nota</button>
            </div>
          </div>

          <div class="divide-y divide-slate-100">
            <div
              v-for="(r, idx) in claim.receipts"
              :key="r.id"
              class="py-3 flex items-center justify-between gap-3 text-xs"
            >
              <div>
                <p class="font-bold text-slate-800">{{ r.merchant }}</p>
                <p class="text-slate-400 text-[11px]">
                  {{ formatDate(r.transaction_date) }} • No: {{ r.receipt_number || '-' }}
                </p>
                <p v-if="r.description" class="text-slate-500 text-[11px] italic mt-0.5">{{ r.description }}</p>
              </div>

              <div class="flex items-center gap-3">
                <span class="font-extrabold text-slate-900 font-mono">Rp {{ formatNumber(r.amount) }}</span>
                <button
                  v-if="isEditable"
                  @click="deleteReceipt(r.id)"
                  class="text-rose-500 hover:text-rose-700 p-1 text-xs"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs font-bold">
            <span class="text-slate-500 uppercase tracking-wider">Total Terverifikasi</span>
            <span class="text-base text-indigo-700 font-extrabold">Rp {{ formatNumber(claim.total_amount) }}</span>
          </div>
        </div>

        <!-- Documents Section -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
              Lampiran Bukti Dokumen & Nota ({{ claim.documents?.length || 0 }})
            </h3>
            <label class="text-xs font-bold text-indigo-600 hover:text-indigo-700 cursor-pointer">
              + Unggah Dokumen
              <input type="file" @change="uploadDocument" class="hidden" />
            </label>
          </div>

          <div v-if="!claim.documents || claim.documents.length === 0" class="p-6 text-center text-xs text-slate-400 border border-dashed rounded-2xl">
            Belum ada berkas foto/nota yang diunggah.
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              v-for="doc in claim.documents"
              :key="doc.id"
              class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3 text-xs"
            >
              <div class="min-w-0">
                <p class="font-bold text-slate-800 truncate">{{ doc.original_filename }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">
                  {{ (doc.file_size / 1024).toFixed(0) }} KB • {{ doc.document_type }}
                </p>
                <p v-if="doc.latitude && doc.longitude" class="text-[10px] text-emerald-700 font-mono mt-1 flex items-center gap-1 font-bold">
                  <span>📍</span>
                  <span>{{ Number(doc.latitude).toFixed(6) }}, {{ Number(doc.longitude).toFixed(6) }}</span>
                  <a
                    :href="'https://www.google.com/maps?q=' + doc.latitude + ',' + doc.longitude"
                    target="_blank"
                    class="text-indigo-600 hover:underline font-semibold ml-1"
                    title="Buka titik koordinat di Google Maps"
                  >
                    (Cek Lokasi SPBU ↗)
                  </a>
                </p>
              </div>

              <div class="flex items-center gap-2">
                <a
                  :href="`/api/documents/${doc.id}/download`"
                  target="_blank"
                  class="px-2.5 py-1 bg-white hover:bg-slate-100 rounded-lg border text-[11px] font-bold text-slate-700 transition"
                >
                  Unduh
                </a>
                <button
                  v-if="isEditable"
                  @click="deleteDocument(doc.id)"
                  class="text-rose-500 hover:text-rose-700 text-xs font-bold"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Timeline & Payment -->
      <div class="space-y-6">
        <!-- Payment Information Card if available -->
        <div v-if="claim.payment" class="bg-white rounded-3xl p-6 border border-emerald-200/80 shadow-xs">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
              <span>💳</span> Bukti Pembayaran Reimbursement
            </h3>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              {{ claim.payment.status }}
            </span>
          </div>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-100">
              <span class="text-slate-400">Jumlah Dibayar:</span>
              <span class="font-black text-emerald-600 text-sm">Rp {{ formatNumber(claim.payment.amount) }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
              <span class="text-slate-400">Bank Transfer:</span>
              <span class="font-bold text-slate-800">{{ claim.payment.bank_name }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
              <span class="text-slate-400">No. Rekening:</span>
              <span class="font-mono text-slate-800">{{ claim.payment.bank_account_number }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
              <span class="text-slate-400">Atas Nama:</span>
              <span class="font-semibold text-slate-800">{{ claim.payment.bank_account_name }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
              <span class="text-slate-400">No. Referensi:</span>
              <span class="font-mono text-slate-800">{{ claim.payment.transfer_reference || '-' }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-slate-400">Diproses Oleh:</span>
              <span class="text-slate-600">{{ claim.payment.processor?.name }}</span>
            </div>
          </div>
        </div>

        <!-- Workflow Timeline Component -->
        <WorkflowTimeline
          :current-state="claim.current_state"
          :histories="claim.status_histories"
        />
      </div>
    </div>

    <!-- Action Confirmation Modal -->
    <ActionModal
      :show="showActionModal"
      :action-type="activeModalAction"
      :claim-number="claim.claim_number"
      @close="showActionModal = false"
      @confirm="executeWorkflowAction"
    />

    <!-- Payment Processing Modal -->
    <PaymentModal
      v-if="showPaymentModal"
      :show="showPaymentModal"
      :claim="claim"
      @close="showPaymentModal = false"
      @confirm="executePayment"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../api/client';
import StatusBadge from '../components/Common/StatusBadge.vue';
import SlaBadge from '../components/Common/SlaBadge.vue';
import WorkflowTimeline from '../components/Workflow/WorkflowTimeline.vue';
import ActionModal from '../components/Workflow/ActionModal.vue';
import PaymentModal from '../components/Payment/PaymentModal.vue';

const route = useRoute();
const authStore = useAuthStore();

const claim = ref(null);
const loading = ref(true);
const allowedActions = ref([]);
const activeSla = ref(null);

const showActionModal = ref(false);
const activeModalAction = ref('');

const showPaymentModal = ref(false);
const showAddReceipt = ref(false);

const newReceipt = reactive({
  merchant: '',
  amount: null,
  transaction_date: new Date().toISOString().split('T')[0],
});

const isEditable = computed(() => {
  return claim.value?.current_state === 'DRAFT' || claim.value?.current_state === 'NEED_REVISION';
});

async function fetchClaim() {
  loading.value = true;
  try {
    const res = await api.get(`/claims/${route.params.id}`);
    claim.value = res.data.data;
    allowedActions.value = res.data.data.allowed_actions || [];
    activeSla.value = res.data.data.sla_instances?.find((s) => !s.completed_at) || null;
  } catch (err) {
    // ignore
  } finally {
    loading.value = false;
  }
}

function openActionModal(action) {
  activeModalAction.value = action;
  showActionModal.value = true;
}

async function executeWorkflowAction(payload) {
  try {
    const action = payload.action;
    let endpoint = `/claims/${claim.value.id}/`;

    if (action === 'SUBMIT' || action === 'RESUBMIT') {
      endpoint += 'submit';
    } else if (['START_REVIEW', 'FORWARD'].includes(action)) {
      endpoint += 'review';
      payload.decision = action;
    } else if (action === 'VERIFY') {
      if (claim.value.current_state === 'SUPERVISOR_VERIFICATION') {
        endpoint += 'supervisor-verification';
      } else if (claim.value.current_state === 'MANAGER_VERIFICATION') {
        endpoint += 'manager-verification';
      } else if (claim.value.current_state === 'FINANCE_VERIFICATION') {
        endpoint += 'finance-verification';
      }
      payload.decision = 'VERIFY';
    } else if (action === 'APPROVE') {
      endpoint += 'management-approval';
      payload.decision = 'APPROVE';
    } else if (action === 'REQUEST_REVISION') {
      endpoint += 'request-revision';
    } else if (action === 'REJECT') {
      endpoint += 'reject';
    }

    await api.post(endpoint, payload);
    showActionModal.value = false;
    await fetchClaim();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses aksi workflow.');
  }
}

async function executePayment(paymentData) {
  try {
    await api.post(`/claims/${claim.value.id}/payment`, paymentData);
    showPaymentModal.value = false;
    await fetchClaim();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses pembayaran.');
  }
}

async function saveNewReceipt() {
  if (!newReceipt.merchant || !newReceipt.amount || !newReceipt.transaction_date) {
    alert('Mohon lengkapi merchant, nominal, dan tanggal.');
    return;
  }
  try {
    await api.post(`/claims/${claim.value.id}/receipts`, newReceipt);
    showAddReceipt.value = false;
    newReceipt.merchant = '';
    newReceipt.amount = null;
    await fetchClaim();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menambah nota.');
  }
}

async function deleteReceipt(receiptId) {
  if (!confirm('Hapus nota ini?')) return;
  try {
    await api.delete(`/claims/${claim.value.id}/receipts/${receiptId}`);
    await fetchClaim();
  } catch (err) {
    alert('Gagal menghapus nota.');
  }
}

async function uploadDocument(e) {
  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);
  formData.append('document_type', 'SUPPORTING_DOC');

  try {
    await api.post(`/claims/${claim.value.id}/documents`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    await fetchClaim();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal mengunggah dokumen.');
  }
}

async function deleteDocument(docId) {
  if (!confirm('Hapus dokumen ini?')) return;
  try {
    await api.delete(`/documents/${docId}`);
    await fetchClaim();
  } catch (err) {
    alert('Gagal menghapus dokumen.');
  }
}

function formatNumber(num) {
  return new Intl.NumberFormat('id-ID').format(num || 0);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function getWorkflowStageTitle(state) {
  switch (state) {
    case 'SUBMITTED': return '1. Menunggu Review Adm EDP';
    case 'EDP_REVIEW': return '2. Sedang Direview Adm EDP';
    case 'SUPERVISOR_VERIFICATION': return '3. Verifikasi Supervisor EDP';
    case 'MANAGER_VERIFICATION': return '4. Verifikasi Manager EDP';
    case 'FINANCE_VERIFICATION': return '5. Verifikasi GA / Finance';
    case 'MANAGEMENT_APPROVAL': return '6. Persetujuan Manajemen (DBM/BM)';
    case 'APPROVED': return '7. Disetujui (Menunggu Pembayaran)';
    case 'PAYMENT_PROCESS': return '8. Proses Transfer Pembayaran';
    case 'NEED_REVISION': return 'Perlu Revisi dari Pemohon';
    default: return state;
  }
}

function getExpectedActorRole(state) {
  switch (state) {
    case 'SUBMITTED':
    case 'EDP_REVIEW':
      return 'Admin EDP (Siti Admin EDP)';
    case 'SUPERVISOR_VERIFICATION':
      return 'Supervisor EDP (Ahmad Supervisor)';
    case 'MANAGER_VERIFICATION':
      return 'Manager EDP (Rendra Manager)';
    case 'FINANCE_VERIFICATION':
    case 'APPROVED':
    case 'PAYMENT_PROCESS':
      return 'Finance (Dewi Finance)';
    case 'MANAGEMENT_APPROVAL':
      return 'Branch Manager / DBM';
    case 'NEED_REVISION':
      return 'Pemohon Klaim (Requester)';
    default:
      return 'Tim Terkait';
  }
}

onMounted(() => {
  fetchClaim();
});
</script>
