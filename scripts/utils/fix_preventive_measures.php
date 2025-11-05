<?php

/**
 * FIX PREVENTIVE MEASURES
 * Fix issues with constraint and table creation
 */

echo "=== FIXING PREVENTIVE MEASURES ===\n\n";

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
        echo "=== 1. DROPPING EXISTING OBJECTS ===\n";

        // Drop existing triggers first
        $triggers = ['questions_prevent_duplicate_insert', 'questions_prevent_duplicate_update'];
        foreach ($triggers as $triggerName) {
            $stmt = $pdo->query("
                SELECT trigger_name
                FROM information_schema.triggers
                WHERE trigger_name = '$triggerName'
                  AND event_object_table = 'questions'
            ");
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                $pdo->exec("DROP TRIGGER IF EXISTS $triggerName ON questions");
                echo "  Dropped trigger: $triggerName\n";
            }
        }

        // Drop existing function
        $stmt = $pdo->query("
            SELECT routine_name
            FROM information_schema.routines
            WHERE routine_name = 'check_duplicate_question_content'
              AND routine_schema = current_schema()
        ");
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $pdo->exec("DROP FUNCTION IF EXISTS check_duplicate_question_content()");
            echo "  Dropped function: check_duplicate_question_content\n";
        }

        echo "\n=== 2. CREATING CLEAN CONSTRAINT ===\n";

        // Drop constraint if it exists
        $stmt = $pdo->query("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'questions'
              AND constraint_name = 'questions_item_content_unique'
        ");
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $pdo->exec("ALTER TABLE questions DROP CONSTRAINT questions_item_content_unique");
            echo "  Dropped existing constraint\n";
        }

        // Check for any remaining duplicates first
        $stmt = $pdo->query("
            SELECT item_id, question, COUNT(*) as count
            FROM questions
            GROUP BY item_id, question
            HAVING COUNT(*) > 1
            LIMIT 1
        ");
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($duplicates)) {
            echo "  ❌ Still found duplicates - cannot create constraint\n";
            echo "  Item ID: {$duplicates[0]['item_id']}, Count: {$duplicates[0]['count']}\n";
            throw new Exception("Duplicates still exist");
        }

        // Create unique constraint
        echo "  Creating unique constraint...\n";
        $pdo->exec("
            ALTER TABLE questions
            ADD CONSTRAINT questions_item_content_unique
            UNIQUE (item_id, question)
        ");
        echo "  ✅ Created unique constraint\n";

        echo "\n=== 3. CREATING VALIDATION FUNCTION ===\n";

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
        echo "  ✅ Created validation function\n";

        echo "\n=== 4. CREATING TRIGGERS ===\n";

        // INSERT trigger
        $pdo->exec("
            CREATE TRIGGER questions_prevent_duplicate_insert
            BEFORE INSERT ON questions
            FOR EACH ROW
            EXECUTE FUNCTION check_duplicate_question_content();
        ");
        echo "  ✅ Created INSERT trigger\n";

        // UPDATE trigger
        $pdo->exec("
            CREATE TRIGGER questions_prevent_duplicate_update
            BEFORE UPDATE ON questions
            FOR EACH ROW
            WHEN (OLD.question IS DISTINCT FROM NEW.question OR OLD.item_id IS DISTINCT FROM NEW.item_id)
            EXECUTE FUNCTION check_duplicate_question_content();
        ");
        echo "  ✅ Created UPDATE trigger\n";

        echo "\n=== 5. CREATING AUDIT LOG TABLE ===\n";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS question_duplicate_prevention_logs (
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
        echo "  ✅ Created audit log table\n";

        echo "\n=== 6. TESTING CONSTRAINT ===\n";

        // Get a sample question to test
        $stmt = $pdo->query("
            SELECT item_id, question FROM questions LIMIT 1
        ");
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sample) {
            echo "  Testing duplicate prevention...\n";

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO questions (item_id, question, type, created_at, updated_at)
                    VALUES (?, ?, 'test', NOW(), NOW())
                ");
                $stmt->execute([$sample['item_id'], $sample['question']]);

                echo "  ❌ Constraint failed - duplicate inserted!\n";
                $pdo->exec("DELETE FROM questions WHERE type = 'test'");

            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'questions_item_content_unique') !== false ||
                    strpos($e->getMessage(), 'Duplicate question content detected') !== false) {
                    echo "  ✅ Constraint working - duplicate prevented!\n";
                } else {
                    echo "  ⚠️  Unexpected error: " . $e->getMessage() . "\n";
                }
            }
        }

        // Commit transaction
        $pdo->commit();

        echo "\n✅ ALL PREVENTIVE MEASURES FIXED AND WORKING!\n";

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

echo "\n=== PREVENTIVE MEASURES FIXING COMPLETE ===\n";