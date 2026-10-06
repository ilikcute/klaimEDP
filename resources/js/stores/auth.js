import { defineStore } from 'pinia';
import api from '../api/client';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token') || null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        roles: (state) => state.user?.roles || [],
        permissions: (state) => state.user?.permissions || [],
        primaryRole: (state) => state.user?.roles?.[0] || 'GUEST',
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            try {
                const response = await api.post('/login', { email, password });
                const { token, user } = response.data.data;

                this.token = token;
                this.user = user;

                localStorage.setItem('token', token);
                localStorage.setItem('user', JSON.stringify(user));

                return response.data;
            } finally {
                this.loading = false;
            }
        },

        async demoLogin(roleEmail) {
            return this.login(roleEmail, 'password');
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/logout');
                }
            } catch (e) {
                // ignore
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        },

        async fetchUser() {
            if (!this.token) return null;
            try {
                const response = await api.get('/me');
                this.user = response.data.data;
                localStorage.setItem('user', JSON.stringify(this.user));
                return this.user;
            } catch (e) {
                this.logout();
                return null;
            }
        },

        hasRole(roleNames) {
            if (!this.user || !this.user.roles) return false;
            if (this.user.roles.includes('ADMIN')) return true;

            const checking = Array.isArray(roleNames) ? roleNames : [roleNames];
            return checking.some((r) => this.user.roles.includes(r));
        },

        hasPermission(permName) {
            if (!this.user) return false;
            if (this.hasRole('ADMIN')) return true;
            return this.permissions.includes(permName);
        },
    },
});
