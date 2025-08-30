FROM ghcr.io/digital-entropy/dokar-php/octane:8.1

# Install PostgreSQL client and tools for potential Rust integration
RUN apt-get update && apt-get install -y \
    postgresql-client \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Note: Rust services run in separate containers
# This container can communicate with them via gRPC or HTTP
