<script setup lang="ts">
import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { adminApi } from '../../api/admin'
import type { AdminUser } from '../../types/admin'

const queryClient = useQueryClient()
const page = ref(1)

const { data: users, isPending } = useQuery({
  queryKey: ['admin-users', page],
  queryFn: () => adminApi.users.list(page.value),
})

const editingUser = ref<AdminUser | null>(null)
const editUsername = ref('')

function startEdit(user: AdminUser): void {
  editingUser.value = user
  editUsername.value = user.username ?? ''
}

function cancelEdit(): void {
  editingUser.value = null
}

const { mutate: updateUser, isPending: updating } = useMutation({
  mutationFn: ({ userId, username }: { userId: string; username: string }) =>
    adminApi.users.update(userId, { username }),
  onSuccess: () => {
    void queryClient.invalidateQueries({ queryKey: ['admin-users'] })
    editingUser.value = null
  },
})

const { mutate: deleteUser } = useMutation({
  mutationFn: (userId: string) => adminApi.users.delete(userId),
  onSuccess: () => {
    void queryClient.invalidateQueries({ queryKey: ['admin-users'] })
  },
})

function confirmDelete(user: AdminUser): void {
  if (confirm(`Delete user ${user.email}? This cannot be undone.`)) {
    deleteUser(user.id)
  }
}

function roleColor(role: string): string {
  switch (role) {
    case 'ROLE_ADMIN': return 'text-amber-400 bg-amber-500/10 border-amber-500/30'
    case 'ROLE_OWNER': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30'
    default: return 'text-blue-400 bg-blue-500/10 border-blue-500/30'
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="sticky top-0 z-40 flex items-center justify-between px-6 py-3 bg-zinc-900/90 backdrop-blur border-b border-amber-500/30">
      <div class="flex items-center gap-3">
        <RouterLink
          to="/admin"
          class="text-zinc-400 hover:text-zinc-100 text-sm transition"
        >
          ← Logs
        </RouterLink>
        <span class="text-zinc-600">|</span>
        <span class="font-bold text-amber-400 text-sm">🛡️ Users</span>
        <span class="rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400">ADMIN</span>
      </div>
      <nav class="flex items-center gap-1 text-sm">
        <RouterLink
          to="/admin/cats"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
        >
          Cats
        </RouterLink>
        <RouterLink
          to="/admin/pet-sitters"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
        >
          Pet Sitters
        </RouterLink>
      </nav>
    </header>

    <div class="mx-auto max-w-6xl px-6 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">
          User Management
        </h1>
        <p class="text-sm text-zinc-400 mt-1">
          All registered users · {{ users?.total ?? 0 }} total
        </p>
      </div>

      <div
        v-if="isPending"
        class="text-center py-12 text-zinc-500"
      >
        Loading…
      </div>
      <div
        v-else
        class="overflow-hidden rounded-xl border border-zinc-700"
      >
        <table class="w-full text-sm">
          <thead class="border-b border-zinc-700 bg-zinc-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">
                Email
              </th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">
                Username
              </th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">
                Role
              </th>
              <th class="px-4 py-3 text-left text-xs text-zinc-400 font-medium">
                Created
              </th>
              <th class="px-4 py-3 text-right text-xs text-zinc-400 font-medium">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-800">
            <tr
              v-for="user in users?.items"
              :key="user.id"
              class="bg-zinc-900 hover:bg-zinc-800/50 transition"
            >
              <td class="px-4 py-3 font-mono text-xs text-zinc-300">
                {{ user.email }}
              </td>
              <td class="px-4 py-3">
                <template v-if="editingUser?.id === user.id">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="editUsername"
                      type="text"
                      class="rounded border border-zinc-600 bg-zinc-800 px-2 py-1 text-xs text-zinc-100 focus:border-amber-500 focus:outline-none w-32"
                    >
                    <button
                      :disabled="updating"
                      class="text-xs text-amber-400 hover:text-amber-300 disabled:opacity-50"
                      @click="updateUser({ userId: user.id, username: editUsername })"
                    >
                      Save
                    </button>
                    <button
                      class="text-xs text-zinc-500 hover:text-zinc-300"
                      @click="cancelEdit"
                    >
                      Cancel
                    </button>
                  </div>
                </template>
                <template v-else>
                  <span class="text-zinc-300">{{ user.username ?? '—' }}</span>
                </template>
              </td>
              <td class="px-4 py-3">
                <span
                  class="rounded border px-1.5 py-0.5 text-xs font-medium"
                  :class="roleColor(user.role)"
                >
                  {{ user.role.replace('ROLE_', '') }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-zinc-500">
                {{ new Date(user.createdAt).toLocaleDateString() }}
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="user.role !== 'ROLE_ADMIN'"
                    class="text-xs text-zinc-400 hover:text-zinc-100 transition"
                    @click="startEdit(user)"
                  >
                    Edit
                  </button>
                  <button
                    v-if="user.role !== 'ROLE_ADMIN'"
                    class="text-xs text-red-400 hover:text-red-300 transition"
                    @click="confirmDelete(user)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="users && users.totalPages > 1"
        class="mt-4 flex justify-center gap-3"
      >
        <button
          :disabled="page <= 1"
          class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition"
          @click="page--"
        >
          ← Prev
        </button>
        <span class="py-1.5 text-sm text-zinc-500">{{ page }} / {{ users.totalPages }}</span>
        <button
          :disabled="page >= users.totalPages"
          class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 transition"
          @click="page++"
        >
          Next →
        </button>
      </div>
    </div>
  </div>
</template>
