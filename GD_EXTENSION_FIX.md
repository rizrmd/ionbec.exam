# GD Extension Setup Guide

## Problem
Image upload functionality was failing with error:
```
Call to undefined function Intervention\Image\Gd\imagecreatefromjpeg()
```

## Root Cause
The GD extension in the Docker container was installed but without JPEG support. The `imagecreatefromjpeg()` function was not available.

## Solution

### 1. Install Required Libraries
```bash
# Install JPEG, PNG, FreeType, and WebP development libraries
docker exec <container> bash -c 'apt update && apt install -y libjpeg-dev libpng-dev libfreetype-dev libwebp-dev'
```

### 2. Reconfigure GD Extension
```bash
# Reconfigure GD extension with JPEG and FreeType support
docker exec <container> docker-php-ext-configure gd --with-jpeg --with-freetype
```

### 3. Reinstall GD Extension
```bash
# Install GD extension with new configuration
docker exec <container> docker-php-ext-install gd
```

### 4. Restart Container
```bash
# Restart container to reload the extension
docker restart <container>
```

### 5. Verify Installation
```bash
# Check GD extension configuration
docker exec <container> php -i | grep -A 10 'GD Support'

# Test JPEG function availability
docker exec <container> php -r "if (function_exists('imagecreatefromjpeg')) { echo 'JPEG support: SUCCESS\n'; } else { echo 'JPEG support: FAILED\n'; }"
```

## Expected Output
After successful installation, you should see:
```
GD Support => enabled
GD Version => bundled (2.1.0 compatible)
FreeType Support => enabled
JPEG Support => enabled
libJPEG Version => 6b
PNG Support => enabled
libPNG Version => 1.6.48
```

## Container Information
- Container name: `app-okksscs4w0s8oc0go0k4cg8k`
- Server: `cf.avolut.com`
- Application path: `/var/www/`

## Files Using GD Extension
- `app/Http/Controllers/BackOffice/QuestionSetController.php:301` - Image processing for question attachments

## Implementation Date
November 3, 2025

## Maintenance Notes
- This fix needs to be applied whenever the container is rebuilt
- Consider adding the library installation to the Dockerfile for permanent solution
- The Intervention Image package depends on GD for image manipulation