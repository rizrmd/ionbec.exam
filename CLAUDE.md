- to deploy copy file to ssh root@cf.avolut.com in docker container app-okksscs4w0s8oc0go0k4cg8k
- to query use local psql with server info in .env
- when querying mysql use local mysql

## File Organization Guidelines

### IMPORTANT: Keep Root Directory Clean
- **NEVER create files in the root directory** - only Laravel core files should remain there
- All utility scripts go in appropriate `scripts/` subdirectories:
  - `scripts/debug/` - Debug and analysis scripts
  - `scripts/utils/` - Utility scripts
  - `scripts/maintenance/` - Maintenance scripts
  - `scripts/imports/` - Import scripts
  - `scripts/shell/` - Shell scripts
- All data files go in `data/` subdirectories:
  - `data/csv/` - CSV files
  - `data/sql/` - SQL backup files
  - `data/` - JSON and other data files
- Documentation goes in `docs/`
- Backups go in `data/sql/`

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
- Laravel app is in `/var/www/` not `/var/www/html/`
- Nginx serves from port 3000 internally
- Always test with console logs to verify route helper is working
- Check Ziggy config shows correct domain, not localhost
- VERY IMPORTANT: do not run database seed, do not truncate any table.
- **CRITICAL: Never create files in root directory - use proper organized folders!**
- to rebuild rust-service we just need to commit and push the project, it will automatically build the rust service