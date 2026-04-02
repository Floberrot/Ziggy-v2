# Ziggy — Production Deployment Guide

**Stack:** Symfony 8 · FrankenPHP (Caddy) · Vue 3 · PostgreSQL 16 · Docker  
**Target:** [Infomaniak VPS Lite](https://www.infomaniak.com/fr/hebergement/vps-lite)  
**Recommended OS:** Ubuntu 22.04 LTS

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Prerequisites](#2-prerequisites)
3. [Step 1 — Order & access the VPS](#3-step-1--order--access-the-vps)
4. [Step 2 — Harden the server](#4-step-2--harden-the-server)
5. [Step 3 — Configure DNS](#5-step-3--configure-dns)
6. [Step 4 — Generate JWT keys](#6-step-4--generate-jwt-keys)
7. [Step 5 — Configure environment](#7-step-5--configure-environment)
8. [Step 6 — Upload secrets](#8-step-6--upload-secrets)
9. [Step 7 — First deployment (manual)](#9-step-7--first-deployment-manual)
10. [Step 8 — Automated CI/CD (GitHub Actions)](#10-step-8--automated-cicd-github-actions)
11. [Step 9 — Set up backups](#11-step-9--set-up-backups)
12. [Operations Reference](#12-operations-reference)
13. [Troubleshooting](#13-troubleshooting)
14. [Security Checklist](#14-security-checklist)

---

## 1. Architecture Overview

```
                      Internet
                          │
                    443 / 80 (TCP/UDP)
                          │
              ┌───────────▼───────────┐
              │     FrankenPHP        │  ← Caddy HTTPS + HTTP/3
              │   (app container)     │  ← Symfony API (/api/*)
              │                       │  ← Vue 3 SPA (everything else)
              └───────┬───────────────┘
                      │  Docker network (ziggy)
          ┌───────────┼────────────┐
          │                        │
  ┌───────▼──────┐       ┌────────▼────────┐
  │  PostgreSQL  │       │  Messenger       │
  │  (db)        │       │  Worker          │
  └──────────────┘       └─────────────────┘
```

- **FrankenPHP** handles TLS termination, HTTP/3, PHP execution, and static file serving — no Nginx needed.
- **Caddy** (embedded in FrankenPHP) automatically obtains and renews Let's Encrypt certificates.
- **PostgreSQL** is never exposed to the host — only reachable inside the Docker bridge network.
- **Messenger Worker** consumes the `async` queue (emails, background tasks) and auto-restarts.

---

## 2. Prerequisites

| Requirement | Details |
|---|---|
| Infomaniak VPS Lite | Any tier. Minimum: 1 vCPU, 2 GB RAM, 20 GB SSD |
| Domain name | Pointed to the VPS IP (A record) |
| GitHub repository | With GitHub Container Registry (GHCR) enabled |
| Local machine | SSH client, `openssl` for key generation |

---

## 3. Step 1 — Order & access the VPS

1. Order a **VPS Lite** at infomaniak.com.
2. Choose **Ubuntu 22.04 LTS** as the OS.
3. Add your SSH public key during the order (or set a root password and add it later).
4. Once provisioned, connect:

```bash
ssh root@YOUR_VPS_IP
```

---

## 4. Step 2 — Harden the server

Run the setup script **once** as root. It installs Docker, configures UFW, fail2ban, SSH hardening, swap, and creates the `deploy` user.

```bash
# On your local machine — upload the script
scp deploy/setup-vps.sh root@YOUR_VPS_IP:/tmp/

# On the VPS
ssh root@YOUR_VPS_IP
nano /tmp/setup-vps.sh      # Set SSH_PUBLIC_KEY (see below)
bash /tmp/setup-vps.sh
```

**Get your CI SSH public key** (or generate a dedicated deploy key):

```bash
# Generate a dedicated ED25519 deploy key (on your local machine)
ssh-keygen -t ed25519 -C "ziggy-deploy" -f ~/.ssh/ziggy_deploy -N ""
cat ~/.ssh/ziggy_deploy.pub   # → paste this into SSH_PUBLIC_KEY in setup-vps.sh
```

After the script completes, verify you can SSH as the deploy user before closing the root session:

```bash
ssh deploy@YOUR_VPS_IP
```

---

## 5. Step 3 — Configure DNS

In your DNS provider (or Infomaniak's DNS manager), create:

| Type | Name | Value | TTL |
|---|---|---|---|
| A | `app.yourdomain.com` | `YOUR_VPS_IP` | 300 |

Wait for DNS propagation (up to 5 minutes with TTL 300).  
FrankenPHP/Caddy will automatically request a Let's Encrypt certificate on first start.

> **Port requirements:** 80 and 443 (TCP + UDP for HTTP/3) must be open. The setup script configures UFW for this.

---

## 6. Step 4 — Generate JWT keys

```bash
# From the project root (back/ directory)
cd back

# Generate RSA-4096 private key with passphrase
openssl genpkey -algorithm RSA \
    -aes256 \
    -pass pass:YOUR_PASSPHRASE \
    -out config/jwt/private.pem \
    -pkeyopt rsa_keygen_bits:4096

# Derive the public key
openssl pkey \
    -in config/jwt/private.pem \
    -passin pass:YOUR_PASSPHRASE \
    -pubout \
    -out config/jwt/public.pem
```

> Keep `YOUR_PASSPHRASE` — you will need it as `JWT_PASSPHRASE` in the environment file.

---

## 7. Step 5 — Configure environment

```bash
# From the project root
cp deploy/.env.prod.example /tmp/.env.prod
nano /tmp/.env.prod
```

Fill in every variable. Key ones:

| Variable | How to generate |
|---|---|
| `SERVER_NAME` | Your domain: `app.yourdomain.com` |
| `ACME_EMAIL` | Your email for Let's Encrypt |
| `APP_SECRET` | `openssl rand -hex 32` |
| `JWT_PASSPHRASE` | Same passphrase used during key generation |
| `POSTGRES_PASSWORD` | `openssl rand -hex 24` |
| `MAILER_DSN` | See `.env.prod.example` for examples |

---

## 8. Step 6 — Upload secrets to the VPS

```bash
# JWT private key
scp back/config/jwt/private.pem deploy@YOUR_VPS_IP:/opt/ziggy/secrets/jwt_private.pem
scp back/config/jwt/public.pem  deploy@YOUR_VPS_IP:/opt/ziggy/secrets/jwt_public.pem

# Environment file
scp /tmp/.env.prod deploy@YOUR_VPS_IP:/opt/ziggy/.env.prod

# Deployment scripts
scp deploy/docker-compose.prod.yml deploy@YOUR_VPS_IP:/opt/ziggy/docker-compose.prod.yml
scp deploy/deploy.sh               deploy@YOUR_VPS_IP:/opt/ziggy/deploy.sh
scp deploy/backup.sh               deploy@YOUR_VPS_IP:/opt/ziggy/backup.sh

# Fix permissions
ssh deploy@YOUR_VPS_IP 'chmod 600 /opt/ziggy/secrets/*.pem /opt/ziggy/.env.prod && chmod +x /opt/ziggy/deploy.sh /opt/ziggy/backup.sh'
```

---

## 9. Step 7 — First deployment (manual)

```bash
# On the VPS as deploy user
ssh deploy@YOUR_VPS_IP

# Authenticate with GitHub Container Registry
echo "YOUR_GITHUB_PAT" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin

# Pull and start the stack for the first time
cd /opt/ziggy
IMAGE_TAG=latest bash deploy.sh
```

> **GitHub PAT:** Generate a Personal Access Token with `read:packages` scope at github.com → Settings → Developer settings → Personal access tokens.

After the first deploy, verify:

```bash
# Stack status
docker compose -f /opt/ziggy/docker-compose.prod.yml ps

# Application logs
docker compose -f /opt/ziggy/docker-compose.prod.yml logs app --tail=50

# Test HTTPS
curl -I https://app.yourdomain.com/health
# Expected: HTTP/2 200
```

---

## 10. Step 8 — Automated CI/CD (GitHub Actions)

Every push to `main` automatically runs quality gates, builds the Docker image, and deploys.

### GitHub Secrets to configure

Go to your repo → **Settings → Secrets and variables → Actions → Secrets** and add:

| Secret | Value |
|---|---|
| `VPS_HOST` | Your VPS IP address or hostname |
| `VPS_USER` | `deploy` |
| `VPS_SSH_KEY` | Contents of `~/.ssh/ziggy_deploy` (the **private** key) |
| `VPS_PORT` | `22` (optional, defaults to 22) |

### GitHub Environment

Create a **production** environment (repo → Settings → Environments) and optionally add:
- Required reviewers (for manual approval before deploy)
- Wait timer

### Pipeline overview

```
push to main
    │
    ├── quality        PHPStan + PHPCS + Deptrac
    ├── typecheck      vue-tsc
    │       │
    └── build ─────── docker build → ghcr.io/org/ziggy-v2:SHA + :latest
            │
        deploy ──────  SSH → pull image → migrate DB → rolling restart → health check
```

---

## 11. Step 9 — Set up backups

The `backup.sh` script dumps PostgreSQL, compresses it, and rotates files older than 14 days.

```bash
# Test it manually first
ssh deploy@YOUR_VPS_IP 'bash /opt/ziggy/backup.sh'

# Install cron job (daily at 03:00)
ssh deploy@YOUR_VPS_IP 'crontab -e'
# Add this line:
# 0 3 * * * /opt/ziggy/backup.sh >> /var/log/ziggy-backup.log 2>&1
```

### Restore from backup

```bash
# On the VPS
BACKUP_FILE="/opt/ziggy/backups/ziggy_20250101_030000.sql.gz"

# Decompress and restore
gunzip -c "${BACKUP_FILE}" | docker run --rm -i \
    --network ziggy_ziggy \
    -e PGPASSWORD="${POSTGRES_PASSWORD}" \
    postgres:16-alpine \
    psql --host db --username ziggy --dbname ziggy
```

---

## 12. Operations Reference

All commands run on the VPS as the `deploy` user from `/opt/ziggy`.

```bash
COMPOSE="docker compose -f /opt/ziggy/docker-compose.prod.yml --env-file /opt/ziggy/.env.prod"

# View running containers
$COMPOSE ps

# Follow application logs
$COMPOSE logs app -f

# Follow worker logs
$COMPOSE logs worker -f

# Restart a service
$COMPOSE restart app
$COMPOSE restart worker

# Open a shell in the app container
$COMPOSE exec app bash

# Run a Symfony console command
$COMPOSE exec app php bin/console <command>

# Force re-deploy with a specific tag
bash /opt/ziggy/deploy.sh abc1234

# Scale worker (if queue volume increases)
$COMPOSE up -d --scale worker=2

# Full stack down (maintenance)
$COMPOSE down

# Full stack up
$COMPOSE up -d
```

---

## 13. Troubleshooting

### Certificate not obtained

```bash
# Check Caddy/FrankenPHP logs
docker compose -f /opt/ziggy/docker-compose.prod.yml logs app | grep -i "acme\|tls\|cert"

# Common causes:
# - DNS A record not propagated yet (wait and retry)
# - Port 80 or 443 blocked by UFW (check: ufw status)
# - ACME_EMAIL not set in .env.prod
```

### Database connection refused

```bash
# Check PostgreSQL is healthy
docker compose -f /opt/ziggy/docker-compose.prod.yml ps db
docker compose -f /opt/ziggy/docker-compose.prod.yml logs db --tail=20

# Verify the db container is on the right network
docker network inspect ziggy_ziggy
```

### Migration fails during deploy

```bash
# Run manually
docker compose -f /opt/ziggy/docker-compose.prod.yml exec app \
    php bin/console doctrine:migrations:migrate --no-interaction

# Check migration status
docker compose -f /opt/ziggy/docker-compose.prod.yml exec app \
    php bin/console doctrine:migrations:status
```

### Out of disk space

```bash
# Check disk
df -h

# Clean unused Docker resources
docker system prune -f
docker volume prune -f   # WARNING: only run if all containers are stopped

# Check backup dir size
du -sh /opt/ziggy/backups/
```

### Worker not processing messages

```bash
# Check failed messages
docker compose -f /opt/ziggy/docker-compose.prod.yml exec app \
    php bin/console messenger:failed:show

# Retry all failed messages
docker compose -f /opt/ziggy/docker-compose.prod.yml exec app \
    php bin/console messenger:failed:retry
```

---

## 14. Security Checklist

- [ ] Root SSH login uses key-only (`PermitRootLogin prohibit-password`)
- [ ] Password authentication disabled (`PasswordAuthentication no`)
- [ ] UFW enabled — only 22, 80, 443 open
- [ ] fail2ban running (`systemctl status fail2ban`)
- [ ] PostgreSQL port NOT exposed to host (verify with `docker compose ps` — no `0.0.0.0:5432`)
- [ ] `.env.prod` permissions are `600` (`ls -la /opt/ziggy/.env.prod`)
- [ ] JWT key permissions are `600` (`ls -la /opt/ziggy/secrets/`)
- [ ] `APP_SECRET` is a unique 64-char hex string (not the example value)
- [ ] `POSTGRES_PASSWORD` is strong and unique
- [ ] Automatic security updates enabled (`systemctl status unattended-upgrades`)
- [ ] Backups running and tested with a restore
- [ ] GitHub Actions secrets set (VPS_HOST, VPS_USER, VPS_SSH_KEY)
- [ ] GHCR image is private (repo → Packages → Visibility)
