# Coolify Deployment Instructions for MDXM App

## Application Details
- **Application Name**: mdxm
- **Project**: Tools (UUID: sws0ckk)
- **Repository**: https://github.com/rizrmd/ionbec.exam
- **Branch**: main
- **Port**: 3000
- **Build Pack**: Dockerfile (already present in repo)

## Manual Setup Steps in Coolify UI

1. **Navigate to Coolify Dashboard**
   - Go to https://cf.avolut.com
   - Login with your credentials

2. **Create New Application**
   - Go to Projects → Tools
   - Click "New Resource" → "Public Repository"
   - Enter repository URL: `https://github.com/rizrmd/ionbec.exam`
   - Select branch: `main`
   - Name: `mdxm`

3. **Configure Build Settings**
   - Build Pack: Dockerfile
   - Base Directory: `/`
   - Dockerfile Location: `./Dockerfile`
   - Port: `3000`

4. **Environment Variables**
   Copy these environment variables to the application settings:

   ```env
   APP_NAME=MDXM
   APP_ENV=production
   APP_KEY=base64:BXmuQm+4JdpqL3GD+pTWlCBmE2+VjQjn2+yjLjqF43s=
   APP_DEBUG=false
   APP_URL=http://mdxm.107.155.75.50.sslip.io
   
   # Database (using existing external database)
   DB_CONNECTION=pgsql
   DB_HOST=107.155.75.50
   DB_PORT=5986
   DB_DATABASE=ionbec-new
   DB_USERNAME=postgres
   DB_PASSWORD=6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES
   
   # Redis (using existing Redis service - standalone-redis:u8s0cgsks4gcwo84ccskwok4)
   # Note: The Redis service name in Coolify network is typically the container name
   # You may need to adjust REDIS_HOST based on the actual container name
   REDIS_HOST=u8s0cgsks4gcwo84ccskwok4
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   
   # Session/Cache (using Redis)
   SESSION_DRIVER=redis
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_LIFETIME=120
   
   # Other
   BROADCAST_DRIVER=log
   FILESYSTEM_DRIVER=local
   MAIL_MAILER=log
   ```

5. **Domain Configuration**
   - Add domain: `mdxm.107.155.75.50.sslip.io`
   - Or use the auto-generated Coolify domain

6. **Deploy**
   - Click "Deploy"
   - Monitor the build logs
   - Application should be accessible at the configured domain

## Application Features
- Laravel 8.x application
- PostgreSQL database (external at 107.155.75.50:5986)
- Redis caching (using standalone-redis service in Coolify)
- Nginx + PHP-FPM
- Automated migrations on startup
- Health check on port 3000

## Redis Configuration
- **Existing Redis Service**: Your Coolify has a healthy Redis service (UUID: u8s0cgsks4gcwo84ccskwok4)
- **Container Name**: Typically accessible as `redis` within the Coolify network
- **Important**: If the application can't connect to Redis, check:
  1. The actual container name: `docker ps | grep redis`
  2. Network connectivity between app and Redis containers
  3. Update `REDIS_HOST` if the container name is different (e.g., `coolify-redis`, `redis-u8s0cgsks4gcwo84ccskwok4`)

## Post-Deployment Verification
1. Check application is running: `http://mdxm.107.155.75.50.sslip.io`
2. Verify database connection
3. Check Laravel logs if needed

## Troubleshooting
- If build fails, check Dockerfile syntax
- Ensure database is accessible from Coolify server
- Check environment variables are properly set
- Monitor application logs in Coolify dashboard

## Notes
- The application uses an external PostgreSQL database (not managed by Coolify)
- Redis is optional and can use Coolify's Redis service if available
- The Dockerfile includes nginx and PHP-FPM for serving the application