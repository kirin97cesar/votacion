# Usamos PHP 7.4 con FPM
FROM php:7.4-fpm

# Argumentos definidos en docker-compose.yml
ARG user=appuser
ARG uid=1000

# Instalar dependencias del sistema y limpiar caché en un solo paso
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para evitar correr como root
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos del proyecto al contenedor
COPY . /var/www/html

# Copiar configuración de Nginx
COPY nginx/default.conf /etc/nginx/nginx.conf

# Cambiar permisos de storage y bootstrap/cache para evitar problemas
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Copiar archivo de entorno .env
COPY .env .env
RUN chmod 644 .env

# Exponer el puerto 80
EXPOSE 80

# Usar usuario root (para iniciar Nginx sin problemas)
USER root

# Ejecutar migraciones y seeders antes de iniciar Nginx y PHP-FPM
# CMD php artisan migrate --force && php artisan db:seed --force && service nginx start && php-fpm
CMD service nginx start && php-fpm
