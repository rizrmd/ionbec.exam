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

## Additional Fix: PHP Upload Limits

During troubleshooting, a Bad Gateway error was discovered due to insufficient PHP upload limits:

### Problem
- `upload_max_filesize = 2M` (too small for images)
- `post_max_size = 8M` (too small)
- `memory_limit = 128M` (may be insufficient for image processing)

### Solution
Create `/usr/local/etc/php/conf.d/uploads.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 60M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

Then restart the container to apply changes.

## Implementation Date
November 3, 2025

## Permanent Solution Implementation

To make this fix persistent across container restarts, the following libraries need to be installed and GD extension reconfigured:

### Required Libraries (must be installed before GD compilation):
```bash
apt update && apt install -y libjpeg-dev libpng-dev libfreetype-dev libwebp-dev libmagickwand-dev
```

### GD Extension Configuration:
```bash
docker-php-ext-configure gd --with-jpeg --with-freetype
docker-php-ext-install gd
```

### Alternative: Use ImageMagick
If GD continues to have issues, ImageMagick can be used as an alternative:
```bash
apt install -y libmagickwand-dev
docker-php-ext-install imagick
```

Then update Laravel config to use ImageMagick driver:
```php
// config/intervention.php
'driver' => 'imagick'
```

## Additional Error: "Maximum Resolution of 999 pixels"

If encountering validation error "The image has reached its maximum resolution of 999 pixels", this is likely:
1. Frontend validation (JavaScript) - check browser console for validation scripts
2. Intervention Image auto-orientation or size limits
3. Custom validation rules in Laravel Request classes

### Current Laravel Validation:
- File types: `jpeg,jpg,png`
- Max file size: `2048` KB (2MB)
- No resolution limits found in backend validation

## Maintenance Notes
- This fix needs to be applied whenever the container is rebuilt
- Consider adding the library installation to the Dockerfile for permanent solution
- The Intervention Image package depends on GD for image manipulation
- PHP upload limits may need to be adjusted based on actual upload requirements
- For permanent solution, create custom Docker image with these extensions pre-installed