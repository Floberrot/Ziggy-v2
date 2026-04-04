<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { authApi } from '@/api/auth'
import { useAuthStore } from '@/stores/useAuthStore'
import BaseThemeToggle from '@/components/atoms/BaseThemeToggle.vue'
import ProfileMenuModal from '@/components/molecules/ProfileMenuModal.vue'

const authStore = useAuthStore()
const route = useRoute()
const profileOpen = ref(false)

const { data: me } = useQuery({
  queryKey: ['me'],
  queryFn: () => authApi.me(),
  enabled: authStore.isAuthenticated,
})

const isOwner = () =>
  me.value?.role === 'ROLE_OWNER' || me.value?.role === 'ROLE_ADMIN'

function isActive(path: string): boolean {
  return route.path === path || route.path.startsWith(path + '/')
}
</script>

<template>
  <nav v-if="authStore.isAuthenticated" class="flex items-center gap-1">
    <!-- Dashboard -->
    <RouterLink
      to="/dashboard"
      :title="'Dashboard'"
      :class="[
        'p-2 rounded-xl transition-colors',
        isActive('/dashboard')
          ? 'text-rose-400 bg-rose-500/10'
          : 'text-[var(--text-3)] hover:text-[var(--text-2)] hover:bg-[var(--surface-3)]',
      ]"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
      </svg>
    </RouterLink>

    <!-- My Cats -->
    <RouterLink
      to="/cats"
      :title="'My Cats'"
      :class="[
        'p-2 rounded-xl transition-colors',
        isActive('/cats')
          ? 'text-rose-400 bg-rose-500/10'
          : 'text-[var(--text-3)] hover:text-[var(--text-2)] hover:bg-[var(--surface-3)]',
      ]"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
    </RouterLink>

    <!-- Chip Types — owners only -->
    <RouterLink
      v-if="isOwner()"
      to="/chip-types"
      :title="'Chip Types'"
      :class="[
        'p-2 rounded-xl transition-colors',
        isActive('/chip-types')
          ? 'text-rose-400 bg-rose-500/10'
          : 'text-[var(--text-3)] hover:text-[var(--text-2)] hover:bg-[var(--surface-3)]',
      ]"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
      </svg>
    </RouterLink>

    <!-- Pet Sitters — owners only -->
    <RouterLink
      v-if="isOwner()"
      to="/pet-sitters"
      :title="'Pet Sitters'"
      :class="[
        'p-2 rounded-xl transition-colors',
        isActive('/pet-sitters')
          ? 'text-rose-400 bg-rose-500/10'
          : 'text-[var(--text-3)] hover:text-[var(--text-2)] hover:bg-[var(--surface-3)]',
      ]"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
    </RouterLink>

    <div class="w-px h-5 bg-[var(--border)] mx-1" />

    <!-- Theme toggle -->
    <BaseThemeToggle />

    <!-- Profile button -->
    <button
      type="button"
      title="My Account"
      :class="[
        'p-2 rounded-xl transition-colors',
        profileOpen
          ? 'text-rose-400 bg-rose-500/10'
          : 'text-[var(--text-3)] hover:text-[var(--text-2)] hover:bg-[var(--surface-3)]',
      ]"
      @click="profileOpen = true"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    </button>
  </nav>

  <!-- Unauthenticated: only theme toggle -->
  <div v-else class="flex items-center">
    <BaseThemeToggle />
  </div>

  <ProfileMenuModal
    :open="profileOpen"
    :me="me"
    @close="profileOpen = false"
  />
</template>
