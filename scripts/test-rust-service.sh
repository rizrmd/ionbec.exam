#!/bin/bash

echo "Testing Rust Service Integration"
echo "================================="

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Build and start services
echo -e "\n${YELLOW}Building Rust service...${NC}"
docker-compose build rust-service

echo -e "\n${YELLOW}Starting services...${NC}"
docker-compose up -d rust-service redis

# Wait for services to start
echo "Waiting for services to initialize..."
sleep 5

# Check if Rust service is running
echo -e "\n${YELLOW}Checking Rust service status...${NC}"
if docker-compose ps | grep -q "rust-service.*Up"; then
    echo -e "${GREEN}✓ Rust service is running${NC}"
else
    echo -e "${RED}✗ Rust service is not running${NC}"
    docker-compose logs --tail=20 rust-service
    exit 1
fi

# Test health endpoint
echo -e "\n${YELLOW}Testing health endpoint...${NC}"
HEALTH_RESPONSE=$(curl -s http://localhost:3000/health)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Health endpoint accessible${NC}"
    echo "Response: $HEALTH_RESPONSE"
else
    echo -e "${RED}✗ Health endpoint failed${NC}"
fi

# Test process endpoint
echo -e "\n${YELLOW}Testing process endpoint...${NC}"
PROCESS_RESPONSE=$(curl -s -X POST http://localhost:3000/api/process \
    -H "Content-Type: application/json" \
    -d '{"task_type":"exam_validation","data":{"test":"data"}}')
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Process endpoint accessible${NC}"
    echo "Response: $PROCESS_RESPONSE"
else
    echo -e "${RED}✗ Process endpoint failed${NC}"
fi

# Test score calculation
echo -e "\n${YELLOW}Testing score calculation...${NC}"
SCORE_RESPONSE=$(curl -s -X POST http://localhost:3000/api/score \
    -H "Content-Type: application/json" \
    -d '{"exam_id":1,"answers":[{"question_id":1,"answer":"A"},{"question_id":2,"answer":"B"}]}')
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Score calculation endpoint accessible${NC}"
    echo "Response: $SCORE_RESPONSE"
else
    echo -e "${RED}✗ Score calculation failed${NC}"
fi

# Test exam validation
echo -e "\n${YELLOW}Testing exam validation...${NC}"
VALIDATE_RESPONSE=$(curl -s -X POST http://localhost:3000/api/validate \
    -H "Content-Type: application/json" \
    -d '{"exam_id":1,"questions":[{"id":1,"text":"Question 1?","options":["A","B","C"],"correct_answer":"A"}]}')
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Validation endpoint accessible${NC}"
    echo "Response: $VALIDATE_RESPONSE"
else
    echo -e "${RED}✗ Validation endpoint failed${NC}"
fi

# Test PHP integration
echo -e "\n${YELLOW}Testing PHP integration...${NC}"
docker-compose exec -T octane php -r "
require '/var/www/vendor/autoload.php';
\$app = require_once '/var/www/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

try {
    \$service = new \App\Services\RustService();
    \$health = \$service->health();
    echo 'Health check: ' . json_encode(\$health) . PHP_EOL;
    
    \$score = \$service->calculateScore(1, [
        ['question_id' => 1, 'answer' => 'A'],
        ['question_id' => 2, 'answer' => 'B']
    ]);
    echo 'Score calculation: ' . json_encode(\$score) . PHP_EOL;
    
    echo 'PHP integration: ✓ Success' . PHP_EOL;
} catch (Exception \$e) {
    echo 'PHP integration: ✗ Failed - ' . \$e->getMessage() . PHP_EOL;
}
"

echo -e "\n${YELLOW}=================================${NC}"
echo "Test Summary:"
echo -e "${GREEN}Rust service is integrated and running on port 3000${NC}"
echo ""
echo "Available endpoints:"
echo "  - GET  http://localhost:3000/health"
echo "  - POST http://localhost:3000/api/process"
echo "  - POST http://localhost:3000/api/score"
echo "  - POST http://localhost:3000/api/validate"
echo ""
echo "PHP Service: App\Services\RustService"
echo ""
echo "To view logs:"
echo "  docker-compose logs -f rust-service"