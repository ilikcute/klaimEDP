<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PagePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Page Access Permissions
            ['name' => 'page.dashboard', 'display_name' => 'Halaman: Dashboard Ringkasan', 'description' => 'Akses dashboard utama dan KPI ringkasan.'],
            ['name' => 'page.claims.create', 'display_name' => 'Halaman: Buat Klaim Baru', 'description' => 'Akses pengajuan klaim baru (BBM, Tol, Kesehatan).'],
            ['name' => 'page.claims.index', 'display_name' => 'Halaman: Daftar Klaim', 'description' => 'Akses daftar dan riwayat pengajuan klaim.'],
            ['name' => 'page.tasks', 'display_name' => 'Halaman: Antrean Tugas', 'description' => 'Akses daftar tugas verifikasi atau approval yang tertunda.'],
            ['name' => 'page.reports', 'display_name' => 'Halaman: Laporan & Analisa', 'description' => 'Akses direktori pusat laporan dan analisa analitik.'],
            ['name' => 'page.master.edp', 'display_name' => 'Halaman: Master EDP Lapangan', 'description' => 'Akses pengelolaan data petugas EDP lapangan.'],
            ['name' => 'page.master.vehicles', 'display_name' => 'Halaman: Master Kendaraan Dinas', 'description' => 'Akses pengelolaan data armada kendaraan dinas.'],
            ['name' => 'page.audit', 'display_name' => 'Halaman: Audit Trail Log', 'description' => 'Akses log audit keamanan dan aktivitas sistem.'],
            ['name' => 'page.users', 'display_name' => 'Halaman: Manajemen User & Role Permission', 'description' => 'Akses pengaturan pengguna dan penugasan hak akses halaman.'],

            // Workflow Action Permissions
            ['name' => 'user.manage', 'display_name' => 'Aksi: Kelola Akun & Role User', 'description' => 'Dapat menambah, mengedit, dan menghapus pengguna.'],
            ['name' => 'user.assign_permissions', 'display_name' => 'Aksi: Atur Hak Akses User', 'description' => 'Dapat mengatur halaman yang boleh diakses oleh setiap user.'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // Assign page permissions to roles as defaults
        $roles = Role::all()->keyBy('name');

        if (isset($roles['ADMIN'])) {
            $roles['ADMIN']->permissions()->syncWithoutDetaching(Permission::pluck('id'));
        }

        if (isset($roles['EDP_OPERASIONAL'])) {
            $roles['EDP_OPERASIONAL']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.create', 'page.claims.index'])->pluck('id')
            );
        }

        if (isset($roles['EDP_ADM'])) {
            $roles['EDP_ADM']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.master.edp', 'page.master.vehicles'])->pluck('id')
            );
        }

        if (isset($roles['EDP_SUPERVISOR'])) {
            $roles['EDP_SUPERVISOR']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.master.edp', 'page.master.vehicles', 'page.reports'])->pluck('id')
            );
        }

        if (isset($roles['EDP_MANAGER'])) {
            $roles['EDP_MANAGER']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.reports', 'page.audit', 'page.master.edp', 'page.master.vehicles'])->pluck('id')
            );
        }

        if (isset($roles['FINANCE'])) {
            $roles['FINANCE']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.reports'])->pluck('id')
            );
        }

        if (isset($roles['DBM'])) {
            $roles['DBM']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.reports', 'page.audit'])->pluck('id')
            );
        }

        if (isset($roles['BM'])) {
            $roles['BM']->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['page.dashboard', 'page.claims.index', 'page.tasks', 'page.reports', 'page.audit'])->pluck('id')
            );
        }
    }
}
