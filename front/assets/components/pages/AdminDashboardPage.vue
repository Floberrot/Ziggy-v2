<script setup lang="ts">
import { ref } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { adminApi, clearAdminToken } from '../../api/admin'
import type { AdminLog, AdminLogFilters } from '../../types/admin'

const router = useRouter()

const filters = ref<AdminLogFilters>({
  page: 1,
  limit: 50,
  userId: undefined,
  logLevel: undefined,
  search: undefined,
})

const searchInput = ref('')
const userIdInput = ref('')
const logLevelInput = ref('')

function applyFilters(): void {
  filters.value = {
    page: 1,
    limit: 50,
    userId: userIdInput.value || undefined,
    logLevel: logLevelInput.value || undefined,
    search: searchInput.value || undefined,
  }
}

function clearFilters(): void {
  searchInput.value = ''
  userIdInput.value = ''
  logLevelInput.value = ''
  filters.value = { page: 1, limit: 50 }
}

const { data: logs, isPending } = useQuery({
  queryKey: ['admin-logs', filters],
  queryFn: () => adminApi.logs.list(filters.value),
})

const expandedLogId = ref<string | null>(null)

function toggleTrace(id: string): void {
  expandedLogId.value = expandedLogId.value === id ? null : id
}

function prevPage(): void {
  if ((filters.value.page ?? 1) > 1) {
    filters.value = { ...filters.value, page: (filters.value.page ?? 1) - 1 }
  }
}

function nextPage(): void {
  if ((filters.value.page ?? 1) < (logs.value?.totalPages ?? 1)) {
    filters.value = { ...filters.value, page: (filters.value.page ?? 1) + 1 }
  }
}

function logLevelColor(level: AdminLog['logLevel']): string {
  switch (level) {
    case 'error': return 'text-red-400 bg-red-500/10 border-red-500/30'
    case 'warning': return 'text-amber-400 bg-amber-500/10 border-amber-500/30'
    default: return 'text-blue-400 bg-blue-500/10 border-blue-500/30'
  }
}

function logout(): void {
  clearAdminToken()
  void router.push({ name: 'admin-login' })
}
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <!-- Admin header -->
    <header class="sticky top-0 z-40 flex items-center justify-between px-6 py-3 bg-zinc-900/90 backdrop-blur border-b border-amber-500/30">
      <div class="flex items-center gap-3">
        <span class="text-xl">🛡️</span>
        <span class="font-bold text-amber-400">Ziggy Admin</span>
        <span class="rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400 font-medium">ADMIN MODE</span>
      </div>
      <nav class="flex items-center gap-1 text-sm">
        <RouterLink
          to="/admin"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >
          Logs
        </RouterLink>
        <RouterLink
          to="/admin/users"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >
          Users
        </RouterLink>
        <RouterLink
          to="/admin/cats"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >
          Cats
        </RouterLink>
        <RouterLink
          to="/admin/pet-sitters"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >
          Pet Sitters
        </RouterLink>
        <button
          class="ml-3 rounded-lg px-3 py-1.5 text-xs text-red-400 border border-red-500/30 hover:bg-red-500/10 transition"
          @click="logout"
        >
          Sign out
        </button>
      </nav>
    </header>

    <div class="mx-auto max-w-7xl px-6 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">
          Error Logs
        </h1>
        <p class="text-sm text-zinc-400 mt-1">
          HTTP 4xx and 5xx errors captured from the API
        </p>
      </div>

      <!-- Filters -->
      <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-zinc-700 bg-zinc-900 p-4">
        <div class="flex-1 min-w-48">
          <label class="block text-xs text-zinc-400 mb-1">Search (path or message)</label>
          <input
            v-model="searchInput"
            type="text"
            placeholder="/api/cats or 'not found'"
            class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
          >
        </div>
        <div class="w-48">
          <label class="block text-xs text-zinc-400 mb-1">User ID / Email</label>
          <input
            v-model="userIdInput"
            type="text"
            placeholder="user@example.com"
            class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
          >
        </div>
        <div class="w-40">
          <label class="block text-xs text-zinc-400 mb-1">Log Level</label>
          <select
            v-model="logLevelInput"
            class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 focus:border-amber-500 focus:outline-none"
          >
            <option value="">
              All levels
            </option>
            <option value="error">
              Error (5xx)
            </option>
            <option value="warning">
              Warning (4xx)
            </option>
            <option value="info">
              Info
            </option>
          </select>
        </div>
        <div class="flex gap-2">
          <button
            class="rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-zinc-950 hover:bg-amber-400 transition"
            @click="applyFilters"
          >
            Apply
          </button>
          <button
            class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 transition"
            @click="clearFilters"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="mb-4 flex items-center justify-between text-sm text-zinc-400">
        <span>{{ logs?.total ?? 0 }} entries found</span>
        <span v-if="logs">Page {{ logs.page }} / {{ logs.totalPages }}</span>
      </div>

      <!-- Table -->
      <div
        v-if="isPending"
        class="text-center py-12 text-zinc-500"
      >
        Loading logs…
      </div>
      <div
        v-else-if="!logs?.items.length"
        class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500"
      >
        No logs found.
      </div>
      <div
        v-else
        class="space-y-2"
      >
        <div
          v-for="log in logs.items"
          :key="log.id"
          class="rounded-xl border bg-zinc-900 overflow-hidden"
          :class="log.logLevel === 'error' ? 'border-red-500/30' : 'border-amber-500/30'"
        >
          <div
            class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-zinc-800/50 transition"
            @click="toggleTrace(log.id)"
          >
            <span
              class="shrink-0 rounded border px-1.5 py-0.5 text-xs font-mono font-bold"
              :class="logLevelColor(log.logLevel)"
            >{{ log.statusCode }}</span>
            <span class="shrink-0 text-xs text-zinc-500 font-mono w-12">{{ log.method }}</span>
            <span class="flex-1 truncate text-sm font-mono text-zinc-300">{{ log.path }}</span>
            <span class="shrink-0 text-xs text-zinc-500 truncate max-w-48">{{ log.message }}</span>
            <span class="shrink-0 text-xs text-zinc-600 ml-2">{{ new Date(log.createdAt).toLocaleString() }}</span>
            <span class="shrink-0 text-xs text-zinc-600">{{ expandedLogId === log.id ? '▲' : '▼' }}</span>
          </div>
          <div
            v-if="expandedLogId === log.id"
            class="border-t border-zinc-700 px-4 py-3 bg-zinc-950/50"
          >
            <div class="grid grid-cols-2 gap-2 text-xs text-zinc-400 mb-3">
              <div><span class="text-zinc-600">User:</span> {{ log.userId ?? '—' }}</div>
              <div>
                <span class="text-zinc-600">Log Level:</span>
                <span :class="log.logLevel === 'error' ? 'text-red-400' : 'text-amber-400'">
                  {{ log.logLevel }}
                </span>
              </div>
              <div class="col-span-2">
                <span class="text-zinc-600">Message:</span> {{ log.message }}
              </div>
            </div>
            <div v-if="log.stackTrace">
              <p class="text-xs text-zinc-600 mb-1">
                Stack trace:
              </p>
              <pre class="text-xs text-red-300/80 bg-zinc-950 rounded p-3 overflow-auto max-h-64 whitespace-pre-wrap">{{ log.stackTrace }}</pre>
            </div>
            <div
              v-else
              class="text-xs text-zinc-600 italic"
            >
              No stack trace available.
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="logs && logs.totalPages > 1"
        class="mt-4 flex justify-center gap-3"
      >
        <button
          :disabled="logs.page <= 1"
          class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition"
          @click="prevPage"
        >
          ← Previous
        </button>
        <button
          :disabled="logs.page >= logs.totalPages"
          class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition"
          @click="nextPage"
        >
          Next →
        </button>
      </div>
    </div>
  </div>
</template>
