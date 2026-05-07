<template>
    <main class="app-shell">
        <AppHeader :authenticated="isAuthenticated" @logout="logout" />

        <section class="hero-grid">
            <article class="hero-panel glass">
                <p class="eyebrow">Digital patient portal</p>
                <h1>Fast access to lab results, grouped and secure.</h1>
                <p class="hero-copy">
                    Sign in with the patient name login and birth date password, then browse orders and results in a
                    clean, responsive dashboard.
                </p>

                <div class="hero-badges">
                    <span>JWT auth</span>
                    <span>CSV import</span>
                    <span>LocalStorage session</span>
                </div>
            </article>

            <aside class="info-panel glass">
                <p class="section-label">How it works</p>
                <ol>
                    <li>Import the CSV with `php artisan results:import`.</li>
                    <li>Seed demo data with `php artisan migrate --seed`.</li>
                    <li>Log in and open the patient dashboard.</li>
                </ol>
            </aside>
        </section>

        <section class="content-grid">
            <LoginCard
                v-if="!isAuthenticated"
                v-model:login="login"
                v-model:password="password"
                :loading="loading"
                :error="error"
                @submit="submitLogin"
            />

            <ResultsPanel
                v-else
                :patient="patient"
                :orders="orders"
                :loading="loading"
                :error="error"
                :order-count="orderCount"
                :result-count="resultCount"
            />
        </section>
    </main>
</template>

<script setup>
import AppHeader from './components/AppHeader.vue';
import LoginCard from './components/LoginCard.vue';
import ResultsPanel from './components/ResultsPanel.vue';
import { usePatientResults } from './composables/usePatientResults';

const {
    login,
    password,
    loading,
    error,
    patient,
    orders,
    isAuthenticated,
    orderCount,
    resultCount,
    submitLogin,
    logout,
} = usePatientResults();
</script>
