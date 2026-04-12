<script setup lang="ts">
import { useLogout } from '@/composables/useLogout'
import BaseModal from './BaseModal.vue'
import type { MeResponse } from '@/api/auth'

defineProps<{
  open: boolean
  me: MeResponse | undefined
}>()

const emit = defineEmits<{
  close: []
}>()

const { logout } = useLogout()

async function handleLogout(): Promise<void> {
  emit('close')
  await logout()
}
</script>

<template>
  <BaseModal
    title="My Account"
    :open="open"
    @close="emit('close')"
  >
    <div class="flex flex-col gap-5">
      <!-- User identity -->
      <div class="flex items-center gap-3 pb-4 border-b border-[var(--border)]">
        <div class="w-10 h-10 rounded-full bg-rose-500/20 flex items-center justify-center shrink-0">
          <svg
            class="w-5 h-5 text-rose-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
        </div>
        <div class="min-w-0">
          <p
            v-if="me?.username"
            class="font-semibold text-[var(--text)] truncate"
          >
            {{ me.username }}
          </p>
          <p class="text-sm text-[var(--text-3)] truncate">
            {{ me?.email }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col gap-2">
        <RouterLink
          to="/profile"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-[var(--text-2)] hover:bg-[var(--surface-3)] hover:text-[var(--text)] transition-colors"
          @click="emit('close')"
        >
          <svg
            class="w-4 h-4 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zM19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"
            />
          </svg>
          My Profile
        </RouterLink>

        <button
          type="button"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:bg-red-500/10 transition-colors w-full text-left"
          @click="handleLogout"
        >
          <svg
            class="w-4 h-4 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
            />
          </svg>
          Sign out
        </button>
      </div>
    </div>
  </BaseModal>
</template>
