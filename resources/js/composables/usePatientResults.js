import { computed, onMounted, ref } from 'vue';

const storageKey = 'patient-results-token';

const parseJwt = (token) => {
    try {
        const payload = token.split('.')[1];
        return JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/')));
    } catch {
        return null;
    }
};

export function usePatientResults() {
    const login = ref('');
    const password = ref('');
    const loading = ref(false);
    const error = ref('');
    const token = ref(localStorage.getItem(storageKey) || '');
    const patient = ref(null);
    const orders = ref([]);
    const logoutTimer = ref(null);

    const isAuthenticated = computed(() => Boolean(token.value));
    const orderCount = computed(() => orders.value.length);
    const resultCount = computed(() => orders.value.reduce((total, order) => total + order.results.length, 0));

    const clearSession = () => {
        token.value = '';
        patient.value = null;
        orders.value = [];
        localStorage.removeItem(storageKey);

        if (logoutTimer.value) {
            clearTimeout(logoutTimer.value);
            logoutTimer.value = null;
        }
    };

    const scheduleLogout = () => {
        const payload = parseJwt(token.value);
        if (!payload?.exp) {
            return;
        }

        const delay = Math.max(payload.exp * 1000 - Date.now(), 0);

        if (logoutTimer.value) {
            clearTimeout(logoutTimer.value);
        }

        logoutTimer.value = window.setTimeout(() => {
            clearSession();
            error.value = 'Session expired. Please sign in again.';
        }, delay);
    };

    const fetchResults = async () => {
        if (!token.value) {
            return;
        }

        loading.value = true;
        error.value = '';

        try {
            const response = await fetch('/api/results', {
                headers: {
                    Accept: 'application/json',
                    Authorization: `Bearer ${token.value}`,
                },
            });

            if (response.status === 401) {
                clearSession();
                error.value = 'Session expired. Please sign in again.';
                return;
            }

            if (response.status === 404) {
                clearSession();
                error.value = 'No results were found for this patient.';
                return;
            }

            if (!response.ok) {
                throw new Error('Unable to load results.');
            }

            const payload = await response.json();
            patient.value = payload.patient;
            orders.value = payload.orders;
            scheduleLogout();
        } catch (exception) {
            error.value = exception.message || 'Unexpected error.';
        } finally {
            loading.value = false;
        }
    };

    const submitLogin = async () => {
        loading.value = true;
        error.value = '';

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    login: login.value,
                    password: password.value,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Unable to sign in.');
            }

            token.value = payload.token;
            localStorage.setItem(storageKey, payload.token);
            await fetchResults();
        } catch (exception) {
            error.value = exception.message || 'Unexpected error.';
        } finally {
            loading.value = false;
        }
    };

    const logout = () => {
        clearSession();
        error.value = '';
    };

    onMounted(() => {
        if (token.value) {
            fetchResults();
        }
    });

    return {
        login,
        password,
        loading,
        error,
        token,
        patient,
        orders,
        isAuthenticated,
        orderCount,
        resultCount,
        submitLogin,
        logout,
    };
}
