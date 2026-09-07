FROM wordpress:latest

# Ensure .htaccess overrides are enabled for /var/www/html
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Fix ownership on startup
RUN echo '#!/bin/bash\n\
chown -R www-data:www-data /var/www/html\n\
find /var/www/html -type d -exec chmod 755 {} +\n\
find /var/www/html -type f -exec chmod 644 {} +\n\
chmod 755 /var/www/html/wp-admin/post.php /var/www/html/wp-admin/admin-ajax.php /var/www/html/wp-admin/admin-post.php /var/www/html/wp-admin/load-scripts.php /var/www/html/wp-admin/load-styles.php 2>/dev/null || true\n\
exec docker-entrypoint.sh apache2-foreground\n' > /usr/local/bin/fix-and-start.sh && chmod +x /usr/local/bin/fix-and-start.sh

ENTRYPOINT ["/usr/local/bin/fix-and-start.sh"]
