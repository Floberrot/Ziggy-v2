<script setup lang="ts">
import ToastContainer from './components/organisms/ToastContainer.vue'
import { useAuthStore } from './stores/useAuthStore'

const authStore = useAuthStore()
</script>

<template>
  <RouterView />
  <ToastContainer />
  <div
    v-if="authStore.sessionExpired"
    class="fixed inset-0 z-[9999] cursor-not-allowed bg-black/40"
  />
</template>
