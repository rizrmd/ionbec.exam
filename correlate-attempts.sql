-- SQL approach to correlate attempts efficiently
-- This builds the correlation mappings we need

-- Step 1: Create correlation for takers
-- Match legacy takers to imported takers by email or name
WITH taker_correlation AS (
    SELECT 
        lt.id as legacy_taker_id,
        it.id as new_taker_id
    FROM legacy.takers lt
    JOIN ionbec.takers it ON (
        (lt.email IS NOT NULL AND it.email = lt.email) OR
        (lt.email IS NULL AND it.name = lt.name)
    )
    WHERE it.client_id = 16
),

-- Step 2: Create correlation for deliveries  
-- Match by exam code + group name combination
delivery_correlation AS (
    SELECT 
        ld.id as legacy_delivery_id,
        id.id as new_delivery_id
    FROM legacy.deliveries ld
    JOIN legacy.exams le ON ld.exam_id = le.id
    JOIN legacy.groups lg ON ld.group_id = lg.id
    JOIN ionbec.deliveries id ON id.client_id = 16
    JOIN ionbec.exams ie ON id.exam_id = ie.id AND ie.code = le.code
    JOIN ionbec.groups ig ON id.group_id = ig.id AND ig.name = lg.name
)

-- Step 3: Join attempts with both correlations
SELECT 
    la.id as legacy_attempt_id,
    tc.new_taker_id as attempted_by,
    id.exam_id,
    dc.new_delivery_id as delivery_id,
    la.started_at,
    la.ended_at,
    la.finished_at,
    la.score,
    la.created_at,
    la.updated_at
FROM legacy.attempts la
JOIN taker_correlation tc ON la.taker_id = tc.legacy_taker_id
JOIN delivery_correlation dc ON la.delivery_id = dc.legacy_delivery_id
JOIN ionbec.deliveries id ON id.id = dc.new_delivery_id;