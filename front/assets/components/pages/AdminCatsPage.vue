<script setup lang="ts">
import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { adminApi } from '../../api/admin'
import type { AdminCat } from '../../types/admin'

const queryClient = useQueryClient()
const page = ref(1)

const { data: cats, isPending } = useQuery({
  queryKey: ['admin-cats', page],
  queryFn: () => adminApi.cats.list(page.value),
})

const { mutate: deleteCat } = useMutation({
  mutationFn: (catId: string) => adminApi.cats.delete(catId),
  onSuccess: () => {
    void queryClient.invalidateQueries({ queryKey: ['admin-cats'] })
  },
})

function confirmDelete(cat: AdminCat): void {
  if (confirm(`Delete cat "${cat.name}" (owner: ${cat.ownerEmail ?? cat.ownerId})? This cannot be undone.`)) {
    deleteCat(cat.id)
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="sticky top-0 z-40 flex items-center justify-between px-6 py-3 bg-zinc-900/90 backdrop-blur border-b border-amber-500/30">
      <div class="flex items-center gap-3">
        <RouterLink to="/admin" class="text-zinc-400 hover:text-zinc-100 text-sm transition">← Logs</RouterLink>
        <span class="text-zinc-600">|</span>
        <span class="font-bold text-amber-400 text-sm">🛡️ Cats</span>
        <span class="rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400">ADMIN</span>
      </div>
      <nav class="flex items-center gap-1 text-sm">
        <RouterLink to="/admin/users" class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition">Users</RouterLink>
        <RouterLink to="/admin/pet-sitters" class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition">Pet Sitters</RouterLink>
      </nav>
    </header>

    <div class="mx-auto max-w-6xl px-6 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">All Cats</h1>
        <p class="text-sm text-zinc-400 mt-1">Every cat across all owners · {{ cats?.total ?? 0 }} total</p>
      </div>

      <div v-if="isPending" class="text-center py-12 text-zinc-500">Loading…</div>
      <div v-else class="grid gap-3">
        <div
          v-for="cat in cats?.items"
          :key="cat.id"
          class="flex items-center gap-4 rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 hover:bg-zinc-800/50 transition"
        >
          <!-- Color swatches -->
          <div class="flex gap-1 shrink-0">
            <div
              v-for="color in cat.colors.slice(0, 3)"
              :key="color"
              class="h-5 w-5 rounded-full border border-zinc-600"
              :style="{ backgroundColor: color }"
            />
          </div>

          <!-- Cat info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-zinc-100">{{ cat.name }}</span>
              <span v-if="cat.breed" class="text-xs text-zinc-500">· {{ cat.breed }}</span>
              <span v-if="cat.weight" class="text-xs text-zinc-500">· {{ cat.weight }} kg</span>
            </div>
          </div>

          <!-- Owner info — amber highlight as admin-only data -->
          <div class="shrink-0 rounded-lg border border-amber-500/30 bg-amber-500/5 px-3 py-1.5 text-xs">
            <p class="text-amber-400/60 font-medium mb-0.5">Owner</p>
            <p class="text-amber-300 font-mono">{{ cat.ownerEmail ?? cat.ownerId }}</p>
            <p v-if="cat.ownerUsername" class="text-amber-400/60">{{ cat.ownerUsername }}</p>
          </div>

          <button
            class="shrink-0 text-xs text-red-400 hover:text-red-300 transition"
            @click="confirmDelete(cat)"
          >Delete</button>
        </div>

        <div v-if="!cats?.items.length" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">
          No cats found.
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="cats && cats.totalPages > 1" class="mt-4 flex justify-center gap-3">
        <button :disabled="page <= 1" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition" @click="page--">← Prev</button>
        <span class="py-1.5 text-sm text-zinc-500">{{ page }} / {{ cats.totalPages }}</span>
        <button :disabled="page >= cats.totalPages" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition" @click="page++">Next →</button>
      </div>
    </div>
  </div>
</template>
