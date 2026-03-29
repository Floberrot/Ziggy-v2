<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import { useAuthStore } from '../../stores/useAuthStore'
import BaseButton from '../atoms/BaseButton.vue'
import MainTemplate from '../templates/MainTemplate.vue'

const router = useRouter()
const authStore = useAuthStore()

const { data: me } = useQuery({
  queryKey: ['me'],
  queryFn: () => authApi.me(),
  enabled: authStore.isAuthenticated,
})

async function logout(): Promise<void> {
  authStore.logout()
  await router.push('/login')
}

const sections = [
  {
    emoji: '🐾',
    title: 'My Cats',
    description: 'Add and manage your cats.',
    href: '/cats',
  },
  {
    emoji: '🎨',
    title: 'Chip Types',
    description: 'Create labels for your calendar.',
    href: '/chip-types',
  },
  {
    emoji: '✉️',
    title: 'Pet Sitters',
    description: 'Invite helpers to care for your cats.',
    href: '/pet-sitters',
  },
]
</script>

<template>
  <MainTemplate>
    <template #nav>
      <span class="text-sm text-[var(--text-2)]">{{ me?.username ?? me?.email }}</span>
      <BaseButton variant="ghost" size="sm" @click="logout">Sign out</BaseButton>
    </template>

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
          v-for="section in sections"
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
