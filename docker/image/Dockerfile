FROM ghcr.io/digital-entropy/dokar-php/octane:8.1

# Install PostgreSQL client and tools
RUN apt-get update && apt-get install -y \
    postgresql-client \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Copy Laravel application files
COPY . /var/www

# Install composer dependencies as root
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Install npm dependencies and build assets as root
RUN npm ci && npm run production

# Run Laravel post-install scripts
RUN composer run-script post-autoload-dump

# Set proper permissions for www-data user
RUN chown -R www-data:www-data /var/www

# Clean up node_modules to reduce image size (optional)
RUN rm -rf /var/www/node_modules

# The base image will handle the user switching
