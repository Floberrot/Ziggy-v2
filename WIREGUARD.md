# Ziggy — WireGuard VPN Setup

The app is **not exposed to the internet**. Only devices connected to the WireGuard VPN can reach it.

```
Your device (anywhere)  ──WireGuard tunnel──►  VPS (10.8.0.1)  ──►  Ziggy app
                                                    │
                                          All traffic encrypted
                                          by WireGuard (ChaCha20)
```

**Why WireGuard?**
- Modern, fast, minimal codebase (~4000 lines vs OpenVPN's ~100k)
- Connects in milliseconds (stateless handshake)
- Works on every OS and mobile
- Split tunnel: only VPN-subnet traffic goes through the tunnel — your Netflix stays local

---

## Table of Contents

1. [Server setup](#1-server-setup)
2. [Add your first client](#2-add-your-first-client)
3. [Client installation](#3-client-installation)
   - [macOS](#macos)
   - [Windows](#windows)
   - [Linux](#linux)
   - [iPhone / iPad (iOS)](#iphone--ipad-ios)
   - [Android](#android)
4. [Access the app](#4-access-the-app)
5. [Add more devices](#5-add-more-devices)
6. [Remove a device](#6-remove-a-device)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Server setup

Run **once** on the VPS as root, after `setup-vps.sh` has already been executed.

```bash
# Upload the scripts to the VPS
scp -r deploy/wireguard root@YOUR_VPS_IP:/opt/ziggy/wireguard

# Connect and run
ssh root@YOUR_VPS_IP
chmod +x /opt/ziggy/wireguard/setup-wireguard.sh
bash /opt/ziggy/wireguard/setup-wireguard.sh
```

The script will:
- Install WireGuard
- Generate the server keypair
- Create the `wg0` interface at `10.8.0.1/24`
- Close ports 80 and 443 from the public internet
- Open UDP 51820 (WireGuard)
- Allow 80/443 only from the VPN subnet `10.8.0.0/24`

Then switch to the VPN-aware Docker stack:

```bash
# Upload VPN compose and Caddyfile
scp deploy/docker-compose.vpn.yml root@YOUR_VPS_IP:/opt/ziggy/
scp deploy/Caddyfile.vpn          root@YOUR_VPS_IP:/opt/ziggy/

# On the VPS — restart the stack with the VPN compose file
ssh deploy@YOUR_VPS_IP
cd /opt/ziggy

# Stop the current stack
docker compose -f docker-compose.prod.yml --env-file .env.prod down

# Start the VPN stack
docker compose -f docker-compose.vpn.yml --env-file .env.prod up -d
```

> The app is now at `http://10.8.0.1` — only reachable through the VPN.

---

## 2. Add your first client

Run on the VPS as **root** for each device you want to connect.

```bash
ssh root@YOUR_VPS_IP
chmod +x /opt/ziggy/wireguard/add-client.sh

# Laptop
bash /opt/ziggy/wireguard/add-client.sh my-laptop

# Phone
bash /opt/ziggy/wireguard/add-client.sh my-iphone

# A friend or pet-sitter
bash /opt/ziggy/wireguard/add-client.sh alice-phone
```

Each call:
- Assigns the next available VPN IP (`10.0.8.2`, `.3`, `.4` …)
- Generates a keypair + preshared key (forward-secrecy)
- Saves the client config to `/opt/ziggy/wireguard/clients/<name>/<name>.conf`
- Prints a **QR code** for mobile setup
- Registers the peer on the live server without any restart

---

## 3. Client installation

### macOS

1. Install WireGuard from the [Mac App Store](https://apps.apple.com/app/wireguard/id1451685025)
2. Download the `.conf` file from the VPS:
   ```bash
   scp root@YOUR_VPS_IP:/opt/ziggy/wireguard/clients/my-laptop/my-laptop.conf ~/Downloads/
   ```
3. Open **WireGuard** → **Import tunnel(s) from file** → select the `.conf`
4. Click **Activate**

---

### Windows

1. Download WireGuard from [wireguard.com/install](https://www.wireguard.com/install/)
2. Download the `.conf` file:
   ```powershell
   scp root@YOUR_VPS_IP:/opt/ziggy/wireguard/clients/my-laptop/my-laptop.conf C:\Users\You\Downloads\
   ```
3. Open **WireGuard** → **Import tunnel(s) from file** → select the `.conf`
4. Click **Activate**

---

### Linux

```bash
# Install
sudo apt install wireguard   # Debian/Ubuntu
# or
sudo dnf install wireguard-tools   # Fedora/RHEL

# Download config
scp root@YOUR_VPS_IP:/opt/ziggy/wireguard/clients/my-laptop/my-laptop.conf /etc/wireguard/

# Connect
sudo wg-quick up my-laptop

# Disconnect
sudo wg-quick down my-laptop

# Auto-start at boot (optional)
sudo systemctl enable wg-quick@my-laptop
```

---

### iPhone / iPad (iOS)

1. Install **WireGuard** from the [App Store](https://apps.apple.com/app/wireguard/id1441195209)
2. On the VPS, generate the client and display the QR:
   ```bash
   bash /opt/ziggy/wireguard/add-client.sh my-iphone
   # A QR code will appear in the terminal
   ```
3. Open the WireGuard app → tap **+** → **Create from QR code**
4. Scan the QR code shown in the terminal
5. Name it (e.g. "Ziggy VPN") and tap **Save**
6. Toggle the tunnel **on**

---

### Android

1. Install **WireGuard** from [Google Play](https://play.google.com/store/apps/details?id=com.wireguard.android) or [F-Droid](https://f-droid.org/packages/com.wireguard.android/)
2. On the VPS, generate the client and display the QR:
   ```bash
   bash /opt/ziggy/wireguard/add-client.sh my-android
   # A QR code will appear in the terminal
   ```
3. Open the WireGuard app → tap **+** → **Scan from QR code**
4. Scan the QR code
5. Name it and tap **Create tunnel**
6. Toggle it **on**

---

## 4. Access the app

Once the VPN tunnel is active on any device, open a browser and navigate to:

```
http://10.8.0.1
```

### Optional: friendly hostname

For a nicer URL (`http://ziggy.local`), add this line to your device's hosts file:

| OS | File |
|---|---|
| macOS / Linux | `/etc/hosts` |
| Windows | `C:\Windows\System32\drivers\etc\hosts` |
| iOS / Android | Requires a hosts app (e.g. **AdGuard**) or DNS override |

```
10.8.0.1    ziggy.local
```

Then access at `http://ziggy.local`.

---

## 5. Add more devices

Same as step 2 — one command per device:

```bash
ssh root@YOUR_VPS_IP
bash /opt/ziggy/wireguard/add-client.sh device-name
```

The peer is added to the live server **without any restart** — existing connections are not affected.

### List current peers

```bash
ssh root@YOUR_VPS_IP 'wg show wg0'
```

---

## 6. Remove a device

```bash
ssh root@YOUR_VPS_IP

# Get the client's public key
cat /opt/ziggy/wireguard/clients/alice-phone/public.key

# Remove peer from live server (immediate effect)
wg set wg0 peer <PUBLIC_KEY> remove

# Remove from wg0.conf (persists across reboots)
# Edit /etc/wireguard/wg0.conf and delete the [Peer] block for that client

# Delete client files
rm -rf /opt/ziggy/wireguard/clients/alice-phone
```

---

## 7. Troubleshooting

### Cannot connect to VPN

```bash
# On the VPS — check WireGuard is running
systemctl status wg-quick@wg0
wg show wg0

# Check port 51820 is open
ufw status | grep 51820

# Check the VPS public IP matches what's in the client config Endpoint field
curl https://api.ipify.org
```

### Connected to VPN but cannot reach the app

```bash
# From your device — ping the VPN gateway
ping 10.8.0.1

# From the VPS — check the app container is listening on the VPN IP
ss -tlnp | grep :80
# Should show: 10.8.0.1:80

# Check the Docker stack is running
docker compose -f /opt/ziggy/docker-compose.vpn.yml ps
```

### Handshake but no traffic

This usually means IP forwarding is disabled:

```bash
ssh root@YOUR_VPS_IP
sysctl net.ipv4.ip_forward
# Should return: net.ipv4.ip_forward = 1

# Fix if 0:
echo "net.ipv4.ip_forward = 1" > /etc/sysctl.d/99-wireguard.conf
sysctl -p /etc/sysctl.d/99-wireguard.conf
```

### VPN works but only inside home network, not outside

Make sure the `Endpoint` in the client config is your VPS **public IP**, not the WireGuard VPN IP:

```ini
[Peer]
Endpoint = YOUR_VPS_PUBLIC_IP:51820   # correct
# NOT:
# Endpoint = 10.8.0.1:51820           # wrong
```

### QR code not showing

```bash
# Install qrencode manually
apt install qrencode

# Re-display the QR for an existing client
qrencode -t ansiutf8 < /opt/ziggy/wireguard/clients/my-iphone/my-iphone.conf
```
