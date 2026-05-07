<template>
    <header class="app-header glass">
        <div>
            <p class="eyebrow">Patient Results App</p>
            <h2>Patient portal</h2>
        </div>

        <div class="header-actions">
            <span class="status-pill" :class="{ active: authenticated }">
                {{ authenticated ? 'Signed in' : 'Guest mode' }}
            </span>
            <button v-if="authenticated" type="button" class="secondary" @click="$emit('logout')">
                Sign out
            </button>
        </div>
    </header>
</template>

<script setup>
defineProps({
    authenticated: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['logout']);
</script>
