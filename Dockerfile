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

# Copy existing application directory contents
COPY . /var/www

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Set up Python virtual environment and install requirements
RUN python3 -m venv /var/www/venv
RUN /var/www/venv/bin/pip install --no-cache-dir -r requirements.txt

# Install Node.js and build assets
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Copy dependency files first to leverage Docker cache
COPY package*.json ./
RUN npm install

# Copy the rest and build
COPY . .
RUN npm run build

# Final permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod +x /var/www/docker-entrypoint.sh

# Expose port 80
EXPOSE 80

# Environment variables for Python to use the venv
ENV PATH="/var/www/venv/bin:$PATH"

ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
