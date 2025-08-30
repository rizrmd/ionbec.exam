#!/bin/bash

# Test MySQL Connection Script
# This script tests the connection to the remote MySQL database

echo "========================================="
echo "MySQL Database Connection Test"
echo "========================================="

# Load environment variables
source /.env.mysql

echo ""
echo "Testing connection to remote MySQL database..."
echo "Host: $REMOTE_DB_HOST"
echo "Port: $REMOTE_DB_PORT"
echo "Database: $REMOTE_DB_DATABASE"
echo "User: $REMOTE_DB_USERNAME"
echo ""

# Test connection using mysql client
mysql -h "$REMOTE_DB_HOST" \
      -P "$REMOTE_DB_PORT" \
      -u "$REMOTE_DB_USERNAME" \
      -p"$REMOTE_DB_PASSWORD" \
      -e "SELECT VERSION() as 'MySQL Version', DATABASE() as 'Current Database', NOW() as 'Server Time';" \
      "$REMOTE_DB_DATABASE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Connection successful!"
    echo ""
    
    # Show database information
    mysql -h "$REMOTE_DB_HOST" \
          -P "$REMOTE_DB_PORT" \
          -u "$REMOTE_DB_USERNAME" \
          -p"$REMOTE_DB_PASSWORD" \
          -e "SHOW TABLES;" \
          "$REMOTE_DB_DATABASE" 2>/dev/null
    
    echo ""
    echo "========================================="
    echo "Connection test completed successfully"
    echo "========================================="
else
    echo ""
    echo "✗ Connection failed!"
    echo "Please check your credentials and network connectivity."
    echo "========================================="
    exit 1
fi