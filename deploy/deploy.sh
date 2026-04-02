#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
# Ziggy — Zero-downtime deployment script
#
# Run on the VPS as the deploy user.
# Called by GitHub Actions after the Docker image is pushed to GHCR.
#
# Usage:
#   bash /opt/ziggy/deploy.sh [IMAGE_TAG]
#   IMAGE_TAG defaults to "latest"
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

DEPLOY_DIR="/opt/ziggy"
ENV_FILE="${DEPLOY_DIR}/.env.prod"
COMPOSE_FILE="${DEPLOY_DIR}/docker-compose.prod.yml"
IMAGE_TAG="${1:-latest}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()    { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

# ── Guards ────────────────────────────────────────────────────────────────────
[[ -f "${ENV_FILE}" ]]     || error "Missing ${ENV_FILE}. Create it from deploy/.env.prod.example"
[[ -f "${COMPOSE_FILE}" ]] || error "Missing ${COMPOSE_FILE}."
[[ -d "${DEPLOY_DIR}/secrets" ]] || error "Missing ${DEPLOY_DIR}/secrets/. Upload JWT keys first."
[[ -f "${DEPLOY_DIR}/secrets/jwt_private.pem" ]] || error "Missing jwt_private.pem in secrets/"
[[ -f "${DEPLOY_DIR}/secrets/jwt_public.pem"  ]] || error "Missing jwt_public.pem in secrets/"

cd "${DEPLOY_DIR}"

# Inject the target tag into the env
export $(grep -v '^#' "${ENV_FILE}" | xargs)
export IMAGE_TAG="${IMAGE_TAG}"

# ── Derive GITHUB_REPOSITORY from env if not already exported ─────────────────
GITHUB_REPOSITORY="${GITHUB_REPOSITORY:-}"
[[ -n "${GITHUB_REPOSITORY}" ]] || error "GITHUB_REPOSITORY not set in ${ENV_FILE}"

REGISTRY="ghcr.io/${GITHUB_REPOSITORY}"
FULL_IMAGE="${REGISTRY}:${IMAGE_TAG}"

# ── 1. Pull the new image ─────────────────────────────────────────────────────
info "Pulling image: ${FULL_IMAGE}"
docker pull "${FULL_IMAGE}"

# Tag as latest locally so compose always has a consistent ref
docker tag "${FULL_IMAGE}" "${REGISTRY}:latest"

# ── 2. Run DB migrations before switching traffic ─────────────────────────────
info "Running database migrations..."
docker run --rm \
    --env-file "${ENV_FILE}" \
    --env IMAGE_TAG="${IMAGE_TAG}" \
    --network ziggy_ziggy \
    --entrypoint php \
    "${FULL_IMAGE}" \
    bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# ── 3. Rolling update (Compose v2 native) ────────────────────────────────────
info "Updating app service..."
IMAGE_TAG="${IMAGE_TAG}" docker compose \
    --env-file "${ENV_FILE}" \
    -f "${COMPOSE_FILE}" \
    up -d --no-deps --pull never app

info "Updating worker service..."
IMAGE_TAG="${IMAGE_TAG}" docker compose \
    --env-file "${ENV_FILE}" \
    -f "${COMPOSE_FILE}" \
    up -d --no-deps --pull never worker

# ── 4. Health check ───────────────────────────────────────────────────────────
info "Waiting for app to be healthy..."
RETRIES=20
until docker compose \
    --env-file "${ENV_FILE}" \
    -f "${COMPOSE_FILE}" \
    ps --format json | jq -e '
        [.[] | select(.Service == "app")] |
        all(.Health == "healthy")
    ' > /dev/null 2>&1; do
    RETRIES=$((RETRIES - 1))
    if [[ ${RETRIES} -le 0 ]]; then
        error "App failed to become healthy. Rolling back..."
    fi
    warn "App not healthy yet (${RETRIES} retries left)..."
    sleep 6
done

# ── 5. Prune old images ───────────────────────────────────────────────────────
info "Pruning dangling images..."
docker image prune -f

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Deploy successful — ${FULL_IMAGE}${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
docker compose --env-file "${ENV_FILE}" -f "${COMPOSE_FILE}" ps
