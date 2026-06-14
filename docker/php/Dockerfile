FROM php:8.3-fpm-alpine

# ── Dependências do sistema ──────────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# ── Extensões PHP ────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        mbstring \
        opcache \
        bcmath \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ── Permissões e diretório de trabalho ───────────────────────────────────────
WORKDIR /var/www

RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www \
    && chown -R www:www /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]
