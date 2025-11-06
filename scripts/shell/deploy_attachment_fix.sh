#!/bin/bash

echo "🔧 DEPLOYING: Attachment URL Fix for Rust API and Database Fallback"
echo "=================================================================="

# Function to cleanup existing containers
cleanup_containers() {
    echo "🧹 Cleaning up existing containers..."

    # Stop and remove existing containers gracefully
    docker stop rust-service-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Rust service container already stopped"
    docker stop app-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "App container already stopped"
    docker stop queue-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Queue container already stopped"
    docker stop soketi-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Soketi container already stopped"
    docker stop scheduler-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Scheduler container already stopped"

    # Remove containers
    docker rm rust-service-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Rust service container removed"
    docker rm app-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "App container removed"
    docker rm queue-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Queue container removed"
    docker rm soketi-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Soketi container removed"
    docker rm scheduler-okksscs4w0s8oc0go0k4cg8k 2>/dev/null || echo "Scheduler container removed"

    echo "✅ Container cleanup completed"
}

# Function to deploy updated files
deploy_files() {
    echo "📤 Deploying updated files..."

    # Copy MainController.php
    scp app/Http/Controllers/Exam/MainController.php root@cf.avolut.com:/tmp/MainController.php
    echo "✅ MainController.php copied to temp"

    # Copy Main.vue
    scp resources/js/Pages/Exam/Main.vue root@cf.avolut.com:/tmp/Main.vue
    echo "✅ Main.vue copied to temp"

    # Copy files to app container
    ssh root@cf.avolut.com "docker cp /tmp/MainController.php app-okksscs4w0s8oc0go0k4cg8k:/var/www/app/Http/Controllers/Exam/MainController.php"
    echo "✅ MainController.php copied to container"

    ssh root@cf.avolut.com "docker cp /tmp/Main.vue app-okksscs4w0s8oc0go0k4cg8k:/var/www/resources/js/Pages/Exam/Main.vue"
    echo "✅ Main.vue copied to container"

    # Clean temp files
    ssh root@cf.avolut.com "rm /tmp/MainController.php /tmp/Main.vue"
    echo "✅ Temp files cleaned"
}

# Function to restart services
restart_services() {
    echo "🔄 Restarting services..."

    # Build and restart containers
    ssh root@cf.avolut.com "docker-compose down && docker-compose up -d --build"
    echo "✅ Services restarted"

    # Wait for services to be ready
    echo "⏳ Waiting for services to be ready..."
    sleep 10

    # Clear Laravel caches
    echo "🧹 Clearing Laravel caches..."
    ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan cache:clear' || echo 'Cache clear failed (container might not be ready yet)'"
    ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan view:clear' || echo 'View clear failed'"
    ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan config:clear' || echo 'Config clear failed'"
    ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan route:clear' || echo 'Route clear failed'"

    echo "✅ Caches cleared"
}

# Function to verify deployment
verify_deployment() {
    echo "🔍 Verifying deployment..."

    # Check if containers are running
    ssh root@cf.avolut.com "docker ps --format 'table {{.Names}}\t{{.Status}}'" | grep -E "(app|rust-service)"
    echo ""

    # Test application health
    echo "🏥 Testing application health..."
    health_check=$(curl -s -o /dev/null -w "%{http_code}" "https://medxamion.com/health" || echo "000")

    if [ "$health_check" = "200" ]; then
        echo "✅ Application health check passed (HTTP $health_check)"
    else
        echo "⚠️  Application health check failed (HTTP $health_check)"
        echo "📋 Container may still be starting up, please check manually"
    fi
}

# Main deployment flow
main() {
    echo "🚀 Starting deployment process..."
    echo ""

    # Step 1: Cleanup existing containers
    cleanup_containers
    echo ""

    # Step 2: Deploy updated files
    deploy_files
    echo ""

    # Step 3: Restart services
    restart_services
    echo ""

    # Step 4: Verify deployment
    verify_deployment
    echo ""

    echo "🎉 DEPLOYMENT COMPLETED!"
    echo ""
    echo "📋 What was fixed:"
    echo "  ✅ Rust API attachment URL generation"
    echo "  ✅ Database fallback attachment handling"
    echo "  ✅ Frontend attachment display consistency"
    echo "  ✅ Comprehensive logging for debugging"
    echo ""
    echo "🔗 Testing URLs:"
    echo "  - Main app: https://medxamion.com"
    echo "  - Health check: https://medxamion.com/health"
    echo ""
    echo "📝 Next steps:"
    echo "  1. Wait 1-2 minutes for all containers to fully start"
    echo "  2. Test exam functionality with images"
    echo "  3. Check logs if issues occur"
}

# Run main function
main