<template>
    <article class="order-card">
        <div class="order-header">
            <div>
                <p class="section-label">Order</p>
                <h4>#{{ order.orderId }}</h4>
            </div>
            <span class="result-count">{{ order.results.length }} results</span>
        </div>

        <ul class="result-list">
            <li v-for="result in order.results" :key="result.name + result.value + result.reference" class="result-row">
                <div class="result-meta">
                    <strong>{{ result.name }}</strong>
                    <span>{{ result.reference || 'No reference range' }}</span>
                </div>
                <code>{{ result.value }}</code>
            </li>
        </ul>
    </article>
</template>

<script setup>
defineProps({
    order: {
        type: Object,
        required: true,
    },
});
</script>
