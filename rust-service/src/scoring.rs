use serde::{Deserialize, Serialize};
use sqlx::{PgPool, Row};
use std::collections::HashMap;
use tracing::{info, error, debug};

#[derive(Debug, Deserialize)]
pub struct CalculateScoreRequest {
    pub attempt_id: i64,
}

#[derive(Debug, Serialize)]
pub struct CalculateScoreResponse {
    pub attempt_id: i64,
    pub score: f64,
    pub progress: i32,
    pub total_questions: i32,
    pub answered_questions: i32,
    pub processing_time_ms: u64,
}

#[derive(Debug)]
struct Attempt {
    id: i64,
    exam_id: i64,
    delivery_id: i64,
    taker_id: i64,
}

#[derive(Debug)]
struct Item {
    id: i64,
    is_vignette: bool,
}

#[derive(Debug)]
struct Question {
    id: i64,
    item_id: i64,
    question_type: String,
}

#[derive(Debug)]
struct AttemptQuestion {
    question_id: i64,
    answer: Option<String>,
    is_correct: Option<bool>,
    score: Option<f64>,
}

pub async fn calculate_score_for_attempt(
    pool: &PgPool,
    attempt_id: i64,
) -> Result<CalculateScoreResponse, String> {
    let start = std::time::Instant::now();
    
    info!("Starting score calculation for attempt {}", attempt_id);
    
    // 1. Fetch attempt details
    let attempt = fetch_attempt(pool, attempt_id).await?;
    
    // 2. Get distinct attempt questions (handling potential duplicates)
    let attempt_questions = fetch_attempt_questions(pool, attempt_id).await?;
    
    if attempt_questions.is_empty() {
        // No questions answered
        update_attempt_score(pool, attempt_id, 0.0, 0).await?;
        
        return Ok(CalculateScoreResponse {
            attempt_id,
            score: 0.0,
            progress: 0,
            total_questions: 0,
            answered_questions: 0,
            processing_time_ms: start.elapsed().as_millis() as u64,
        });
    }
    
    // 3. Fetch all items and questions for this exam
    let items = fetch_exam_items(pool, attempt.exam_id).await?;
    let questions = fetch_exam_questions(pool, attempt.exam_id).await?;
    
    // 4. Calculate total questions count
    let total_questions = questions.len() as i32;
    
    // 5. Create lookup maps for efficient processing
    let mut question_map: HashMap<i64, Question> = HashMap::new();
    for question in questions {
        question_map.insert(question.id, question);
    }
    
    let mut item_map: HashMap<i64, Item> = HashMap::new();
    for item in &items {
        item_map.insert(item.id, item.clone());
    }
    
    let mut attempt_question_map: HashMap<i64, AttemptQuestion> = HashMap::new();
    for aq in attempt_questions {
        attempt_question_map.insert(aq.question_id, aq);
    }
    
    // 6. Calculate scores
    let mut total_score = 0.0;
    let mut total_items = 0;
    let mut processed_items: HashMap<i64, ProcessedItem> = HashMap::new();
    
    // Process each item
    for item in &items {
        let mut item_questions: Vec<i64> = Vec::new();
        let mut has_mcq = false;
        let mut has_interview = false;
        let mut item_score = 0.0;
        let mut item_question_count = 0;
        
        // Find all questions for this item
        for (question_id, question) in &question_map {
            if question.item_id == item.id {
                item_questions.push(*question_id);
                
                // Check if this question was attempted
                if let Some(attempt_question) = attempt_question_map.get(question_id) {
                    item_question_count += 1;
                    
                    match question.question_type.as_str() {
                        "multiple-choice" => {
                            has_mcq = true;
                            // Auto-score MCQ: 100 if correct, 0 if wrong
                            let score = if attempt_question.is_correct.unwrap_or(false) {
                                100.0
                            } else {
                                0.0
                            };
                            item_score += score;
                            
                            // Update the attempt_question score in database
                            update_attempt_question_score(pool, attempt_id, *question_id, score).await?;
                        }
                        "essay" => {
                            // Use manually set score (don't override)
                            item_score += attempt_question.score.unwrap_or(0.0);
                        }
                        "interview" => {
                            has_interview = true;
                            // Use manually set score (don't override)
                            item_score += attempt_question.score.unwrap_or(0.0);
                        }
                        _ => {
                            // Unknown type, treat as 0
                        }
                    }
                } else {
                    // Question not attempted, count it anyway for denominator
                    item_question_count += 1;
                }
            }
        }
        
        // Apply vignette logic
        if !has_mcq && item.is_vignette && item_questions.len() > 1 {
            // For non-MCQ vignettes, treat multiple questions as one item
            item_question_count = 1;
        }
        
        // Skip pure interview items without MCQ
        if !has_mcq && has_interview {
            continue;
        }
        
        if item_question_count > 0 {
            total_score += item_score;
            total_items += item_question_count;
        }
        
        processed_items.insert(item.id, ProcessedItem {
            score: item_score,
            question_count: item_question_count,
        });
    }
    
    // 7. Calculate final score and progress
    let final_score = if total_items > 0 {
        total_score / total_items as f64
    } else {
        0.0
    };
    
    let answered_count = attempt_question_map.len() as i32;
    let progress = ((answered_count as f64 / total_questions as f64) * 100.0).ceil() as i32;
    
    // 8. Update attempt in database
    update_attempt_score(pool, attempt_id, final_score, progress).await?;
    
    // 9. Log performance metrics
    let processing_time = start.elapsed().as_millis() as u64;
    info!(
        "Score calculation completed for attempt {} in {}ms. Score: {:.2}, Progress: {}%",
        attempt_id, processing_time, final_score, progress
    );
    
    Ok(CalculateScoreResponse {
        attempt_id,
        score: final_score,
        progress,
        total_questions,
        answered_questions: answered_count,
        processing_time_ms: processing_time,
    })
}

#[derive(Debug, Clone)]
struct ProcessedItem {
    score: f64,
    question_count: i32,
}

async fn fetch_attempt(pool: &PgPool, attempt_id: i64) -> Result<Attempt, String> {
    sqlx::query_as!(
        Attempt,
        r#"
        SELECT id, exam_id, delivery_id, taker_id
        FROM attempts
        WHERE id = $1
        "#,
        attempt_id
    )
    .fetch_one(pool)
    .await
    .map_err(|e| format!("Failed to fetch attempt: {}", e))
}

async fn fetch_attempt_questions(
    pool: &PgPool,
    attempt_id: i64,
) -> Result<Vec<AttemptQuestion>, String> {
    // Get distinct questions (in case of duplicates)
    let rows = sqlx::query!(
        r#"
        SELECT DISTINCT ON (question_id) 
            question_id,
            answer,
            is_correct,
            score
        FROM attempt_question
        WHERE attempt_id = $1
        ORDER BY question_id, id DESC
        "#,
        attempt_id
    )
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch attempt questions: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| AttemptQuestion {
            question_id: row.question_id,
            answer: row.answer,
            is_correct: row.is_correct,
            score: row.score,
        })
        .collect())
}

async fn fetch_exam_items(pool: &PgPool, exam_id: i64) -> Result<Vec<Item>, String> {
    let rows = sqlx::query!(
        r#"
        SELECT DISTINCT i.id, i.is_vignette
        FROM items i
        INNER JOIN exam_item ei ON ei.item_id = i.id
        WHERE ei.exam_id = $1
        ORDER BY i.id
        "#,
        exam_id
    )
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch exam items: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| Item {
            id: row.id,
            is_vignette: row.is_vignette.unwrap_or(false),
        })
        .collect())
}

async fn fetch_exam_questions(pool: &PgPool, exam_id: i64) -> Result<Vec<Question>, String> {
    let rows = sqlx::query!(
        r#"
        SELECT DISTINCT q.id, q.item_id, q.type as question_type
        FROM questions q
        INNER JOIN items i ON i.id = q.item_id
        INNER JOIN exam_item ei ON ei.item_id = i.id
        WHERE ei.exam_id = $1
        ORDER BY q.id
        "#,
        exam_id
    )
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch exam questions: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| Question {
            id: row.id,
            item_id: row.item_id,
            question_type: row.question_type.unwrap_or_else(|| "unknown".to_string()),
        })
        .collect())
}

async fn update_attempt_question_score(
    pool: &PgPool,
    attempt_id: i64,
    question_id: i64,
    score: f64,
) -> Result<(), String> {
    sqlx::query!(
        r#"
        UPDATE attempt_question
        SET score = $3, is_correct = $4
        WHERE attempt_id = $1 AND question_id = $2
        "#,
        attempt_id,
        question_id,
        score,
        score >= 100.0
    )
    .execute(pool)
    .await
    .map_err(|e| format!("Failed to update attempt question score: {}", e))?;
    
    Ok(())
}

async fn update_attempt_score(
    pool: &PgPool,
    attempt_id: i64,
    score: f64,
    progress: i32,
) -> Result<(), String> {
    sqlx::query!(
        r#"
        UPDATE attempts
        SET score = $2, progress = $3, finished_scoring = true
        WHERE id = $1
        "#,
        attempt_id,
        score,
        progress
    )
    .execute(pool)
    .await
    .map_err(|e| format!("Failed to update attempt score: {}", e))?;
    
    Ok(())
}

// Batch processing for multiple attempts
pub async fn calculate_scores_batch(
    pool: &PgPool,
    attempt_ids: Vec<i64>,
) -> Vec<Result<CalculateScoreResponse, String>> {
    let mut results = Vec::new();
    
    // Process in parallel using tokio tasks
    let mut tasks = Vec::new();
    
    for attempt_id in attempt_ids {
        let pool_clone = pool.clone();
        tasks.push(tokio::spawn(async move {
            calculate_score_for_attempt(&pool_clone, attempt_id).await
        }));
    }
    
    // Collect results
    for task in tasks {
        match task.await {
            Ok(result) => results.push(result),
            Err(e) => results.push(Err(format!("Task failed: {}", e))),
        }
    }
    
    results
}