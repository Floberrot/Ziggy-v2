<script setup lang="ts">
import { useMutation } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import BaseInput from '../atoms/BaseInput.vue'
import AuthForm from '../molecules/AuthForm.vue'

const route = useRoute()
const router = useRouter()

const token = computed(() => String(route.query.token ?? ''))
const password = ref('')
const passwordConfirm = ref('')
const errorMessage = ref<string | null>(null)

const { mutate, isPending } = useMutation({
  mutationFn: () => authApi.resetPassword({ token: token.value, password: password.value }),
  onSuccess: () => {
    router.push({ name: 'login', query: { reset: '1' } })
  },
  onError: (err) => {
    errorMessage.value = err instanceof Error ? err.message : 'Something went wrong.'
  },
})

function handleSubmit(): void {
  errorMessage.value = null

  if (!token.value) {
    errorMessage.value = 'Invalid or missing reset token.'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.'
    return
  }

  if (password.value !== passwordConfirm.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  mutate()
}
</script>

<template>
  <AuthForm
    title="Set a new password"
    subtitle="Choose a strong password for your account"
    submit-label="Reset password"
    :loading="isPending"
    :error="errorMessage ?? undefined"
    @submit="handleSubmit"
  >
    <BaseInput
      v-model="password"
      type="password"
      label="New password"
      placeholder="At least 8 characters"
      :disabled="isPending"
    />
    <BaseInput
      v-model="passwordConfirm"
      type="password"
      label="Confirm new password"
      placeholder="Repeat your password"
      :disabled="isPending"
    />

    <template #footer>
      <RouterLink
        to="/login"
        class="text-rose-400 font-semibold hover:text-rose-300 hover:underline"
      >
        Back to sign in
      </RouterLink>
    </template>
  </AuthForm>
</template>
