# Build stage
FROM rust:latest as builder

# Install GCC and other build essentials
RUN apt-get update && apt-get install -y \
    build-essential \
    && rm -rf /var/lib/apt/lists/* \
    && gcc --version

WORKDIR /app

# Copy everything and build
COPY . .

# Build application
RUN cargo build --release

# Runtime stage
FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y \
    ca-certificates \
    libssl3 \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=builder /app/target/release/ionbec-rust-service /app/ionbec-rust-service

ENV RUST_LOG=info

EXPOSE 3000

ENTRYPOINT ["/app/ionbec-rust-service"]