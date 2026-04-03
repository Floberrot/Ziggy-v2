<script setup lang="ts">
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import { useAuthStore } from '../../stores/useAuthStore'
import BaseInput from '../atoms/BaseInput.vue'
import AuthForm from '../molecules/AuthForm.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const queryClient = useQueryClient()

const email = ref('')
const password = ref('')

const successMessage = computed(() => {
  if (route.query.registered) return 'Account created! You can now sign in.'
  if (route.query.invited) return 'Account activated! You can now sign in.'
  if (route.query.reset) return 'Password reset! You can now sign in with your new password.'
  return null
})

const { mutate, isPending, error } = useMutation({
  mutationFn: () => authApi.login({ email: email.value, password: password.value }),
  onSuccess: async (data) => {
    queryClient.clear()
    authStore.setToken(data.token)
    const me = await authApi.me()
    authStore.setUser(me)
    await router.push('/dashboard')
  },
})

const errorMessage = ref<string | null>(null)

function handleSubmit(): void {
  errorMessage.value = null
  mutate(undefined, {
    onError: (err) => {
      errorMessage.value = err instanceof Error ? err.message : 'Login failed.'
    },
  })
}
</script>

<template>
  <AuthForm
    title="Welcome back"
    subtitle="Sign in to your Ziggy account"
    submit-label="Sign in"
    :loading="isPending"
    :error="errorMessage ?? undefined"
    @submit="handleSubmit"
  >
    <div
      v-if="successMessage"
      class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-sm text-emerald-400"
    >
      {{ successMessage }}
    </div>
    <BaseInput
      v-model="email"
      type="email"
      label="Email"
      placeholder="you@example.com"
      :disabled="isPending"
    />
    <BaseInput
      v-model="password"
      type="password"
      label="Password"
      placeholder="••••••••"
      :disabled="isPending"
    />

    <template #footer>
      <RouterLink to="/forgot-password" class="text-rose-400 hover:text-rose-300 hover:underline">
        Forgot your password?
      </RouterLink>
      <span class="mx-2 text-[var(--text-3)]">·</span>
      No account yet?
      <RouterLink to="/register" class="text-rose-400 font-semibold hover:text-rose-300 hover:underline">
        Create one
      </RouterLink>
    </template>
  </AuthForm>
</template>
