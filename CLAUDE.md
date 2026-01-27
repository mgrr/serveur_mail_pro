# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Docker-based professional mail server stack with:
- **Caddy** (v2.8): Reverse proxy with automatic HTTPS via Let's Encrypt
- **docker-mailserver**: Full-featured mail server (Postfix, Dovecot, SpamAssassin, ClamAV, Fail2ban, Postgrey)
- **Roundcube**: Webmail client with 2FA support (TOTP/Google Authenticator)
- **PostgreSQL** (v16): Database for Roundcube
- **Ofelia**: Scheduler for automatic SSL certificate reload (weekly)

## Architecture

```
Internet
    |
    +--► Caddy (:80, :443) --► Roundcube webmail
    |
    +--► Mailserver (:25 SMTP, :587 Submission, :993 IMAPS)
            |
            +--► Uses Caddy's Let's Encrypt certs from ./data/caddy/certificates/
```

**Networks:**
- `internet`: External-facing services (Caddy, Mailserver, Roundcube)
- `no-internet`: Internal only (PostgreSQL)

## Common Commands

```bash
# Start all services
docker compose up -d

# Rebuild Roundcube after modifying .docker/roundcube/
docker compose build roundcube && docker compose up -d roundcube

# View logs
docker compose logs -f [service_name]

# Manage mail accounts
docker exec -it mailserver setup email add user@domain.tld
docker exec -it mailserver setup email list
docker exec -it mailserver setup alias add alias@domain.tld target@domain.tld

# Generate DKIM key
docker exec -it mailserver setup config dkim
```

## Configuration

All domain-specific settings are in `.env` (copy from `.env.example`). Key variables:
- `DOMAIN_NAME`: Main domain (e.g., `exemple.fr`)
- `LETSENCRYPT_EMAIL`: Email for certificate notifications
- `POSTGRES_*`: Database credentials

Static mailserver settings are in `mailserver.env` (security features, protocols).

## Key Files

- `.env.example`: Template for environment variables
- `Caddyfile`: Uses `{$DOMAIN_NAME}` and `{$LETSENCRYPT_EMAIL}` from environment
- `mailserver.env`: Static mail server settings (SpamAssassin, ClamAV, etc.)
- `.docker/roundcube/custom.inc.php`: Roundcube config with 2FA plugin
- `init-data.sh`: PostgreSQL initialization (creates Roundcube database/user)
