FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    ffmpeg \
    python3 \
    python3-pip \
    python3-venv \
    libpq-dev \
    libpq5 \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Install Node.js
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# --- OPTIMIZATION: LAYER CACHING ---

# 1. Composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# 2. Python
COPY requirements.txt ./
RUN python3 -m venv /var/www/venv && \
    /var/www/venv/bin/pip install --no-cache-dir -r requirements.txt

# 3. NPM
COPY package.json package-lock.json ./
RUN npm install

# 4. Copy rest of the code
COPY . /var/www

# Build assets
RUN npm run build

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod +x /var/www/docker-entrypoint.sh

# Expose port 80
EXPOSE 80

# Env for Python
ENV PATH="/var/www/venv/bin:$PATH"

ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
