<script setup lang="ts">
import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { adminApi } from '../../api/admin'
import type { AdminPetSitter } from '../../types/admin'

const queryClient = useQueryClient()
const page = ref(1)

const { data: petSitters, isPending } = useQuery({
  queryKey: ['admin-pet-sitters', page],
  queryFn: () => adminApi.petSitters.list(page.value),
})

const { mutate: deletePetSitter } = useMutation({
  mutationFn: (id: string) => adminApi.petSitters.delete(id),
  onSuccess: () => {
    void queryClient.invalidateQueries({ queryKey: ['admin-pet-sitters'] })
  },
})

function confirmDelete(ps: AdminPetSitter): void {
  if (confirm(`Delete pet sitter ${ps.inviteeEmail}? This cannot be undone.`)) {
    deletePetSitter(ps.id)
  }
}

function typeLabel(type: string): string {
  switch (type) {
    case 'family': return '👨‍👩‍👧 Family'
    case 'friend': return '👫 Friend'
    case 'professional': return '💼 Professional'
    default: return type
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="sticky top-0 z-40 flex items-center justify-between px-6 py-3 bg-zinc-900/90 backdrop-blur border-b border-amber-500/30">
      <div class="flex items-center gap-3">
        <RouterLink to="/admin" class="text-zinc-400 hover:text-zinc-100 text-sm transition">← Logs</RouterLink>
        <span class="text-zinc-600">|</span>
        <span class="font-bold text-amber-400 text-sm">🛡️ Pet Sitters</span>
        <span class="rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400">ADMIN</span>
      </div>
      <nav class="flex items-center gap-1 text-sm">
        <RouterLink to="/admin/users" class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition">Users</RouterLink>
        <RouterLink to="/admin/cats" class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition">Cats</RouterLink>
      </nav>
    </header>

    <div class="mx-auto max-w-6xl px-6 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">All Pet Sitters</h1>
        <p class="text-sm text-zinc-400 mt-1">Every pet sitter across all owners · {{ petSitters?.total ?? 0 }} total</p>
      </div>

      <div v-if="isPending" class="text-center py-12 text-zinc-500">Loading…</div>
      <div v-else class="overflow-hidden rounded-xl border border-zinc-700">
        <table class="w-full text-sm">
          <thead class="border-b border-zinc-700 bg-zinc-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">Invitee Email</th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">Type</th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">Owner</th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">Status</th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">Created</th>
              <th class="px-4 py-3 text-right text-xs text-zinc-400 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-800">
            <tr v-for="ps in petSitters?.items" :key="ps.id" class="bg-zinc-900 hover:bg-zinc-800/50 transition">
              <td class="px-4 py-3 font-mono text-xs text-zinc-300">{{ ps.inviteeEmail }}</td>
              <td class="px-4 py-3 text-xs text-zinc-400">{{ typeLabel(ps.type) }}</td>
              <td class="px-4 py-3">
                <!-- Amber highlight = admin-visible owner info -->
                <div class="rounded border border-amber-500/20 bg-amber-500/5 px-2 py-1 text-xs inline-block">
                  <span class="text-amber-300 font-mono">{{ ps.ownerEmail ?? ps.ownerId }}</span>
                  <span v-if="ps.ownerUsername" class="text-amber-400/60 ml-1">({{ ps.ownerUsername }})</span>
                </div>
              </td>
              <td class="px-4 py-3 text-xs">
                <span v-if="ps.userId" class="text-emerald-400">Accepted</span>
                <span v-else class="text-zinc-500">Pending</span>
              </td>
              <td class="px-4 py-3 text-xs text-zinc-500">{{ new Date(ps.createdAt).toLocaleDateString() }}</td>
              <td class="px-4 py-3 text-right">
                <button class="text-xs text-red-400 hover:text-red-300 transition" @click="confirmDelete(ps)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!petSitters?.items.length" class="py-12 text-center text-zinc-500">No pet sitters found.</div>
      </div>

      <!-- Pagination -->
      <div v-if="petSitters && petSitters.totalPages > 1" class="mt-4 flex justify-center gap-3">
        <button :disabled="page <= 1" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition" @click="page--">← Prev</button>
        <span class="py-1.5 text-sm text-zinc-500">{{ page }} / {{ petSitters.totalPages }}</span>
        <button :disabled="page >= petSitters.totalPages" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition" @click="page++">Next →</button>
      </div>
    </div>
  </div>
</template>
