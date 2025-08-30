#!/bin/bash

# Legacy Database Import Script
# This script helps import the legacy MySQL database into the multi-tenant PostgreSQL setup

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
CLIENT_NAME="Legacy Client"
CLIENT_CODE="LEGACY"
MYSQL_HOST="107.155.75.50"
MYSQL_PORT="5654"
MYSQL_DATABASE="default"
MYSQL_USERNAME="mysql"
MYSQL_PASSWORD="S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK"
DRY_RUN=""

# Function to show usage
show_usage() {
    echo -e "${BLUE}Legacy Database Import Script${NC}"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -n, --client-name NAME    Name for the client (default: 'Legacy Client')"
    echo "  -c, --client-code CODE    Code for the client (default: 'LEGACY')"
    echo "  -h, --mysql-host HOST     MySQL host (default: '107.155.75.50')"
    echo "  -p, --mysql-port PORT     MySQL port (default: '5654')"
    echo "  -d, --mysql-database DB   MySQL database name (default: 'default')"
    echo "  -u, --mysql-username USER MySQL username (default: 'mysql')"
    echo "  -P, --mysql-password PASS MySQL password"
    echo "  --dry-run                 Run in dry-run mode (no changes made)"
    echo "  --verify                  Verify imported data"
    echo "  --help                    Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 --dry-run                                    # Test run"
    echo "  $0 --client-name 'ACME Corp' --client-code 'ACME'  # Import as ACME Corp"
    echo "  $0 --verify                                     # Verify existing import"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -n|--client-name)
            CLIENT_NAME="$2"
            shift 2
            ;;
        -c|--client-code)
            CLIENT_CODE="$2"
            shift 2
            ;;
        -h|--mysql-host)
            MYSQL_HOST="$2"
            shift 2
            ;;
        -p|--mysql-port)
            MYSQL_PORT="$2"
            shift 2
            ;;
        -d|--mysql-database)
            MYSQL_DATABASE="$2"
            shift 2
            ;;
        -u|--mysql-username)
            MYSQL_USERNAME="$2"
            shift 2
            ;;
        -P|--mysql-password)
            MYSQL_PASSWORD="$2"
            shift 2
            ;;
        --dry-run)
            DRY_RUN="--dry-run"
            shift
            ;;
        --verify)
            VERIFY="--verify"
            shift
            ;;
        --help)
            show_usage
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            show_usage
            exit 1
            ;;
    esac
done

# Show configuration
echo -e "${BLUE}=== Legacy Database Import Configuration ===${NC}"
echo -e "Client Name: ${GREEN}$CLIENT_NAME${NC}"
echo -e "Client Code: ${GREEN}$CLIENT_CODE${NC}"
echo -e "MySQL Host: ${GREEN}$MYSQL_HOST:$MYSQL_PORT${NC}"
echo -e "MySQL Database: ${GREEN}$MYSQL_DATABASE${NC}"
echo -e "MySQL Username: ${GREEN}$MYSQL_USERNAME${NC}"

if [[ -n "$DRY_RUN" ]]; then
    echo -e "Mode: ${YELLOW}DRY RUN${NC} (no changes will be made)"
elif [[ -n "$VERIFY" ]]; then
    echo -e "Mode: ${BLUE}VERIFY${NC} (checking imported data)"
else
    echo -e "Mode: ${RED}LIVE${NC} (changes will be made)"
fi

echo ""

# Confirm before proceeding (unless dry-run or verify)
if [[ -z "$DRY_RUN" && -z "$VERIFY" ]]; then
    echo -e "${YELLOW}⚠️  This will import data into your PostgreSQL database.${NC}"
    echo -e "${YELLOW}⚠️  Make sure you have a backup before proceeding.${NC}"
    echo ""
    read -p "Do you want to continue? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Import cancelled.${NC}"
        exit 1
    fi
fi

# Build the artisan command
CMD="php artisan import:legacy-db"
CMD="$CMD --client-name=\"$CLIENT_NAME\""
CMD="$CMD --client-code=\"$CLIENT_CODE\""
CMD="$CMD --mysql-host=\"$MYSQL_HOST\""
CMD="$CMD --mysql-port=\"$MYSQL_PORT\""
CMD="$CMD --mysql-database=\"$MYSQL_DATABASE\""
CMD="$CMD --mysql-username=\"$MYSQL_USERNAME\""
CMD="$CMD --mysql-password=\"$MYSQL_PASSWORD\""

if [[ -n "$DRY_RUN" ]]; then
    CMD="$CMD $DRY_RUN"
fi

if [[ -n "$VERIFY" ]]; then
    CMD="$CMD $VERIFY"
fi

# Run the import
echo -e "${BLUE}Running import command:${NC}"
echo -e "${GREEN}$CMD${NC}"
echo ""

# Execute the command
eval $CMD

echo ""
echo -e "${GREEN}✅ Import process completed!${NC}"

# Show next steps
if [[ -n "$DRY_RUN" ]]; then
    echo -e "${YELLOW}Next steps:${NC}"
    echo "1. Review the dry-run output above"
    echo "2. Run without --dry-run flag to perform actual import"
elif [[ -z "$VERIFY" ]]; then
    echo -e "${YELLOW}Next steps:${NC}"
    echo "1. Verify the import: $0 --verify"
    echo "2. Test the application with imported data"
    echo "3. Update client settings if needed"
fi