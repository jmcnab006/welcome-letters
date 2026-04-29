FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        wget \
        ca-certificates \
        libonig-dev \
    && docker-php-ext-install mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy app code
COPY public/ ./public/
COPY includes/ ./includes/

# Create expected mount points
RUN mkdir -p ./templates ./includes/vendor/phpmailer ./config

# Install PHPMailer manually
RUN wget -q -O includes/vendor/phpmailer/PHPMailer.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php \
    && wget -q -O includes/vendor/phpmailer/SMTP.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php \
    && wget -q -O includes/vendor/phpmailer/Exception.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php

# Apache serve /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
