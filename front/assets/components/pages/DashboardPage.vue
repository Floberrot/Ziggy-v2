<script setup lang="ts">
import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { authApi, type MeResponse } from '../../api/auth'
import { useAuthStore } from '../../stores/useAuthStore'
import MainTemplate from '../templates/MainTemplate.vue'

const authStore = useAuthStore()

const { data: me } = useQuery({
  queryKey: ['me'],
  queryFn: () => authApi.me(),
  enabled: authStore.isAuthenticated,
})

const sections: { emoji: string; title: string; description: string; href: string; roles: MeResponse['role'][] }[] = [
  {
    emoji: '🐾',
    title: 'My Cats',
    description: 'Add and manage your cats.',
    href: '/cats',
    roles: ['ROLE_OWNER', 'ROLE_PET_SITTER', 'ROLE_ADMIN'],
  },
  {
    emoji: '🎨',
    title: 'Chip Types',
    description: 'Create labels for your calendar.',
    href: '/chip-types',
    roles: ['ROLE_OWNER', 'ROLE_ADMIN'],
  },
  {
    emoji: '✉️',
    title: 'Pet Sitters',
    description: 'Invite helpers to care for your cats.',
    href: '/pet-sitters',
    roles: ['ROLE_OWNER', 'ROLE_ADMIN'],
  },
  {
    emoji: '👤',
    title: 'My Profile',
    description: 'View your stats and update personal info.',
    href: '/profile',
    roles: ['ROLE_OWNER', 'ROLE_ADMIN'],
  },
]

const visibleSections = computed(() => {
  const role = me.value?.role
  if (!role) return []
  return sections.filter(s => s.roles.includes(role))
})
</script>

<template>
  <MainTemplate>
    <div class="max-w-4xl mx-auto px-6 py-12">
      <!-- Ambient -->
      <div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
        <div class="absolute top-0 right-0 w-[500px] h-[400px] bg-rose-500/5 rounded-full blur-[120px]" />
      </div>

      <div class="mb-10">
        <h1 class="text-3xl font-bold text-[var(--text)]">
          Welcome<span v-if="me?.username">, {{ me.username }}</span> 🐱
        </h1>
        <p class="text-[var(--text-2)] mt-1">What would you like to manage today?</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <RouterLink
          v-for="section in visibleSections"
          :key="section.href"
          :to="section.href"
          class="group bg-[var(--surface)] rounded-2xl p-6 border border-[var(--border)] hover:border-rose-500/30 hover:bg-[var(--surface-2)] hover:shadow-lg hover:shadow-rose-500/5 transition-all duration-200 cursor-pointer"
        >
          <div class="text-3xl mb-3">{{ section.emoji }}</div>
          <h2 class="font-semibold text-[var(--text)] group-hover:text-rose-400 transition-colors">{{ section.title }}</h2>
          <p class="text-sm text-[var(--text-3)] mt-1">{{ section.description }}</p>
        </RouterLink>
      </div>
    </div>
  </MainTemplate>
</template>
