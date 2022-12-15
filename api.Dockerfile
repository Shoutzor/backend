# Can be "production" or "local"
ARG APP_ENV=production

FROM phpswoole/swoole:php8.1-alpine AS base-production

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

# For the dev version of this image we want to add
# NPM and chokidar as these are required for octane --watch
FROM base-production AS base-local
RUN apk add --update npm && npm install chokidar

# Build the final image depending on the APP_ENV
FROM base-${APP_ENV} AS final

# Start Swoole HTTP Server
CMD ["php", "artisan", "octane:start", "--host=0.0.0.0"]