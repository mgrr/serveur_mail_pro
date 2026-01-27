# Serveur Mail Professionnel

Serveur mail complet basé sur Docker avec webmail Roundcube, anti-spam, antivirus et authentification 2FA.

## Composants

| Service | Description |
|---------|-------------|
| **docker-mailserver** | Serveur mail (Postfix, Dovecot, SpamAssassin, ClamAV, Fail2ban) |
| **Roundcube** | Webmail avec support 2FA (Google Authenticator/TOTP) |
| **Caddy** | Reverse proxy avec certificats Let's Encrypt automatiques |
| **PostgreSQL** | Base de données pour Roundcube |
| **Ofelia** | Planificateur pour le rechargement SSL hebdomadaire |

## Prerequis

- Docker et Docker Compose installes
- Un serveur avec une IP publique
- Un nom de domaine avec acces aux enregistrements DNS
- Ports 25, 80, 443, 587 et 993 ouverts

## Installation

### 1. Cloner le depot

```bash
git clone <url-du-repo>
cd serveur_mail_pro
```

### 2. Configurer les variables d'environnement

```bash
cp .env.example .env
nano .env
```

Remplissez les valeurs :

```env
# Votre nom de domaine (sans le sous-domaine mail.)
DOMAIN_NAME=mondomaine.fr

# Configuration PostgreSQL
POSTGRES_DB=postgres
POSTGRES_USER=postgres
POSTGRES_PASSWORD=<mot_de_passe_admin_securise>

# Compte Roundcube (droits limites)
POSTGRES_NO_ROOT_USER=roundcube
POSTGRES_NO_ROOT_PASSWORD=<mot_de_passe_roundcube_securise>
DATABASE_NAME=roundcubemail

# Email pour les notifications Let's Encrypt
LETSENCRYPT_EMAIL=admin@mondomaine.fr
```

### 3. Configurer les enregistrements DNS

Ajoutez ces enregistrements DNS chez votre registrar :

| Type | Nom | Valeur |
|------|-----|--------|
| A | mail | `<IP_DU_SERVEUR>` |
| MX | @ | `mail.mondomaine.fr` (priorite 10) |
| TXT | @ | `v=spf1 mx ~all` |
| TXT | _dmarc | `v=DMARC1; p=quarantine; rua=mailto:postmaster@mondomaine.fr` |

La cle DKIM sera generee au premier demarrage (voir section DKIM).

### 4. Demarrer les services

```bash
# Construire l'image Roundcube personnalisee
docker compose build

# Demarrer tous les services
docker compose up -d

# Verifier que tout fonctionne
docker compose ps
docker compose logs -f
```

### 5. Creer le premier compte email

```bash
docker exec -it mailserver setup email add utilisateur@mondomaine.fr
```

### 6. Configurer DKIM

Generez la cle DKIM :

```bash
docker exec -it mailserver setup config dkim
```

Recuperez la cle publique :

```bash
docker exec -it mailserver cat /tmp/docker-mailserver/opendkim/keys/mondomaine.fr/mail.txt
```

Ajoutez l'enregistrement TXT dans votre DNS :
- **Nom** : `mail._domainkey`
- **Valeur** : le contenu entre parentheses (sans les guillemets ni retours a la ligne)

### 7. Acceder au webmail

Rendez-vous sur `https://mail.mondomaine.fr` et connectez-vous avec votre compte email.

## Gestion des comptes

```bash
# Lister les comptes
docker exec -it mailserver setup email list

# Ajouter un compte
docker exec -it mailserver setup email add user@mondomaine.fr

# Supprimer un compte
docker exec -it mailserver setup email del user@mondomaine.fr

# Ajouter un alias
docker exec -it mailserver setup alias add alias@mondomaine.fr destinataire@mondomaine.fr
```

## Maintenance

### Logs

```bash
# Tous les logs
docker compose logs -f

# Logs d'un service specifique
docker compose logs -f mailserver
docker compose logs -f caddy
```

### Mise a jour

```bash
docker compose pull
docker compose build
docker compose up -d
```

### Sauvegarde

Les donnees importantes sont dans :
- `./mailserver/data/` : emails
- `./mailserver/config/` : configuration mailserver (comptes, DKIM, etc.)
- Volume `db_storage` : base de donnees Roundcube

```bash
# Sauvegarder la base de donnees
docker exec postgres pg_dump -U roundcube roundcubemail > backup_roundcube.sql
```

## Securite

- **SpamAssassin** : filtrage anti-spam
- **ClamAV** : antivirus
- **Fail2ban** : protection contre les attaques brute-force
- **Postgrey** : greylisting
- **2FA** : authentification a deux facteurs dans Roundcube (Settings > 2-Factor Authentication)

## Ports utilises

| Port | Protocole | Description |
|------|-----------|-------------|
| 25 | SMTP | Reception des emails |
| 80 | HTTP | Redirection vers HTTPS |
| 443 | HTTPS | Webmail Roundcube |
| 587 | Submission | Envoi des emails (avec authentification) |
| 993 | IMAPS | Lecture des emails (chiffre) |

## Depannage

### Les certificats ne se chargent pas

Verifiez que Caddy a bien genere les certificats :

```bash
ls -la ./data/caddy/certificates/acme-v02.api.letsencrypt.org-directory/
```

### Erreur de connexion IMAP/SMTP dans Roundcube

Les certificats internes utilisent un certificat auto-signe. C'est normal, la verification SSL est desactivee entre les conteneurs.

### Les emails sont rejetes

Verifiez vos enregistrements DNS (SPF, DKIM, DMARC) avec [MXToolbox](https://mxtoolbox.com/).

## Licence

MIT License - Voir [LICENSE](LICENSE)
