# # Use PHP 7.4 with Apache
# FROM php:7.4-apache

# # Enable Apache rewrite module
# RUN a2enmod rewrite

# # Install required PHP extensions
# RUN docker-php-ext-install pdo pdo_mysql mysqli

# # Set working directory
# WORKDIR /var/www/html

# # Allow .htaccess overrides
# RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# # Expose port 80
# EXPOSE 80

# # Start Apache in the foreground
# CMD ["apache2-foreground"]


# Use PHP 7.4 with Apache
FROM php:7.4-apache

# Set environment variables
ENV APACHE_DOCUMENT_ROOT=/var/www/html \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_TIMEZONE=UTC

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    curl \
    xml \
    opcache

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure PHP
RUN echo "date.timezone = ${PHP_TIMEZONE}" > /usr/local/etc/php/conf.d/timezone.ini \
    && echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "session.gc_maxlifetime = 14400" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Create composer.json and install PHPMailer
RUN echo '{"require":{}}' > composer.json && \
    composer require phpmailer/phpmailer --no-interaction --no-scripts

# Install AWS SDK for PHP
RUN composer require aws/aws-sdk-php --no-interaction --no-scripts

# Fix 1: Create required directories with proper permissions
RUN mkdir -p /var/www/html/assets \
    && mkdir -p /var/www/html/protected/runtime \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/assets \
    && chmod -R 777 /var/www/html/protected/runtime

# Fix 2: Create missing CErrorAction.php
RUN mkdir -p /var/www/yii/framework/web/actions && \
    echo '<?php' > /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo 'class CErrorAction extends CAction' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '{' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '    public $view = "error";' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '    public function run()' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '    {' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '        if ($error = Yii::app()->errorHandler->error) {' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '            if (Yii::app()->request->isAjaxRequest)' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '                echo $error["message"];' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '            else' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '                $this->controller->render($this->view, $error);' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '        }' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '    }' >> /var/www/yii/framework/web/actions/CErrorAction.php && \
    echo '}' >> /var/www/yii/framework/web/actions/CErrorAction.php

# Fix 3: Compatibility fix for get_magic_quotes_gpc() (PHP 7.4+)
RUN echo '<?php if (!function_exists("get_magic_quotes_gpc")) { function get_magic_quotes_gpc() { return false; } } ?>' > /var/www/html/fix.php

# Fix 4: Create a default index.php if not exists
RUN echo '<?php' > /var/www/html/index.php \
    && echo '// PHP 7.4+ compatibility fix' >> /var/www/html/index.php \
    && echo 'require_once("fix.php");' >> /var/www/html/index.php \
    && echo '' >> /var/www/html/index.php \
    && echo '// Yii framework path' >> /var/www/html/index.php \
    && echo '$yii = "/var/www/yii/framework/yii.php";' >> /var/www/html/index.php \
    && echo 'defined("YII_DEBUG") or define("YII_DEBUG", true);' >> /var/www/html/index.php \
    && echo 'defined("YII_TRACE_LEVEL") or define("YII_TRACE_LEVEL", 3);' >> /var/www/html/index.php \
    && echo '' >> /var/www/html/index.php \
    && echo 'require_once($yii);' >> /var/www/html/index.php \
    && echo 'Yii::createWebApplication()->run();' >> /var/www/html/index.php

# In your Dockerfile, add these lines
RUN echo "error_log = /var/log/php/error.log" >> /usr/local/etc/php/php.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
RUN echo "display_errors = Off" >> /usr/local/etc/php/php.ini
RUN mkdir -p /var/log/php && chmod 777 /var/log/php

# Install Google Authenticator library for 2FA
RUN composer require sonata-project/google-authenticator --no-interaction --no-scripts


# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]