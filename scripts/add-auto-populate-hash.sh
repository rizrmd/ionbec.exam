#!/bin/bash

# Models that need AutoPopulateHash trait
models=(
    "app/Models/Attempts/Attempt.php"
    "app/Models/Categories/Category.php"
    "app/Models/Exams/Answer.php"
    "app/Models/Exams/Item.php"
    "app/Models/Exams/Question.php"
    "app/Models/Takers/Group.php"
    "app/Models/Takers/Taker.php"
)

for model in "${models[@]}"; do
    echo "Processing $model..."
    
    # Add import if not exists
    if ! grep -q "use App\\\\Traits\\\\AutoPopulateHash;" "$model"; then
        # Find the line with BelongsToClient or HashableId import and add after it
        sed -i '/use.*BelongsToClient;/a use App\\Traits\\AutoPopulateHash;' "$model"
        
        # If BelongsToClient not found, add after HashableId
        if ! grep -q "use App\\\\Traits\\\\AutoPopulateHash;" "$model"; then
            sed -i '/use.*HashableId;/a use App\\Traits\\AutoPopulateHash;' "$model"
        fi
    fi
    
    # Add trait usage if not exists
    if ! grep -q "use AutoPopulateHash;" "$model"; then
        # Find the line with "use BelongsToClient;" and add after it
        sed -i '/use BelongsToClient;/a \    use AutoPopulateHash;' "$model"
        
        # If BelongsToClient not found, add after HashableId
        if ! grep -q "use AutoPopulateHash;" "$model"; then
            sed -i '/use HashableId;/a \    use AutoPopulateHash;' "$model"
        fi
    fi
    
    echo "  ✓ Updated $model"
done

echo "All models updated with AutoPopulateHash trait!"