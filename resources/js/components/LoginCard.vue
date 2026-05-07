<template>
    <article class="card login-card">
        <div class="card-copy">
            <p class="section-label">Log in</p>
            <h3>Use patient login and birth date</h3>
            <p>
                Login is the patient's first and last name together, for example
                <strong>PiotrKowalski</strong>.
            </p>
        </div>

        <form class="form" @submit.prevent="$emit('submit')">
            <label>
                Login
                <input
                    :value="login"
                    type="text"
                    placeholder="PiotrKowalski"
                    autocomplete="username"
                    @input="$emit('update:login', $event.target.value)"
                />
            </label>

            <label>
                Password
                <input
                    :value="password"
                    type="password"
                    placeholder="1983-04-12"
                    autocomplete="current-password"
                    @input="$emit('update:password', $event.target.value)"
                />
            </label>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Loading...' : 'Sign in' }}
            </button>
        </form>

        <p v-if="error" class="error">{{ error }}</p>
    </article>
</template>

<script setup>
defineProps({
    login: {
        type: String,
        default: '',
    },
    password: {
        type: String,
        default: '',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
});

defineEmits(['submit', 'update:login', 'update:password']);
</script>
