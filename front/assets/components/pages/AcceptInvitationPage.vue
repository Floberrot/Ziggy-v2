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
const username = ref('')
const password = ref('')
const passwordConfirm = ref('')
const errorMessage = ref<string | null>(null)

const { mutate, isPending } = useMutation({
  mutationFn: () => authApi.acceptInvitation({ token: token.value, password: password.value, username: username.value }),
  onSuccess: async () => {
    await router.push('/login?invited=1')
  },
})

function handleSubmit(): void {
  errorMessage.value = null

  if (!token.value) {
    errorMessage.value = 'Invalid or missing invitation token.'
    return
  }

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
      errorMessage.value = err instanceof Error ? err.message : 'Could not accept invitation.'
    },
  })
}
</script>

<template>
  <AuthForm
    title="Accept your invitation"
    subtitle="Set a password to activate your pet sitter account"
    submit-label="Activate account"
    :loading="isPending"
    :error="errorMessage ?? undefined"
    @submit="handleSubmit"
  >
    <BaseInput
      v-model="username"
      label="Username"
      placeholder="Choose a username"
      :disabled="isPending"
    />
    <BaseInput
      v-model="password"
      type="password"
      label="Choose a password"
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
      <RouterLink to="/login" class="text-rose-400 font-semibold hover:text-rose-300 hover:underline">
        Sign in
      </RouterLink>
    </template>
  </AuthForm>
</template>
