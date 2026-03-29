# Agent Skills Reference

## Symfony UX

| Situation | Skill |
|---|---|
| Pure JS behavior, no server round-trip | `stimulus` |
| Navigation, partial page updates | `turbo` |
| Reusable static UI component | `twig-component` |
| Reactive component that re-renders on user input | `live-component` |
| SVG icons (local or Iconify) | `ux-icons` |
| Interactive maps (Leaflet / Google Maps) | `ux-map` |
| Not sure which one fits | `symfony-ux` (orchestrator / decision tree) |

**Key rules:**
- Always render `{{ attributes }}` on the root element of a LiveComponent
- Prefer HTML syntax (`<twig:Alert />`) over Twig syntax (`{% component 'Alert' %}`)
- Use `data-model="debounce(300)|field"` for text inputs in LiveComponents
- Stimulus controllers must clean up listeners and observers in `disconnect()`
- Turbo Frame IDs must match between the page and the server response
- Use Turbo Streams when updating multiple page sections; use Frames for a single section
- `<twig:Turbo:Stream:Append>` syntax is available since Symfony UX 2.22+
- Prefer `<twig:ux:icon name="..." />` over `{{ ux_icon('...') }}` for consistency
- Map containers must have an explicit height (`style="height: 400px;"`)
- Use `fitBoundsToMarkers()` instead of manually calculating center/zoom
- Lock on-demand icons before deploying: `php bin/console ux:icons:lock`

---

## Vue.js

| Situation | Skill |
|---|---|
| General Vue 3 component work | `vue-best-practices` (always load for `.vue` files) |
| Composition API patterns, composables | `create-adaptable-composable` |
| Options API (legacy or explicit requirement) | `vue-options-api-best-practices` |
| JSX in Vue | `vue-jsx-best-practices` |
| State management | `vue-pinia-best-practices` |
| Routing | `vue-router-best-practices` |
| Testing | `vue-testing-best-practices` |
| Debugging Vue issues | `vue-debug-guides` |

**Key rules:**
- Default to Composition API with `<script setup>` and TypeScript
- Only use Options API when the project explicitly requires it
- Use Pinia for UI state only — server state belongs to TanStack Query
- Use `vue-tsc` for type checking `.vue` files

---

## FrankenPHP

| Situation | Skill |
|---|---|
| Application server config, workers, early hints, real-time | `frankenphp` |

**Key rules:**
- FrankenPHP is built on Caddy — Caddyfile directives apply
- Use worker mode for long-running PHP applications
- FrankenPHP can serve Symfony apps with zero additional web server config
- Use `dunglas/frankenphp` as the Docker base image for Symfony apps

---

## Docker

| Situation | Skill |
|---|---|
| Dockerfiles, multi-stage builds, docker-compose, orchestration | `docker-containerization` |
| CLI commands: build, run, exec, logs, cleanup | `docker-cli` |

**Key rules:**
- Use multi-stage builds to minimize final image size
- Never run package managers or project scripts directly on the host; use `docker exec`
