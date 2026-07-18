import 'bootstrap';

import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

window.axios.interceptors.request.use(
    (config) => {
        if (config.headers.Authorization) {
            return config;
        }

        const url = config.url || '';

        if (url.startsWith('/api/v1/auth/login')) {
            return config;
        }

        if (url.startsWith('/api/v1/')) {
            const posToken = localStorage.getItem('pos_token');
            if (posToken) {
                config.headers.Authorization = `Bearer ${posToken}`;
            }
            return config;
        }

        if (url.startsWith('/api/') && !url.startsWith('/api/v1/')) {
            const publicPaths = [
                '/api/auth/login',
                '/api/auth/register',
                '/api/bootstrap',
                '/api/reviews',
            ];
            const isPublic = publicPaths.some((path) => url.startsWith(path));

            if (!isPublic) {
                const clientToken = localStorage.getItem('auth_token');
                if (clientToken) {
                    config.headers.Authorization = `Bearer ${clientToken}`;
                }
            }
        }

        return config;
    },
    (error) => Promise.reject(error)
);
