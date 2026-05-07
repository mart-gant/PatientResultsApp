<template>
    <article class="card results-card">
        <div class="results-topline">
            <div>
                <p class="section-label">Results</p>
                <h3>Patient results overview</h3>
            </div>

            <div class="stats">
                <div class="stat">
                    <span>Orders</span>
                    <strong>{{ orderCount }}</strong>
                </div>
                <div class="stat">
                    <span>Results</span>
                    <strong>{{ resultCount }}</strong>
                </div>
            </div>
        </div>

        <div v-if="loading" class="status-box">Loading results...</div>
        <template v-else>
            <PatientSummary v-if="patient" :patient="patient" />

            <div v-if="orders.length" class="orders-stack">
                <OrderCard v-for="order in orders" :key="order.orderId" :order="order" />
            </div>

            <p v-if="error" class="error">{{ error }}</p>
        </template>
    </article>
</template>

<script setup>
import OrderCard from './OrderCard.vue';
import PatientSummary from './PatientSummary.vue';

defineProps({
    patient: {
        type: Object,
        default: null,
    },
    orders: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    orderCount: {
        type: Number,
        default: 0,
    },
    resultCount: {
        type: Number,
        default: 0,
    },
});
</script>
