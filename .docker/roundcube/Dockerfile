FROM roundcube/roundcubemail:latest

# Installation de git et du plugin 2FA (Google Authenticator / TOTP)
RUN apt-get update && \
    apt-get install -y git && \
    git clone https://github.com/alexandregz/twofactor_gauthenticator.git /usr/src/roundcubemail/plugins/twofactor_gauthenticator && \
    chown -R www-data:www-data /usr/src/roundcubemail/plugins/twofactor_gauthenticator && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuration personnalisée
RUN mkdir -p /var/roundcube/config
COPY custom.inc.php /var/roundcube/config/custom.inc.php
