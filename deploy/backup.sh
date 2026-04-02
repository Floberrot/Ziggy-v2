#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
# Ziggy — PostgreSQL backup script
#
# Dumps the production database, compresses it with gzip, and rotates old
# backups. Designed to be run by cron as the deploy user.
#
# Cron example (daily at 03:00):
#   0 3 * * * /opt/ziggy/backup.sh >> /var/log/ziggy-backup.log 2>&1
#
# Usage:
#   bash /opt/ziggy/backup.sh
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

DEPLOY_DIR="/opt/ziggy"
ENV_FILE="${DEPLOY_DIR}/.env.prod"
BACKUP_DIR="${DEPLOY_DIR}/backups"
KEEP_DAYS=14   # Rotate backups older than N days

GREEN='\033[0;32m'; RED='\033[0;31m'; NC='\033[0m'
info()  { echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] [BACKUP]${NC} $*"; }
error() { echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] [ERROR]${NC} $*"; exit 1; }

# ── Load env ──────────────────────────────────────────────────────────────────
[[ -f "${ENV_FILE}" ]] || error "Missing ${ENV_FILE}"
export $(grep -v '^#' "${ENV_FILE}" | xargs)

POSTGRES_DB="${POSTGRES_DB:-ziggy}"
POSTGRES_USER="${POSTGRES_USER:-ziggy}"
POSTGRES_PASSWORD="${POSTGRES_PASSWORD:?POSTGRES_PASSWORD not set}"

# ── Create backup directory ───────────────────────────────────────────────────
mkdir -p "${BACKUP_DIR}"
chmod 700 "${BACKUP_DIR}"

# ── Dump ─────────────────────────────────────────────────────────────────────
TIMESTAMP="$(date '+%Y%m%d_%H%M%S')"
BACKUP_FILE="${BACKUP_DIR}/ziggy_${TIMESTAMP}.sql.gz"

info "Starting backup → ${BACKUP_FILE}"
docker run --rm \
    --network ziggy_ziggy \
    -e PGPASSWORD="${POSTGRES_PASSWORD}" \
    postgres:16-alpine \
    pg_dump \
        --host db \
        --username "${POSTGRES_USER}" \
        --format plain \
        --no-owner \
        --no-acl \
        "${POSTGRES_DB}" \
    | gzip -9 > "${BACKUP_FILE}"

SIZE=$(du -sh "${BACKUP_FILE}" | cut -f1)
info "Backup completed — size: ${SIZE}"

# ── Rotate old backups ────────────────────────────────────────────────────────
info "Rotating backups older than ${KEEP_DAYS} days..."
find "${BACKUP_DIR}" -name "ziggy_*.sql.gz" -mtime "+${KEEP_DAYS}" -delete
REMAINING=$(find "${BACKUP_DIR}" -name "ziggy_*.sql.gz" | wc -l)
info "Backup rotation done — ${REMAINING} backup(s) retained."

# ── Verify backup is readable ─────────────────────────────────────────────────
if gzip -t "${BACKUP_FILE}"; then
    info "Integrity check passed."
else
    error "Backup file appears corrupted: ${BACKUP_FILE}"
fi
