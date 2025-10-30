#!/bin/bash

# Corrige permissões no storage e bootstrap
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Executa migrações e otimizações
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Inicia o Apache
apache2-foreground
