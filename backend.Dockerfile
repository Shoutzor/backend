FROM php:8-fpm-alpine

WORKDIR /code

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add our PHP configuration
COPY ./php.custom.conf "$PHP_INI_DIR/conf.d/php.custom.conf"

# Add PHP Project files
COPY ./ .

# Add composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install Redis driver
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql
