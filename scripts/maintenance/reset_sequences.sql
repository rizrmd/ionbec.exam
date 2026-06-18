-- Reset all PostgreSQL id sequences to MAX(id) of their owning table.
--
-- WHY: After importing data with explicit ids (e.g. restoring/migrating takers,
-- groups, questions, ...), the sequences are NOT advanced. The next INSERT then
-- reuses an existing id and fails with:
--   SQLSTATE[23505]: Unique violation ... duplicate key value violates unique constraint "<table>_pkey"
-- Symptoms seen in this app: "input peserta" (takers) and "save soal" (items/questions/answers) failing.
--
-- This script is IDEMPOTENT and SAFE to run anytime, especially AFTER any data import.
-- It only ever moves sequences forward to MAX(id); empty tables are skipped.
--
-- Usage:
--   PGPASSWORD=... psql -h <host> -p <port> -U <user> -d <db> -f scripts/maintenance/reset_sequences.sql

DO $$
DECLARE
    r          RECORD;
    seq_name   TEXT;
    max_id     BIGINT;
BEGIN
    FOR r IN
        SELECT c.oid AS tbl_oid, c.relname AS tbl, a.attname AS col
        FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        JOIN pg_attribute a ON a.attrelid = c.oid
        WHERE n.nspname = 'public'
          AND c.relkind = 'r'
          AND a.attname = 'id'
          AND a.attnum > 0
          AND NOT a.attisdropped
    LOOP
        -- Find the sequence via dependency, fall back to the <table>_id_seq naming convention.
        seq_name := pg_get_serial_sequence(format('public.%I', r.tbl), r.col);

        IF seq_name IS NULL THEN
            SELECT format('public.%I', cl.relname) INTO seq_name
            FROM pg_class cl
            JOIN pg_namespace nn ON nn.oid = cl.relnamespace
            WHERE cl.relkind = 'S'
              AND nn.nspname = 'public'
              AND cl.relname = r.tbl || '_id_seq';
        END IF;

        IF seq_name IS NULL THEN
            RAISE NOTICE 'SKIP %, no sequence found for column id', r.tbl;
            CONTINUE;
        END IF;

        EXECUTE format('SELECT MAX(%I) FROM public.%I', r.col, r.tbl) INTO max_id;

        IF max_id IS NULL THEN
            -- Empty table: leave the sequence as-is (next id = 1).
            RAISE NOTICE 'SKIP % (empty)', r.tbl;
            CONTINUE;
        END IF;

        PERFORM setval(seq_name, max_id);
        RAISE NOTICE 'RESET %.% -> % (seq %)', r.tbl, r.col, max_id, seq_name;
    END LOOP;
END $$;
