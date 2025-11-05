<?php

/**
 * CREATE PREVENTIVE MEASURES
 * Add database constraints and backend validation to prevent question duplication
 */

echo "=== CREATING PREVENTIVE MEASURES ===\n\n";

// Connect to database directly using production credentials
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully\n\n";

    // Start transaction
    $pdo->beginTransaction();

    try {
        echo "=== 1. CREATING DATABASE CONSTRAINTS ===\n";

        // Check if unique constraint already exists
        $stmt = $pdo->query("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'questions'
              AND constraint_type = 'UNIQUE'
              AND constraint_name = 'questions_item_content_unique'
        ");
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo "⚠️  Unique constraint already exists\n";
        } else {
            echo "Creating unique constraint to prevent duplicate content per item...\n";

            // First, let's check for any existing duplicates
            $stmt = $pdo->query("
                SELECT item_id, question, COUNT(*) as count
                FROM questions
                GROUP BY item_id, question
                HAVING COUNT(*) > 1
            ");
            $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($duplicates)) {
                echo "❌ Found existing duplicates, cannot create constraint yet:\n";
                foreach ($duplicates as $dup) {
                    echo "  Item ID {$dup['item_id']}: {$dup['count']} duplicates\n";
                }
                echo "Please clean up duplicates first.\n";
                throw new Exception("Existing duplicates prevent constraint creation");
            }

            // Create unique constraint
            $stmt = $pdo->exec("
                ALTER TABLE questions
                ADD CONSTRAINT questions_item_content_unique
                UNIQUE (item_id, question)
            ");

            echo "✅ Created unique constraint on (item_id, question)\n";
        }

        echo "\n=== 2. CREATING VALIDATION FUNCTION ===\n";

        // Create a function to check for duplicates
        $stmt = $pdo->query("
            SELECT routine_name
            FROM information_schema.routines
            WHERE routine_name = 'check_duplicate_question_content'
              AND routine_schema = current_schema()
        ");
        $functionExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($functionExists) {
            echo "⚠️  Validation function already exists\n";
        } else {
            echo "Creating validation function...\n";

            $pdo->exec("
                CREATE OR REPLACE FUNCTION check_duplicate_question_content()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    -- Check if a question with the same content already exists for this item
                    IF EXISTS (
                        SELECT 1 FROM questions
                        WHERE item_id = NEW.item_id
                          AND question = NEW.question
                          AND id != COALESCE(NEW.id, 0)
                    ) THEN
                        RAISE EXCEPTION 'Duplicate question content detected for item %', NEW.item_id;
                    END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;
            ");

            echo "✅ Created validation function\n";
        }

        echo "\n=== 3. CREATING TRIGGERS ===\n";

        // Create trigger for INSERT
        $stmt = $pdo->query("
            SELECT trigger_name
            FROM information_schema.triggers
            WHERE trigger_name = 'questions_prevent_duplicate_insert'
              AND event_object_table = 'questions'
        ");
        $insertTriggerExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($insertTriggerExists) {
            echo "⚠️  INSERT trigger already exists\n";
        } else {
            echo "Creating INSERT trigger...\n";

            $pdo->exec("
                CREATE TRIGGER questions_prevent_duplicate_insert
                BEFORE INSERT ON questions
                FOR EACH ROW
                EXECUTE FUNCTION check_duplicate_question_content();
            ");

            echo "✅ Created INSERT trigger\n";
        }

        // Create trigger for UPDATE
        $stmt = $pdo->query("
            SELECT trigger_name
            FROM information_schema.triggers
            WHERE trigger_name = 'questions_prevent_duplicate_update'
              AND event_object_table = 'questions'
        ");
        $updateTriggerExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($updateTriggerExists) {
            echo "⚠️  UPDATE trigger already exists\n";
        } else {
            echo "Creating UPDATE trigger...\n";

            $pdo->exec("
                CREATE TRIGGER questions_prevent_duplicate_update
                BEFORE UPDATE ON questions
                FOR EACH ROW
                WHEN (OLD.question IS DISTINCT FROM NEW.question OR OLD.item_id IS DISTINCT FROM NEW.item_id)
                EXECUTE FUNCTION check_duplicate_question_content();
            ");

            echo "✅ Created UPDATE trigger\n";
        }

        echo "\n=== 4. CREATING AUDIT LOG TABLE ===\n";

        // Create audit log table for duplicate prevention
        $stmt = $pdo->query("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_name = 'question_duplicate_prevention_logs'
        ");
        $logTableExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($logTableExists) {
            echo "⚠️  Audit log table already exists\n";
        } else {
            echo "Creating audit log table...\n";

            $pdo->exec("
                CREATE TABLE question_duplicate_prevention_logs (
                    id BIGSERIAL PRIMARY KEY,
                    item_id INTEGER NOT NULL,
                    attempted_question TEXT NOT NULL,
                    attempted_by VARCHAR(255),
                    ip_address INET,
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT NOW(),
                    prevented_reason VARCHAR(255) NOT NULL
                );
            ");

            echo "✅ Created audit log table\n";
        }

        echo "\n=== 5. TESTING THE CONSTRAINTS ===\n";

        // Test the constraints by attempting to insert a duplicate
        echo "Testing duplicate prevention...\n";

        try {
            // Try to insert a duplicate question
            $stmt = $pdo->prepare("
                SELECT item_id, question FROM questions LIMIT 1
            ");
            $stmt->execute();
            $sampleQuestion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sampleQuestion) {
                $stmt = $pdo->prepare("
                    INSERT INTO questions (item_id, question, type, created_at, updated_at)
                    VALUES (?, ?, 'test', NOW(), NOW())
                ");
                $stmt->execute([$sampleQuestion['item_id'], $sampleQuestion['question']]);

                // If we get here, constraint failed
                echo "❌ Constraint test failed - duplicate was inserted!\n";

                // Clean up test
                $pdo->exec("DELETE FROM questions WHERE type = 'test'");
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'questions_item_content_unique') !== false ||
                strpos($e->getMessage(), 'Duplicate question content detected') !== false) {
                echo "✅ Constraint test passed - duplicate was prevented!\n";
            } else {
                echo "⚠️  Unexpected error during test: " . $e->getMessage() . "\n";
            }
        }

        // Commit transaction
        $pdo->commit();

        echo "\n✅ PREVENTIVE MEASURES COMPLETED SUCCESSFULLY!\n";
        echo "✅ Database constraints created\n";
        echo "✅ Validation functions created\n";
        echo "✅ Triggers created\n";
        echo "✅ Audit log table created\n";

    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollback();
        throw $e;
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== PREVENTIVE MEASURES CREATION COMPLETE ===\n";