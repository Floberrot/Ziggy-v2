<script setup lang="ts">
import { useMutation } from '@tanstack/vue-query'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import { useAuthStore } from '../../stores/useAuthStore'
import BaseInput from '../atoms/BaseInput.vue'
import AuthForm from '../molecules/AuthForm.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const username = ref('')
const password = ref('')
const passwordConfirm = ref('')
const errorMessage = ref<string | null>(null)

const { mutate, isPending } = useMutation({
  mutationFn: () => authApi.register({ email: email.value, password: password.value, username: username.value }),
  onSuccess: async (data) => {
    authStore.setToken(data.token)
    const me = await authApi.me()
    authStore.setUser(me)
    await router.push('/dashboard')
  },
})

function handleSubmit(): void {
  errorMessage.value = null

  if (username.value.length < 2) {
    errorMessage.value = 'Username must be at least 2 characters.'
    return
  }

  if (password.value !== passwordConfirm.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.'
    return
  }

  mutate(undefined, {
    onError: (err) => {
      errorMessage.value = err instanceof Error ? err.message : 'Registration failed.'
    },
  })
}
</script>

<template>
  <AuthForm
    title="Create your account"
    subtitle="Start managing your cats with Ziggy"
    submit-label="Create account"
    :loading="isPending"
    :error="errorMessage ?? undefined"
    @submit="handleSubmit"
  >
    <BaseInput
      v-model="email"
      type="email"
      label="Email"
      placeholder="you@example.com"
      :disabled="isPending"
    />
    <BaseInput
      v-model="username"
      label="Username"
      placeholder="Choose a username"
      :disabled="isPending"
    />
    <BaseInput
      v-model="password"
      type="password"
      label="Password"
      placeholder="At least 8 characters"
      :disabled="isPending"
    />
    <BaseInput
      v-model="passwordConfirm"
      type="password"
      label="Confirm password"
      placeholder="Repeat your password"
      :disabled="isPending"
    />

    <template #footer>
      Already have an account?
      <RouterLink
        to="/login"
        class="text-rose-400 font-semibold hover:text-rose-300 hover:underline"
      >
        Sign in
      </RouterLink>
    </template>
  </AuthForm>
</template>
