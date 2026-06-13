FROM php:8.3-fpm

# ১. সিস্টেম ডিপেন্ডেন্সি এবং টুলস ইনস্টল
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    nginx

# ২. PHP এক্সটেনশন ইনস্টল
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# ৩. কম্পোজার (Composer) কপি করা
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ৪. প্রজেক্ট ফাইল কন্টেইনারে কপি করা
WORKDIR /var/www
COPY . /var/www

# ۵. কম্পোজার ডিপেন্ডেন্সি ইনস্টল
RUN composer install --no-interaction --optimize-autoloader --no-dev

# ৬. শুধুমাত্র ফিলামেন্ট অ্যাসেট জেনারেট করা
RUN php artisan filament:assets

# ৭. রেন্ডারের জন্য Nginx কনফিগারেশন এবং ডিরেক্টরি পারমিশন সেটআপ
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ৮. Nginx ডিফল্ট সাইট কনফিগ তৈরি
RUN echo 'server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    root /var/www/public;\n\
    \n\
    location /build/ {\n\
        try_files $uri $uri/ =404;\n\
    }\n\
    location /vendor/ {\n\
        try_files $uri $uri/ =404;\n\
    }\n\
    \n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    \n\
    location ~ \.php$ {\n\
        try_files $uri =404;\n\
        fastcgi_split_path_info ^(.+\.php)(/.+)$;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        fastcgi_param PATH_INFO $fastcgi_path_info;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# ৯. কন্টেইনার স্টার্ট হওয়ার কমান্ড
CMD service nginx start && php-fpm
