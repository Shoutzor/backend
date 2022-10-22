FROM php:8-cli-alpine

WORKDIR /code

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add our PHP configuration
COPY ./php.custom.conf "/usr/local/etc/php-fpm.d/zzz-shoutzor.conf"

# Add PHP Project files
COPY ./ .

# Add composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install Redis driver
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Start the Queue Worker
CMD ["php", "artisan", "queue:work", "--queue=uploads"]