<script setup lang="ts">
import { ref } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { adminApi, clearAdminToken } from '../../api/admin'
import type { ActivityLog, ActivityLogFilters, AdminLog, AdminLogFilters } from '../../types/admin'

const router = useRouter()

// ── Active tab ───────────────────────────────────────────────────────────────
type Tab = 'errors' | 'activity' | 'app-logs' | 'server-logs'
const activeTab = ref<Tab>('errors')

// ── Error logs ───────────────────────────────────────────────────────────────
const errorFilters = ref<AdminLogFilters>({ page: 1, limit: 50 })
const errorSearch = ref('')
const errorUserId = ref('')
const errorLogLevel = ref('')

function applyErrorFilters(): void {
  errorFilters.value = {
    page: 1,
    limit: 50,
    userId: errorUserId.value || undefined,
    logLevel: errorLogLevel.value || undefined,
    search: errorSearch.value || undefined,
  }
}

function clearErrorFilters(): void {
  errorSearch.value = ''
  errorUserId.value = ''
  errorLogLevel.value = ''
  errorFilters.value = { page: 1, limit: 50 }
}

const { data: errorLogs, isPending: errorPending } = useQuery({
  queryKey: ['admin-error-logs', errorFilters],
  queryFn: () => adminApi.logs.list(errorFilters.value),
})

const expandedLogId = ref<string | null>(null)

function toggleTrace(id: string): void {
  expandedLogId.value = expandedLogId.value === id ? null : id
}

function prevErrorPage(): void {
  if ((errorFilters.value.page ?? 1) > 1) {
    errorFilters.value = { ...errorFilters.value, page: (errorFilters.value.page ?? 1) - 1 }
  }
}

function nextErrorPage(): void {
  if ((errorFilters.value.page ?? 1) < (errorLogs.value?.totalPages ?? 1)) {
    errorFilters.value = { ...errorFilters.value, page: (errorFilters.value.page ?? 1) + 1 }
  }
}

function logLevelColor(level: AdminLog['logLevel']): string {
  switch (level) {
    case 'error': return 'text-red-400 bg-red-500/10 border-red-500/30'
    case 'warning': return 'text-amber-400 bg-amber-500/10 border-amber-500/30'
    default: return 'text-blue-400 bg-blue-500/10 border-blue-500/30'
  }
}

// ── Activity logs ─────────────────────────────────────────────────────────────
const activityFilters = ref<ActivityLogFilters>({ page: 1, limit: 50 })
const activitySearch = ref('')
const activityUserId = ref('')
const activityMethod = ref('')

function applyActivityFilters(): void {
  activityFilters.value = {
    page: 1,
    limit: 50,
    userId: activityUserId.value || undefined,
    method: activityMethod.value || undefined,
    search: activitySearch.value || undefined,
  }
}

function clearActivityFilters(): void {
  activitySearch.value = ''
  activityUserId.value = ''
  activityMethod.value = ''
  activityFilters.value = { page: 1, limit: 50 }
}

const { data: activityLogs, isPending: activityPending } = useQuery({
  queryKey: ['admin-activity-logs', activityFilters],
  queryFn: () => adminApi.activityLogs.list(activityFilters.value),
})

function prevActivityPage(): void {
  if ((activityFilters.value.page ?? 1) > 1) {
    activityFilters.value = { ...activityFilters.value, page: (activityFilters.value.page ?? 1) - 1 }
  }
}

function nextActivityPage(): void {
  if ((activityFilters.value.page ?? 1) < (activityLogs.value?.totalPages ?? 1)) {
    activityFilters.value = { ...activityFilters.value, page: (activityFilters.value.page ?? 1) + 1 }
  }
}

function statusColor(code: number): string {
  if (code >= 500) return 'text-red-400 bg-red-500/10 border-red-500/30'
  if (code >= 400) return 'text-amber-400 bg-amber-500/10 border-amber-500/30'
  if (code >= 300) return 'text-purple-400 bg-purple-500/10 border-purple-500/30'
  return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30'
}

function methodColor(method: ActivityLog['method']): string {
  switch (method) {
    case 'GET': return 'text-blue-400'
    case 'POST': return 'text-emerald-400'
    case 'PATCH': return 'text-amber-400'
    case 'PUT': return 'text-orange-400'
    case 'DELETE': return 'text-red-400'
    default: return 'text-zinc-400'
  }
}

// ── App logs ──────────────────────────────────────────────────────────────────
const appLogLines = ref(200)
const { data: appLogs, isPending: appLogsPending, refetch: refetchAppLogs } = useQuery({
  queryKey: ['admin-app-logs', appLogLines],
  queryFn: () => adminApi.logs.appLogs(appLogLines.value),
  enabled: false,
})

function loadAppLogs(): void {
  void refetchAppLogs()
}

// ── Server logs ───────────────────────────────────────────────────────────────
const serverLogLines = ref(200)
const { data: serverLogs, isPending: serverLogsPending, refetch: refetchServerLogs } = useQuery({
  queryKey: ['admin-server-logs', serverLogLines],
  queryFn: () => adminApi.logs.serverLogs(serverLogLines.value),
  enabled: false,
})

function loadServerLogs(): void {
  void refetchServerLogs()
}

// ── Auth ──────────────────────────────────────────────────────────────────────
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
        >Logs</RouterLink>
        <RouterLink
          to="/admin/users"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >Users</RouterLink>
        <RouterLink
          to="/admin/cats"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >Cats</RouterLink>
        <RouterLink
          to="/admin/pet-sitters"
          class="rounded-lg px-3 py-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition"
          active-class="bg-zinc-800 text-zinc-100"
        >Pet Sitters</RouterLink>
        <button
          class="ml-3 rounded-lg px-3 py-1.5 text-xs text-red-400 border border-red-500/30 hover:bg-red-500/10 transition"
          @click="logout"
        >Sign out</button>
      </nav>
    </header>

    <div class="mx-auto max-w-7xl px-6 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Logs</h1>
        <p class="text-sm text-zinc-400 mt-1">Activity, errors, and raw application & server logs</p>
      </div>

      <!-- Tab nav -->
      <div class="mb-6 flex gap-1 border-b border-zinc-800">
        <button
          v-for="tab in [
            { id: 'errors', label: 'Error Logs', icon: '⚠️' },
            { id: 'activity', label: 'Activity', icon: '📋' },
            { id: 'app-logs', label: 'App Logs', icon: '📄' },
            { id: 'server-logs', label: 'Server Logs', icon: '🌐' },
          ]"
          :key="tab.id"
          class="px-4 py-2 text-sm font-medium rounded-t-lg transition border-b-2 -mb-px"
          :class="activeTab === tab.id
            ? 'border-amber-500 text-amber-400 bg-zinc-900'
            : 'border-transparent text-zinc-500 hover:text-zinc-300 hover:bg-zinc-900/50'"
          @click="activeTab = tab.id as Tab"
        >{{ tab.icon }} {{ tab.label }}</button>
      </div>

      <!-- ── ERROR LOGS TAB ─────────────────────────────────────────────────── -->
      <template v-if="activeTab === 'errors'">
        <div class="mb-4 text-sm text-zinc-400">HTTP 4xx and 5xx errors captured from the API.</div>

        <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-zinc-700 bg-zinc-900 p-4">
          <div class="flex-1 min-w-48">
            <label class="block text-xs text-zinc-400 mb-1">Search (path or message)</label>
            <input
              v-model="errorSearch"
              type="text"
              placeholder="/api/cats or 'not found'"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
            />
          </div>
          <div class="w-48">
            <label class="block text-xs text-zinc-400 mb-1">User ID / Email</label>
            <input
              v-model="errorUserId"
              type="text"
              placeholder="user@example.com"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
            />
          </div>
          <div class="w-40">
            <label class="block text-xs text-zinc-400 mb-1">Log Level</label>
            <select
              v-model="errorLogLevel"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 focus:border-amber-500 focus:outline-none"
            >
              <option value="">All levels</option>
              <option value="error">Error (5xx)</option>
              <option value="warning">Warning (4xx)</option>
              <option value="info">Info</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button class="rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-zinc-950 hover:bg-amber-400 transition" @click="applyErrorFilters">Apply</button>
            <button class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 transition" @click="clearErrorFilters">Clear</button>
          </div>
        </div>

        <div class="mb-4 flex items-center justify-between text-sm text-zinc-400">
          <span>{{ errorLogs?.total ?? 0 }} entries found</span>
          <span v-if="errorLogs">Page {{ errorLogs.page }} / {{ errorLogs.totalPages }}</span>
        </div>

        <div v-if="errorPending" class="text-center py-12 text-zinc-500">Loading…</div>
        <div v-else-if="!errorLogs?.items.length" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">No error logs found.</div>
        <div v-else class="space-y-2">
          <div
            v-for="log in errorLogs.items"
            :key="log.id"
            class="rounded-xl border bg-zinc-900 overflow-hidden"
            :class="log.logLevel === 'error' ? 'border-red-500/30' : 'border-amber-500/30'"
          >
            <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-zinc-800/50 transition" @click="toggleTrace(log.id)">
              <span class="shrink-0 rounded border px-1.5 py-0.5 text-xs font-mono font-bold" :class="logLevelColor(log.logLevel)">{{ log.statusCode }}</span>
              <span class="shrink-0 text-xs text-zinc-500 font-mono w-12">{{ log.method }}</span>
              <span class="flex-1 truncate text-sm font-mono text-zinc-300">{{ log.path }}</span>
              <span class="shrink-0 text-xs text-zinc-500 truncate max-w-48">{{ log.message }}</span>
              <span class="shrink-0 text-xs text-zinc-600 ml-2">{{ new Date(log.createdAt).toLocaleString() }}</span>
              <span class="shrink-0 text-xs text-zinc-600">{{ expandedLogId === log.id ? '▲' : '▼' }}</span>
            </div>
            <div v-if="expandedLogId === log.id" class="border-t border-zinc-700 px-4 py-3 bg-zinc-950/50">
              <div class="grid grid-cols-2 gap-2 text-xs text-zinc-400 mb-3">
                <div><span class="text-zinc-600">User:</span> {{ log.userId ?? '—' }}</div>
                <div><span class="text-zinc-600">Level:</span>
                  <span :class="log.logLevel === 'error' ? 'text-red-400' : 'text-amber-400'">{{ log.logLevel }}</span>
                </div>
                <div class="col-span-2"><span class="text-zinc-600">Message:</span> {{ log.message }}</div>
              </div>
              <div v-if="log.stackTrace">
                <p class="text-xs text-zinc-600 mb-1">Stack trace:</p>
                <pre class="text-xs text-red-300/80 bg-zinc-950 rounded p-3 overflow-auto max-h-64 whitespace-pre-wrap">{{ log.stackTrace }}</pre>
              </div>
              <div v-else class="text-xs text-zinc-600 italic">No stack trace available.</div>
            </div>
          </div>
        </div>

        <div v-if="errorLogs && errorLogs.totalPages > 1" class="mt-4 flex justify-center gap-3">
          <button :disabled="errorLogs.page <= 1" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition" @click="prevErrorPage">← Previous</button>
          <button :disabled="errorLogs.page >= errorLogs.totalPages" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition" @click="nextErrorPage">Next →</button>
        </div>
      </template>

      <!-- ── ACTIVITY TAB ───────────────────────────────────────────────────── -->
      <template v-else-if="activeTab === 'activity'">
        <div class="mb-4 text-sm text-zinc-400">All API requests made by users, including successful ones.</div>

        <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-zinc-700 bg-zinc-900 p-4">
          <div class="flex-1 min-w-48">
            <label class="block text-xs text-zinc-400 mb-1">Search (path)</label>
            <input
              v-model="activitySearch"
              type="text"
              placeholder="/api/cats"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
            />
          </div>
          <div class="w-48">
            <label class="block text-xs text-zinc-400 mb-1">User ID / Email</label>
            <input
              v-model="activityUserId"
              type="text"
              placeholder="user@example.com"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-amber-500 focus:outline-none"
            />
          </div>
          <div class="w-36">
            <label class="block text-xs text-zinc-400 mb-1">Method</label>
            <select
              v-model="activityMethod"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 focus:border-amber-500 focus:outline-none"
            >
              <option value="">All</option>
              <option value="GET">GET</option>
              <option value="POST">POST</option>
              <option value="PATCH">PATCH</option>
              <option value="PUT">PUT</option>
              <option value="DELETE">DELETE</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button class="rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-zinc-950 hover:bg-amber-400 transition" @click="applyActivityFilters">Apply</button>
            <button class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 transition" @click="clearActivityFilters">Clear</button>
          </div>
        </div>

        <div class="mb-4 flex items-center justify-between text-sm text-zinc-400">
          <span>{{ activityLogs?.total ?? 0 }} entries found</span>
          <span v-if="activityLogs">Page {{ activityLogs.page }} / {{ activityLogs.totalPages }}</span>
        </div>

        <div v-if="activityPending" class="text-center py-12 text-zinc-500">Loading…</div>
        <div v-else-if="!activityLogs?.items.length" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">No activity recorded yet.</div>
        <div v-else class="rounded-xl border border-zinc-700 overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-zinc-700 bg-zinc-900 text-left text-xs text-zinc-500 uppercase tracking-wide">
                <th class="px-4 py-2">Method</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2 flex-1">Path</th>
                <th class="px-4 py-2">User</th>
                <th class="px-4 py-2">IP</th>
                <th class="px-4 py-2">Time</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="log in activityLogs.items"
                :key="log.id"
                class="border-b border-zinc-800 bg-zinc-900 hover:bg-zinc-800/50 transition"
              >
                <td class="px-4 py-2 font-mono font-bold text-xs" :class="methodColor(log.method)">{{ log.method }}</td>
                <td class="px-4 py-2">
                  <span class="rounded border px-1.5 py-0.5 text-xs font-mono font-bold" :class="statusColor(log.statusCode)">{{ log.statusCode }}</span>
                </td>
                <td class="px-4 py-2 font-mono text-xs text-zinc-300 max-w-xs truncate">{{ log.path }}</td>
                <td class="px-4 py-2 text-xs text-zinc-500 truncate max-w-36">{{ log.userId ?? '—' }}</td>
                <td class="px-4 py-2 text-xs text-zinc-600 font-mono">{{ log.ip ?? '—' }}</td>
                <td class="px-4 py-2 text-xs text-zinc-600 whitespace-nowrap">{{ new Date(log.createdAt).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="activityLogs && activityLogs.totalPages > 1" class="mt-4 flex justify-center gap-3">
          <button :disabled="activityLogs.page <= 1" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition" @click="prevActivityPage">← Previous</button>
          <button :disabled="activityLogs.page >= activityLogs.totalPages" class="rounded-lg border border-zinc-700 px-4 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition" @click="nextActivityPage">Next →</button>
        </div>
      </template>

      <!-- ── APP LOGS TAB ───────────────────────────────────────────────────── -->
      <template v-else-if="activeTab === 'app-logs'">
        <div class="mb-4 text-sm text-zinc-400">Symfony application log file (<code class="text-amber-400 font-mono">var/log/app.log</code>).</div>

        <div class="mb-4 flex items-center gap-3">
          <div class="w-36">
            <label class="block text-xs text-zinc-400 mb-1">Lines to fetch</label>
            <select
              v-model="appLogLines"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 focus:border-amber-500 focus:outline-none"
            >
              <option :value="50">50</option>
              <option :value="100">100</option>
              <option :value="200">200</option>
              <option :value="500">500</option>
            </select>
          </div>
          <button
            class="mt-5 rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-zinc-950 hover:bg-amber-400 transition"
            @click="loadAppLogs"
          >Load logs</button>
        </div>

        <div v-if="appLogsPending" class="text-center py-12 text-zinc-500">Loading…</div>
        <div v-else-if="!appLogs" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">
          Click "Load logs" to fetch the latest application log lines.
        </div>
        <div v-else-if="!appLogs.lines.length" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">
          Log file is empty or not yet created.
        </div>
        <div v-else class="rounded-xl border border-zinc-700 bg-zinc-950 overflow-hidden">
          <div class="flex items-center justify-between px-4 py-2 bg-zinc-900 border-b border-zinc-700">
            <span class="text-xs text-zinc-500 font-mono">{{ appLogs.file }} — {{ appLogs.lines.length }} lines</span>
            <button class="text-xs text-amber-400 hover:text-amber-300 transition" @click="loadAppLogs">↺ Refresh</button>
          </div>
          <div class="overflow-auto max-h-[600px]">
            <pre class="text-xs text-zinc-300 font-mono p-4 whitespace-pre-wrap leading-5">{{ appLogs.lines.join('\n') }}</pre>
          </div>
        </div>
      </template>

      <!-- ── SERVER LOGS TAB ────────────────────────────────────────────────── -->
      <template v-else-if="activeTab === 'server-logs'">
        <div class="mb-4 text-sm text-zinc-400">FrankenPHP / Caddy HTTP access log (<code class="text-amber-400 font-mono">/var/log/caddy/access.log</code>).</div>

        <div class="mb-4 flex items-center gap-3">
          <div class="w-36">
            <label class="block text-xs text-zinc-400 mb-1">Lines to fetch</label>
            <select
              v-model="serverLogLines"
              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-100 focus:border-amber-500 focus:outline-none"
            >
              <option :value="50">50</option>
              <option :value="100">100</option>
              <option :value="200">200</option>
              <option :value="500">500</option>
            </select>
          </div>
          <button
            class="mt-5 rounded-lg bg-amber-500 px-4 py-1.5 text-sm font-medium text-zinc-950 hover:bg-amber-400 transition"
            @click="loadServerLogs"
          >Load logs</button>
        </div>

        <div v-if="serverLogsPending" class="text-center py-12 text-zinc-500">Loading…</div>
        <div v-else-if="!serverLogs" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">
          Click "Load logs" to fetch the latest server log lines.
        </div>
        <div v-else-if="!serverLogs.lines.length" class="rounded-xl border border-zinc-700 bg-zinc-900 py-12 text-center text-zinc-500">
          Log file is empty or not yet created. The server log is written on the first HTTP request after container start.
        </div>
        <div v-else class="rounded-xl border border-zinc-700 bg-zinc-950 overflow-hidden">
          <div class="flex items-center justify-between px-4 py-2 bg-zinc-900 border-b border-zinc-700">
            <span class="text-xs text-zinc-500 font-mono">{{ serverLogs.file }} — {{ serverLogs.lines.length }} lines</span>
            <button class="text-xs text-amber-400 hover:text-amber-300 transition" @click="loadServerLogs">↺ Refresh</button>
          </div>
          <div class="overflow-auto max-h-[600px]">
            <pre class="text-xs text-zinc-300 font-mono p-4 whitespace-pre-wrap leading-5">{{ serverLogs.lines.join('\n') }}</pre>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
