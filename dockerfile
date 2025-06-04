# Use a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Atualiza o apt-get e instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    libpng-dev \
    zlib1g-dev \
    && apt-get clean

# # Instala as extensões necessárias
# RUN docker-php-ext-install pdo pdo_pgsql gd zip

# Habilita mod_rewrite do Apache para o Laravel
RUN a2enmod rewrite

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos da aplicação
COPY . .

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissões para o storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expõe a porta do Apache
EXPOSE 80

# Inicia o Apache
CMD ["apache2-foreground"]
