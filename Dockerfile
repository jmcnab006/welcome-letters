FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends wget ca-certificates libonig-dev \
    && docker-php-ext-install mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

WORKDIR /var/www/welcome-letters

COPY app/ ./app/
COPY public/ ./public/
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf

# get the most recent version of phpmailer
RUN mkdir -p ./app/vendor/phpmailer \
    && wget -q -O app/vendor/phpmailer/PHPMailer.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php \
    && wget -q -O app/vendor/phpmailer/SMTP.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php \
    && wget -q -O app/vendor/phpmailer/Exception.php https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php

RUN chown -R www-data:www-data /var/www/welcome-letters \
    && find /var/www/welcome-letters -type d -exec chmod 755 {} \; \
    && find /var/www/welcome-letters -type f -exec chmod 644 {} \;

EXPOSE 80