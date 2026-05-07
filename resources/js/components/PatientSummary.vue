<template>
    <section class="patient-summary">
        <div class="summary-card accent">
            <span>Patient</span>
            <strong>{{ patient.name }} {{ patient.surname }}</strong>
        </div>

        <div class="summary-card">
            <span>ID</span>
            <strong>{{ patient.id }}</strong>
        </div>

        <div class="summary-card">
            <span>Sex</span>
            <strong>{{ patient.sex }}</strong>
        </div>

        <div class="summary-card">
            <span>Birth date</span>
            <strong>{{ patient.birthDate }}</strong>
        </div>
    </section>
</template>

<script setup>
defineProps({
    patient: {
        type: Object,
        required: true,
    },
});
</script>
