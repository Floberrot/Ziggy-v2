#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
# Ziggy — WireGuard VPN Server Setup
#
# Run ONCE on the VPS as root, AFTER setup-vps.sh has already been executed.
#
# What this does:
#   - Installs WireGuard
#   - Generates server keypair
#   - Creates the wg0 interface (subnet 10.8.0.1/24)
#   - Enables IP forwarding
#   - Updates UFW:
#       • Closes ports 80 and 443 from the public internet
#       • Opens UDP 51820 (WireGuard handshake)
#       • Allows 80/443 only from the VPN subnet (10.8.0.0/24)
#   - Enables WireGuard at boot (systemd)
#
# Usage:
#   bash /tmp/setup-wireguard.sh
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

DEPLOY_DIR="/opt/ziggy"
WG_DIR="/etc/wireguard"
WG_IFACE="wg0"
VPN_SUBNET="10.8.0.0/24"
VPN_SERVER_IP="10.8.0.1"
WG_PORT="51820"

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

[[ $EUID -ne 0 ]] && error "Run as root."

# ── 1. Detect public network interface ───────────────────────────────────────
PUBLIC_IFACE=$(ip route show default | awk '/default/ { print $5 }' | head -1)
[[ -n "${PUBLIC_IFACE}" ]] || error "Cannot detect public network interface."
info "Public interface detected: ${PUBLIC_IFACE}"

# ── 2. Install WireGuard ──────────────────────────────────────────────────────
info "Installing WireGuard..."
apt-get update -qq
apt-get install -y -qq wireguard wireguard-tools

# ── 3. Generate server keypair ────────────────────────────────────────────────
info "Generating server keypair..."
mkdir -p "${WG_DIR}"
chmod 700 "${WG_DIR}"

if [[ -f "${WG_DIR}/server_private.key" ]]; then
    warn "Server keys already exist — skipping key generation."
else
    wg genkey | tee "${WG_DIR}/server_private.key" | wg pubkey > "${WG_DIR}/server_public.key"
    chmod 600 "${WG_DIR}/server_private.key"
fi

SERVER_PRIVATE_KEY=$(cat "${WG_DIR}/server_private.key")
SERVER_PUBLIC_KEY=$(cat "${WG_DIR}/server_public.key")

info "Server public key: ${SERVER_PUBLIC_KEY}"

# ── 4. Write wg0.conf ─────────────────────────────────────────────────────────
info "Writing ${WG_DIR}/${WG_IFACE}.conf..."
cat > "${WG_DIR}/${WG_IFACE}.conf" <<EOF
[Interface]
Address    = ${VPN_SERVER_IP}/24
ListenPort = ${WG_PORT}
PrivateKey = ${SERVER_PRIVATE_KEY}

# Enable IP forwarding and NAT when the interface comes up
PostUp   = iptables -A FORWARD -i %i -j ACCEPT; iptables -A FORWARD -o %i -j ACCEPT; iptables -t nat -A POSTROUTING -o ${PUBLIC_IFACE} -j MASQUERADE
PostDown = iptables -D FORWARD -i %i -j ACCEPT; iptables -D FORWARD -o %i -j ACCEPT; iptables -t nat -D POSTROUTING -o ${PUBLIC_IFACE} -j MASQUERADE

# ── Peers (clients) are appended below by add-client.sh ───────────────────────
EOF
chmod 600 "${WG_DIR}/${WG_IFACE}.conf"

# ── 5. Enable IP forwarding ───────────────────────────────────────────────────
info "Enabling IP forwarding..."
echo "net.ipv4.ip_forward = 1" > /etc/sysctl.d/99-wireguard.conf
sysctl -p /etc/sysctl.d/99-wireguard.conf > /dev/null

# ── 6. Update UFW ─────────────────────────────────────────────────────────────
info "Updating firewall rules..."

# WireGuard handshake port (public)
ufw allow "${WG_PORT}/udp" comment "WireGuard"

# Close 80/443 from the public internet
ufw delete allow 80/tcp  2>/dev/null || true
ufw delete allow 443/tcp 2>/dev/null || true
ufw delete allow 443/udp 2>/dev/null || true

# Allow 80/443 only from the VPN subnet
ufw allow from "${VPN_SUBNET}" to any port 80  proto tcp comment "HTTP (VPN only)"
ufw allow from "${VPN_SUBNET}" to any port 443 proto tcp comment "HTTPS (VPN only)"

# UFW needs to allow forwarded packets
sed -i 's/^DEFAULT_FORWARD_POLICY=.*/DEFAULT_FORWARD_POLICY="ACCEPT"/' /etc/default/ufw

ufw reload
info "Firewall updated:"
ufw status numbered

# ── 7. Enable WireGuard at boot ───────────────────────────────────────────────
info "Enabling WireGuard service..."
systemctl enable --now wg-quick@${WG_IFACE}

# ── 8. Store server public key for add-client.sh ─────────────────────────────
echo "${SERVER_PUBLIC_KEY}" > "${DEPLOY_DIR}/wireguard_server_public.key"
chown deploy:deploy "${DEPLOY_DIR}/wireguard_server_public.key"

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  WireGuard server is running!${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo ""
echo "  Interface : ${WG_IFACE} — ${VPN_SERVER_IP}/24"
echo "  Port      : UDP ${WG_PORT}"
echo "  Public key: ${SERVER_PUBLIC_KEY}"
echo ""
echo "Next step — add your first client:"
echo "  bash ${DEPLOY_DIR}/wireguard/add-client.sh <client-name>"
echo "  Example: bash ${DEPLOY_DIR}/wireguard/add-client.sh my-laptop"
echo ""
warn "Ports 80 and 443 are now CLOSED to the public. The app is VPN-only."
warn "Reconnect all existing Docker containers after switching to docker-compose.vpn.yml"
