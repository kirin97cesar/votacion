FROM php:8.1-fpm

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
    unzip && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Obtener la última versión de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar el archivo de variables de entorno
COPY .env .env
RUN chmod 644 .env

# Exponer el puerto del contenedor
EXPOSE 9000

# Usar usuario no root
USER $user

# Comando de inicio
CMD ["php-fpm"]
