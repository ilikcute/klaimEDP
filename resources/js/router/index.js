import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Login from '../pages/Login.vue';
import AppLayout from '../components/Layout/AppLayout.vue';
import Dashboard from '../pages/Dashboard.vue';
import ClaimList from '../pages/ClaimList.vue';
import ClaimDetail from '../pages/ClaimDetail.vue';
import CreateClaim from '../pages/CreateClaim.vue';
import ClaimPrint from '../pages/ClaimPrint.vue';
import TaskQueue from '../pages/TaskQueue.vue';
import ReportHub from '../pages/reports/ReportHub.vue';
import ReportByType from '../pages/reports/ReportByType.vue';
import ReportHistorical from '../pages/reports/ReportHistorical.vue';
import ReportSla from '../pages/reports/ReportSla.vue';
import ReportBbm from '../pages/reports/ReportBbm.vue';
import ReportHealth from '../pages/reports/ReportHealth.vue';
import ReportApprovals from '../pages/reports/ReportApprovals.vue';
import ReportAging from '../pages/reports/ReportAging.vue';
import ReportDuplicates from '../pages/reports/ReportDuplicates.vue';
import EdpOperasionalList from '../pages/master/EdpOperasionalList.vue';
import VehicleList from '../pages/master/VehicleList.vue';
import UserList from '../pages/users/UserList.vue';
import UserCreate from '../pages/users/UserCreate.vue';
import UserPermissions from '../pages/users/UserPermissions.vue';
import RoleMatrix from '../pages/users/RoleMatrix.vue';
import AuditLog from '../pages/AuditLog.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: Dashboard,
            },
            {
                path: 'claims',
                name: 'claims.index',
                component: ClaimList,
            },
            {
                path: 'claims/create',
                name: 'claims.create',
                component: CreateClaim,
            },
            {
                path: 'claims/:id',
                name: 'claims.show',
                component: ClaimDetail,
            },
            {
                path: 'claims/:id/print',
                name: 'claims.print',
                component: ClaimPrint,
            },
            {
                path: 'tasks',
                name: 'tasks.index',
                component: TaskQueue,
            },
            {
                path: 'reports',
                name: 'reports.index',
                component: ReportHub,
            },
            {
                path: 'reports/by-type',
                name: 'reports.by-type',
                component: ReportByType,
            },
            {
                path: 'reports/historical',
                name: 'reports.historical',
                component: ReportHistorical,
            },
            {
                path: 'reports/sla',
                name: 'reports.sla',
                component: ReportSla,
            },
            {
                path: 'reports/bbm',
                name: 'reports.bbm',
                component: ReportBbm,
            },
            {
                path: 'reports/health',
                name: 'reports.health',
                component: ReportHealth,
            },
            {
                path: 'reports/approvals',
                name: 'reports.approvals',
                component: ReportApprovals,
            },
            {
                path: 'reports/aging',
                name: 'reports.aging',
                component: ReportAging,
            },
            {
                path: 'reports/duplicates',
                name: 'reports.duplicates',
                component: ReportDuplicates,
            },
            {
                path: 'master/edp-operasional',
                name: 'master.edp-operasional',
                component: EdpOperasionalList,
            },
            {
                path: 'master/vehicles',
                name: 'master.vehicles',
                component: VehicleList,
            },
            {
                path: 'audit',
                name: 'audit.index',
                component: AuditLog,
                meta: { requiresPermission: 'page.audit' },
            },
            {
                path: 'users',
                name: 'users.index',
                component: UserList,
                meta: { requiresPermission: 'page.users' },
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: UserCreate,
                meta: { requiresPermission: 'page.users' },
            },
            {
                path: 'users/:id/permissions',
                name: 'users.permissions',
                component: UserPermissions,
                meta: { requiresPermission: 'page.users' },
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: UserPermissions,
                meta: { requiresPermission: 'page.users' },
            },
            {
                path: 'users/roles',
                name: 'users.roles',
                component: RoleMatrix,
                meta: { requiresPermission: 'page.users' },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guestOnly && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else if (to.meta.requiresPermission && !authStore.hasPermission(to.meta.requiresPermission)) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
