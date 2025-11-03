-   to deploy copy file to ssh root@cf.avolut.com in docker container app-okksscs4w0s8oc0go0k4cg8k
-   to query use local psql with server info in .env
-   when querying mysql use local mysql

## Route Generation Fix for Relative URLs

### Problem

Links were generating absolute URLs with localhost:8000 instead of relative URLs, causing broken links across different domains.

### Solution

1. **Custom Route Helper** (`resources/js/Libs/ziggy.js`):

    - Forces `absolute = false` for all route calls
    - Converts any absolute URLs to relative URLs
    - Handles undefined route names gracefully

2. **Ziggy Configuration** (`resources/views/app.blade.php`):
    - Sets Ziggy URL to current request scheme and host
    - Removes port to ensure relative URL generation

### Deployment Process

When deploying route generation fixes:

```bash
# Copy files to correct container location (/var/www/ not /var/www/html/)
scp resources/js/Libs/ziggy.js root@cf.avolut.com:/tmp/ziggy.js
ssh root@cf.avolut.com "docker cp /tmp/ziggy.js app-okksscs4w0s8oc0go0k4cg8k:/var/www/resources/js/Libs/ziggy.js"

# Rebuild assets from correct directory
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && npm run production'"

# Clear Laravel caches
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear'"
```

### Key Points

-   Laravel app is in `/var/www/` not `/var/www/html/`
-   Nginx serves from port 3000 internally
-   Always test with console logs to verify route helper is working
-   Check Ziggy config shows correct domain, not localhost

-   to deploy copy file to ssh root@cf.avolut.com in docker container app-okksscs4w0s8oc0go0k4cg8k
-   to query use local psql with server info in .env
-   when querying mysql use local mysql
