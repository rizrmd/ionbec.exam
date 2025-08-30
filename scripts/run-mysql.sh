#!/bin/bash

# Load environment variables from .env file if it exists
if [ -f .env ]; then
    echo "Loading environment variables from .env file..."
    export $(cat .env | grep -v '^#' | xargs)
fi

# Set default values if not provided
DATABASE_URL=${DATABASE_URL:-mysql://mysql:S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK@107.155.75.50:5654/default}
REDIS_URL=${REDIS_URL:-redis://localhost:6379}

echo "Starting database client container..."
echo ""

# Check if image exists
if [[ "$(docker images -q mysql-client:latest 2> /dev/null)" == "" ]]; then
    echo "Image not found. Building it first..."
    ./build-mysql.sh
fi

# Run the container
docker run -it --rm \
    --name mysql-client \
    -e DATABASE_URL="$DATABASE_URL" \
    -e REDIS_URL="$REDIS_URL" \
    mysql-client:latest "$@"