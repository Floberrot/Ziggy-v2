#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
# Ziggy — Add a WireGuard client (peer)
#
# Run on the VPS as root for each new device you want to connect.
# Generates a client config file and a QR code (for mobile).
#
# Usage:
#   bash /opt/ziggy/wireguard/add-client.sh <client-name>
#
# Examples:
#   bash /opt/ziggy/wireguard/add-client.sh my-laptop
#   bash /opt/ziggy/wireguard/add-client.sh my-iphone
#   bash /opt/ziggy/wireguard/add-client.sh work-pc
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

DEPLOY_DIR="/opt/ziggy"
WG_DIR="/etc/wireguard"
WG_IFACE="wg0"
WG_PORT="51820"
VPN_SERVER_IP="10.8.0.1"
CLIENTS_DIR="${DEPLOY_DIR}/wireguard/clients"

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

[[ $EUID -ne 0 ]] && error "Run as root."
[[ -z "${1:-}" ]] && error "Usage: $0 <client-name>  (e.g. my-laptop)"

CLIENT_NAME="$1"
# Sanitize: only alphanumeric and hyphens
CLIENT_NAME=$(echo "${CLIENT_NAME}" | tr -cd '[:alnum:]-' | tr '[:upper:]' '[:lower:]')
[[ -z "${CLIENT_NAME}" ]] && error "Invalid client name."

# ── Read server config ────────────────────────────────────────────────────────
[[ -f "${WG_DIR}/server_public.key" ]] || error "Server not set up. Run setup-wireguard.sh first."

SERVER_PUBLIC_KEY=$(cat "${WG_DIR}/server_public.key")
VPS_PUBLIC_IP=$(curl -s --max-time 5 https://api.ipify.org || ip route get 1 | awk '{print $7; exit}')

# ── Find next available IP ────────────────────────────────────────────────────
USED_IPS=$(grep -oP '10\.8\.0\.\K\d+' "${WG_DIR}/${WG_IFACE}.conf" 2>/dev/null || true)
NEXT_IP=2
while echo "${USED_IPS}" | grep -qw "${NEXT_IP}"; do
    NEXT_IP=$((NEXT_IP + 1))
done
[[ ${NEXT_IP} -gt 253 ]] && error "VPN subnet full (max 252 clients)."
CLIENT_IP="10.8.0.${NEXT_IP}"

# ── Generate client keypair ───────────────────────────────────────────────────
mkdir -p "${CLIENTS_DIR}"
chmod 700 "${CLIENTS_DIR}"

CLIENT_DIR="${CLIENTS_DIR}/${CLIENT_NAME}"
if [[ -d "${CLIENT_DIR}" ]]; then
    warn "Client '${CLIENT_NAME}' already exists at ${CLIENT_DIR}. Overwrite? [y/N]"
    read -r CONFIRM
    [[ "${CONFIRM}" =~ ^[Yy]$ ]] || { info "Aborted."; exit 0; }
fi
mkdir -p "${CLIENT_DIR}"
chmod 700 "${CLIENT_DIR}"

CLIENT_PRIVATE_KEY=$(wg genkey)
CLIENT_PUBLIC_KEY=$(echo "${CLIENT_PRIVATE_KEY}" | wg pubkey)
PRESHARED_KEY=$(wg genpsk)

echo "${CLIENT_PRIVATE_KEY}" > "${CLIENT_DIR}/private.key"
echo "${CLIENT_PUBLIC_KEY}"  > "${CLIENT_DIR}/public.key"
chmod 600 "${CLIENT_DIR}/private.key"

# ── Write client config ───────────────────────────────────────────────────────
CLIENT_CONF="${CLIENT_DIR}/${CLIENT_NAME}.conf"
cat > "${CLIENT_CONF}" <<EOF
[Interface]
# ${CLIENT_NAME} — ${CLIENT_IP}/32
Address    = ${CLIENT_IP}/32
DNS        = 1.1.1.1, 8.8.8.8
PrivateKey = ${CLIENT_PRIVATE_KEY}

[Peer]
# Ziggy VPS
PublicKey    = ${SERVER_PUBLIC_KEY}
PresharedKey = ${PRESHARED_KEY}
Endpoint     = ${VPS_PUBLIC_IP}:${WG_PORT}
# Only route VPN subnet through the tunnel (split tunnel — your other traffic stays local)
AllowedIPs   = 10.8.0.0/24
PersistentKeepalive = 25
EOF
chmod 600 "${CLIENT_CONF}"

# ── Register peer on the server ───────────────────────────────────────────────
info "Registering peer on the server..."

# Append to the wg0.conf for persistence across reboots
cat >> "${WG_DIR}/${WG_IFACE}.conf" <<EOF

[Peer]
# ${CLIENT_NAME} — added $(date '+%Y-%m-%d')
PublicKey    = ${CLIENT_PUBLIC_KEY}
PresharedKey = ${PRESHARED_KEY}
AllowedIPs   = ${CLIENT_IP}/32
EOF

# Apply live without restarting (existing connections stay up)
wg set "${WG_IFACE}" peer "${CLIENT_PUBLIC_KEY}" \
    preshared-key <(echo "${PRESHARED_KEY}") \
    allowed-ips "${CLIENT_IP}/32"

# ── Print QR code (install qrencode if missing) ───────────────────────────────
if ! command -v qrencode &>/dev/null; then
    apt-get install -y -qq qrencode
fi

echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Client '${CLIENT_NAME}' created — IP: ${CLIENT_IP}${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════════════════${NC}"
echo ""
echo "Config file: ${CLIENT_CONF}"
echo ""
echo "── For desktop/laptop — copy this file to your device:"
echo "   scp root@${VPS_PUBLIC_IP}:${CLIENT_CONF} ~/Downloads/${CLIENT_NAME}.conf"
echo ""
echo "── For mobile — scan the QR code below with the WireGuard app:"
echo ""
qrencode -t ansiutf8 < "${CLIENT_CONF}"
echo ""
echo "── VPN subnet: 10.8.0.0/24 (split tunnel)"
echo "   Your app will be available at: http://10.8.0.1"
echo "   Or add to /etc/hosts / phone hosts:  10.8.0.1  ziggy.local"
