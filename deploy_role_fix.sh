#!/bin/bash

echo "Deploying role display fix to production server..."

# Copy the fixed UserManagementController to production
scp app/Http/Controllers/BackOffice/UserManagementController.php root@cf.avolut.com:/tmp/UserManagementController.php

# Copy to the correct container location
ssh root@cf.avolut.com "docker cp /tmp/UserManagementController.php app-okksscs4w0s8oc0go0k4cg8k:/var/www/app/Http/Controllers/BackOffice/UserManagementController.php"

# Clear Laravel caches to refresh the loaded classes
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan cache:clear'"
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan view:clear'"
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan config:clear'"
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan route:clear'"

echo "✅ Role display fix deployed successfully!"
echo "📋 Next steps:"
echo "  1. Refresh the browser page: https://ionbec.com/back-office/users?client_id=3"
echo "  2. Check if roles now appear in the ROLES column"
echo "  3. Test user editing to see if roles are displayed correctly"