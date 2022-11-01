FROM phpswoole/swoole:php8.1-alpine

# Add OpCache
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add our PHP configuration
COPY ./php.custom.conf "/usr/local/etc/php/conf.d/zzz-shoutzor.ini"

# Add composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Switch to our working directory
WORKDIR /code

# Copy the default dotEnv file
COPY ./.env.default .env

# Copy PHP Project files
COPY ./ .

# Start Swoole HTTP Server
CMD ["php", "artisan", "octane:start", "--host=0.0.0.0"]