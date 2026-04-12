<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { adminApi, setAdminToken } from '../../api/admin'

const router = useRouter()

const email = ref('')
const password = ref('')
const adminSecret = ref('')
const error = ref<string | null>(null)
const loading = ref(false)

async function handleLogin(): Promise<void> {
  error.value = null
  loading.value = true

  try {
    const response = await adminApi.login(email.value, password.value, adminSecret.value)
    setAdminToken(response.token)
    await router.push({ name: 'admin-dashboard' })
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Login failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Admin warning banner -->
      <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-amber-400">
        <span class="text-xl">⚠️</span>
        <div>
          <p class="font-semibold text-sm">
            Restricted Area — Admin Access
          </p>
          <p class="text-xs text-amber-400/70 mt-0.5">
            This panel is for administrators only. All actions are logged.
          </p>
        </div>
      </div>

      <div class="rounded-2xl border border-zinc-700 bg-zinc-900 p-8 shadow-2xl">
        <div class="mb-6 text-center">
          <span class="text-4xl">🛡️</span>
          <h1 class="mt-2 text-xl font-bold text-zinc-100">
            Admin Login
          </h1>
          <p class="text-sm text-zinc-400 mt-1">
            Ziggy Administration Panel
          </p>
        </div>

        <form
          class="space-y-4"
          @submit.prevent="handleLogin"
        >
          <div>
            <label class="block text-xs font-medium text-zinc-400 mb-1">Email</label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="admin@example.com"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500/40"
            >
          </div>

          <div>
            <label class="block text-xs font-medium text-zinc-400 mb-1">Password</label>
            <input
              v-model="password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500/40"
            >
          </div>

          <div>
            <label class="block text-xs font-medium text-zinc-400 mb-1">
              Admin Secret
              <span class="ml-1 text-zinc-500">(second factor)</span>
            </label>
            <input
              v-model="adminSecret"
              type="password"
              required
              placeholder="••••••••••••"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500/40"
            >
          </div>

          <div
            v-if="error"
            class="rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm text-red-400"
          >
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Authenticating…' : 'Sign in to Admin' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
