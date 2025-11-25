FROM php:8.2-apache

# 1. Instala dependências do sistema, drivers e bibliotecas gráficas (WKHTMLTOPDF)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    zip \
    libxrender1 \
    libfontconfig1 \
    libx11-dev \
    libjpeg62-turbo \
    libxtst6 \
    wget \
    fontconfig \
    xfonts-75dpi \
    xfonts-base \
    xz-utils \
    && docker-php-ext-install pdo pdo_pgsql gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Baixa e instala o WKHTMLTOPDF
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb \
    && apt-get install -y ./wkhtmltox_0.12.6.1-3.bookworm_amd64.deb \
    && rm wkhtmltox_0.12.6.1-3.bookworm_amd64.deb

# 3. Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# 4. Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. --- CORREÇÃO DEFINITIVA DO APACHE ---
# Sobrescreve o arquivo de site padrão para apontar para /public e liberar o .htaccess
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html