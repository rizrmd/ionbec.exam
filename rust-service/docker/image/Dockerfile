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
    && rm -rf /var/lib/apt/lists/*
