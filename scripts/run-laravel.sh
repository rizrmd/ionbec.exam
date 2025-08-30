#!/bin/bash

# Load environment variables from .env file if it exists
if [ -f .env ]; then
    echo "Loading environment variables from .env file..."
    export $(cat .env | grep -E '^(DATABASE_URL|REDIS_URL)=' | xargs)
fi

# Set default values if not provided
DATABASE_URL=${DATABASE_URL:-mysql://mysql:S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK@107.155.75.50:5654/default}
REDIS_URL=${REDIS_URL:-redis://default:43bgTxX07rGOxcDeD2Z67qc57qSAH39KEUJXCHap7W613KVNZPnLaOBdBG2Z0Yq6@107.155.75.50:9675/0}

echo "Starting Laravel application container..."
echo ""

# Check if image exists
if [[ "$(docker images -q laravel-app:latest 2> /dev/null)" == "" ]]; then
    echo "Image not found. Building it first..."
    ./build-laravel.sh
fi

# Default command
CMD=${1:-bash}

if [ "$CMD" == "serve" ]; then
    # Run Laravel development server
    docker run -it --rm \
        --name laravel-app \
        -p 8000:8000 \
        -e DATABASE_URL="$DATABASE_URL" \
        -e REDIS_URL="$REDIS_URL" \
        laravel-app:latest
else
    # Run interactive or specific command
    docker run -it --rm \
        --name laravel-app \
        -e DATABASE_URL="$DATABASE_URL" \
        -e REDIS_URL="$REDIS_URL" \
        laravel-app:latest "$@"
fi