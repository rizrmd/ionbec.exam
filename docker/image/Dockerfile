FROM ghcr.io/digital-entropy/dokar-php/octane:8.1

ARG USER_CONTAINER=dokar
ARG PUID=1000
ARG PGID=1000

# Create user if it doesn't exist
RUN if ! grep -q ${PGID} /etc/group; then groupadd -g ${PGID} ${USER_CONTAINER}; fi && \
    useradd --gid ${PGID} --uid ${PUID} -m ${USER_CONTAINER}

# Install PostgreSQL client and tools for potential Rust integration
RUN apt-get update && apt-get install -y \
    postgresql-client \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Copy Laravel application files
COPY . /var/www

# Set proper permissions
RUN chown -R ${USER_CONTAINER}:${USER_CONTAINER} /var/www

# Switch to non-root user
USER ${USER_CONTAINER}

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader
