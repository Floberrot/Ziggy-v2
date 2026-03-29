<script setup lang="ts">
import { useMutation } from '@tanstack/vue-query'
import { ref } from 'vue'
import { authApi } from '../../api/auth'
import BaseInput from '../atoms/BaseInput.vue'
import AuthForm from '../molecules/AuthForm.vue'

const email = ref('')
const errorMessage = ref<string | null>(null)
const submitted = ref(false)

const { mutate, isPending } = useMutation({
  mutationFn: () => authApi.requestPasswordReset({ email: email.value }),
  onSuccess: () => {
    submitted.value = true
  },
  onError: (err) => {
    errorMessage.value = err instanceof Error ? err.message : 'Something went wrong.'
  },
})

function handleSubmit(): void {
  errorMessage.value = null
  if (!email.value.trim()) {
    errorMessage.value = 'Email is required.'
    return
  }
  mutate()
}
</script>

<template>
  <AuthForm
    title="Forgot password?"
    :subtitle="submitted ? 'Check your inbox' : 'Enter your email and we\'ll send you a reset link'"
    submit-label="Send reset link"
    :loading="isPending"
    :error="errorMessage ?? undefined"
    @submit="handleSubmit"
  >
    <template v-if="submitted">
      <p class="text-center text-gray-600 text-sm py-2">
        If an account exists for <strong>{{ email }}</strong>, a password reset link has been sent.
        Check your inbox (and spam folder just in case).
      </p>
    </template>
    <template v-else>
      <BaseInput
        v-model="email"
        type="email"
        label="Email"
        placeholder="you@example.com"
        :disabled="isPending"
      />
    </template>

    <template #footer>
      Remembered it?
      <RouterLink to="/login" class="text-rose-400 font-semibold hover:text-rose-300 hover:underline">
        Back to sign in
      </RouterLink>
    </template>
  </AuthForm>
</template>
