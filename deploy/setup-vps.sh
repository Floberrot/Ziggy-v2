#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
# Ziggy — Infomaniak VPS Lite — Initial Server Setup & Hardening
#
# Run ONCE as root on a fresh Ubuntu 22.04 / Debian 12 VPS.
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/YOUR_ORG/ziggy-v2/main/deploy/setup-vps.sh | bash
#   — or —
#   scp deploy/setup-vps.sh root@YOUR_VPS_IP:/tmp/ && ssh root@YOUR_VPS_IP bash /tmp/setup-vps.sh
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

# ── Configuration — edit before running ──────────────────────────────────────
DEPLOY_USER="deploy"
DEPLOY_DIR="/opt/ziggy"
SSH_PUBLIC_KEY=""   # Paste your GitHub Actions / CI deploy public key here
# ─────────────────────────────────────────────────────────────────────────────

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()    { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

[[ $EUID -ne 0 ]] && error "This script must be run as root."

# ── 1. System update ──────────────────────────────────────────────────────────
info "Updating system packages..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq \
    curl \
    wget \
    git \
    ufw \
    fail2ban \
    unattended-upgrades \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    logrotate \
    htop \
    jq \
    postgresql-client

# ── 2. Unattended security upgrades ──────────────────────────────────────────
info "Enabling automatic security updates..."
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOF
systemctl enable --now unattended-upgrades

# ── 3. Deploy user ────────────────────────────────────────────────────────────
info "Creating deploy user: ${DEPLOY_USER}..."
if ! id "${DEPLOY_USER}" &>/dev/null; then
    useradd --system --create-home --shell /bin/bash --groups docker "${DEPLOY_USER}" 2>/dev/null || true
    useradd --create-home --shell /bin/bash "${DEPLOY_USER}"
fi

# Allow deploy user to manage Docker without sudo
usermod -aG docker "${DEPLOY_USER}" 2>/dev/null || true

# SSH key for CI/CD
if [[ -n "${SSH_PUBLIC_KEY}" ]]; then
    mkdir -p "/home/${DEPLOY_USER}/.ssh"
    echo "${SSH_PUBLIC_KEY}" >> "/home/${DEPLOY_USER}/.ssh/authorized_keys"
    chmod 700 "/home/${DEPLOY_USER}/.ssh"
    chmod 600 "/home/${DEPLOY_USER}/.ssh/authorized_keys"
    chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "/home/${DEPLOY_USER}/.ssh"
    info "SSH public key added for ${DEPLOY_USER}."
else
    warn "SSH_PUBLIC_KEY not set. Add it manually: echo 'ssh-ed25519 ...' >> /home/${DEPLOY_USER}/.ssh/authorized_keys"
fi

# ── 4. Application directory ──────────────────────────────────────────────────
info "Creating application directory at ${DEPLOY_DIR}..."
mkdir -p "${DEPLOY_DIR}/secrets"
chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "${DEPLOY_DIR}"
chmod 750 "${DEPLOY_DIR}"
chmod 700 "${DEPLOY_DIR}/secrets"

# ── 5. Docker installation ────────────────────────────────────────────────────
if command -v docker &>/dev/null; then
    info "Docker already installed: $(docker --version)"
else
    info "Installing Docker..."
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
        > /etc/apt/sources.list.d/docker.list

    apt-get update -qq
    apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    systemctl enable --now docker
    info "Docker installed: $(docker --version)"
fi

# ── 6. UFW firewall ───────────────────────────────────────────────────────────
info "Configuring UFW firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh        # 22 — SSH
ufw allow 80/tcp     # HTTP (Caddy redirects to HTTPS)
ufw allow 443/tcp    # HTTPS
ufw allow 443/udp    # HTTP/3 (QUIC)
# Do NOT expose 5432 — PostgreSQL stays private inside Docker network
ufw --force enable
info "UFW status:"
ufw status verbose

# ── 7. fail2ban ───────────────────────────────────────────────────────────────
info "Configuring fail2ban..."
cat > /etc/fail2ban/jail.d/ziggy.conf <<'EOF'
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled  = true
port     = ssh
logpath  = %(sshd_log)s
maxretry = 3
bantime  = 24h
EOF
systemctl enable --now fail2ban
info "fail2ban enabled."

# ── 8. SSH hardening ──────────────────────────────────────────────────────────
info "Hardening SSH configuration..."
SSHD_CONFIG="/etc/ssh/sshd_config.d/99-ziggy-hardening.conf"
cat > "${SSHD_CONFIG}" <<'EOF'
# Ziggy VPS hardening
PermitRootLogin           prohibit-password
PasswordAuthentication    no
PubkeyAuthentication      yes
AuthorizedKeysFile        .ssh/authorized_keys
X11Forwarding             no
AllowTcpForwarding        no
MaxAuthTries              3
LoginGraceTime            20
ClientAliveInterval       300
ClientAliveCountMax       2
EOF
sshd -t && systemctl reload sshd
info "SSH hardened. Root login: key-only. Password auth: disabled."

# ── 9. Swap (recommended for VPS Lite with < 4 GB RAM) ────────────────────────
if [[ ! -f /swapfile ]]; then
    info "Creating 2 GB swapfile..."
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    sysctl vm.swappiness=10
    echo 'vm.swappiness=10' >> /etc/sysctl.d/99-swap.conf
    info "Swap created and enabled."
else
    info "Swap already configured."
fi

# ── 10. Log rotation for Docker ───────────────────────────────────────────────
info "Configuring Docker log rotation..."
cat > /etc/docker/daemon.json <<'EOF'
{
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "50m",
    "max-file": "5"
  }
}
EOF
systemctl reload docker

# ── 11. Kernel hardening (sysctl) ─────────────────────────────────────────────
info "Applying sysctl hardening..."
cat > /etc/sysctl.d/99-ziggy-security.conf <<'EOF'
# Disable IP source routing
net.ipv4.conf.all.accept_source_route = 0
net.ipv6.conf.all.accept_source_route = 0
# Disable ICMP redirects
net.ipv4.conf.all.accept_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
# Enable SYN cookies (SYN flood protection)
net.ipv4.tcp_syncookies = 1
# Ignore bogus ICMP errors
net.ipv4.icmp_ignore_bogus_error_responses = 1
# Reduce time-wait sockets
net.ipv4.tcp_fin_timeout = 15
EOF
sysctl --system > /dev/null

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  VPS setup complete!${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo ""
echo "Next steps:"
echo "  1. Upload JWT keys:   scp back/config/jwt/*.pem ${DEPLOY_USER}@VPS_IP:${DEPLOY_DIR}/secrets/"
echo "  2. Create env file:   cp deploy/.env.prod.example ${DEPLOY_DIR}/.env.prod && nano ${DEPLOY_DIR}/.env.prod"
echo "  3. Upload compose:    scp deploy/docker-compose.prod.yml ${DEPLOY_USER}@VPS_IP:${DEPLOY_DIR}/"
echo "  4. Deploy:            ssh ${DEPLOY_USER}@VPS_IP 'cd ${DEPLOY_DIR} && bash deploy.sh'"
echo ""
warn "IMPORTANT: Verify you can SSH as ${DEPLOY_USER} BEFORE closing this session."
