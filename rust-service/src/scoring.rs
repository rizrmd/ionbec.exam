use bigdecimal::BigDecimal;
use serde::{Deserialize, Serialize};
use sqlx::{PgPool, Row, FromRow};
use std::collections::HashMap;
use std::str::FromStr;
use tracing::info;

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

#[derive(Debug, FromRow)]
#[allow(dead_code)]
struct Attempt {
    id: i32,
    exam_id: i32,
    delivery_id: i32,
    attempted_by: i32,
}

#[derive(Debug, Clone)]
struct Item {
    id: i32,
    is_vignette: bool,
}

#[derive(Debug)]
struct Question {
    id: i32,
    item_id: i32,
    question_type: String,
}

#[derive(Debug)]
#[allow(dead_code)]
struct AttemptQuestion {
    question_id: i32,
    answer: Option<String>,
    is_correct: bool,
    score: Option<f32>,
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
    let items = fetch_exam_items(pool, attempt.exam_id.into()).await?;
    let questions = fetch_exam_questions(pool, attempt.exam_id.into()).await?;
    
    // 4. Calculate total questions count
    let total_questions = questions.len() as i32;
    
    // 5. Create lookup maps for efficient processing
    let mut question_map: HashMap<i64, Question> = HashMap::new();
    for question in questions {
        question_map.insert(question.id.into(), question);
    }
    
    let mut item_map: HashMap<i64, Item> = HashMap::new();
    for item in &items {
        item_map.insert(item.id.into(), item.clone());
    }
    
    let mut attempt_question_map: HashMap<i64, AttemptQuestion> = HashMap::new();
    for aq in attempt_questions {
        attempt_question_map.insert(aq.question_id.into(), aq);
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
                            let score = if attempt_question.is_correct {
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
                            item_score += attempt_question.score.unwrap_or(0.0) as f64;
                        }
                        "interview" => {
                            has_interview = true;
                            // Use manually set score (don't override)
                            item_score += attempt_question.score.unwrap_or(0.0) as f64;
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
        
        processed_items.insert(item.id.into(), ProcessedItem {
            score: item_score as f64,
            question_count: item_question_count,
        });
    }
    
    // 7. Calculate final score and progress
    let final_score = if total_items > 0 {
        (total_score as f64) / (total_items as f64)
    } else {
        0.0
    };
    
    let answered_count = attempt_question_map.len() as i32;
    let progress = ((answered_count as f64 / total_questions as f64) * 100.0).ceil() as i32;
    
    // 8. Update attempt in database
    update_attempt_score(pool, attempt_id, final_score as f64, progress).await?;
    
    // 9. Log performance metrics
    let processing_time = start.elapsed().as_millis() as u64;
    info!(
        "Score calculation completed for attempt {} in {}ms. Score: {:.2}, Progress: {}%",
        attempt_id, processing_time, final_score, progress
    );
    
    Ok(CalculateScoreResponse {
        attempt_id,
        score: final_score as f64,
        progress,
        total_questions,
        answered_questions: answered_count,
        processing_time_ms: processing_time,
    })
}

#[derive(Debug, Clone)]
#[allow(dead_code)]
struct ProcessedItem {
    score: f64,
    question_count: i32,
}

async fn fetch_attempt(pool: &PgPool, attempt_id: i64) -> Result<Attempt, String> {
    sqlx::query_as::<_, Attempt>(
        "SELECT id, exam_id, delivery_id, attempted_by FROM attempts WHERE id = $1"
    )
    .bind(attempt_id as i32)
    .fetch_one(pool)
    .await
    .map_err(|e| format!("Failed to fetch attempt: {}", e))
}

async fn fetch_attempt_questions(
    pool: &PgPool,
    attempt_id: i64,
) -> Result<Vec<AttemptQuestion>, String> {
    // Get distinct questions (in case of duplicates)
    let rows = sqlx::query(
        "SELECT DISTINCT ON (question_id) question_id, answer, is_correct, score FROM attempt_question WHERE attempt_id = $1 ORDER BY question_id, id DESC"
    )
    .bind(attempt_id as i32)
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch attempt questions: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| AttemptQuestion {
            question_id: row.get::<i32, _>("question_id"),
            answer: row.get::<Option<String>, _>("answer"),
            is_correct: row.get::<bool, _>("is_correct"),
            score: row.get::<Option<BigDecimal>, _>("score").map(|bd| bd.to_string().parse::<f32>().unwrap_or(0.0)),
        })
        .collect())
}

async fn fetch_exam_items(pool: &PgPool, exam_id: i64) -> Result<Vec<Item>, String> {
    let rows = sqlx::query(
        "SELECT DISTINCT i.id, i.is_vignette FROM items i INNER JOIN exam_item ei ON ei.item_id = i.id WHERE ei.exam_id = $1 ORDER BY i.id"
    )
    .bind(exam_id as i32)
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch exam items: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| Item {
            id: row.get::<i32, _>("id"),
            is_vignette: row.get::<bool, _>("is_vignette"),
        })
        .collect())
}

async fn fetch_exam_questions(pool: &PgPool, exam_id: i64) -> Result<Vec<Question>, String> {
    let rows = sqlx::query(
        "SELECT DISTINCT q.id, q.item_id, q.type as question_type FROM questions q INNER JOIN items i ON i.id = q.item_id INNER JOIN exam_item ei ON ei.item_id = i.id WHERE ei.exam_id = $1 ORDER BY q.id"
    )
    .bind(exam_id as i32)
    .fetch_all(pool)
    .await
    .map_err(|e| format!("Failed to fetch exam questions: {}", e))?;
    
    Ok(rows
        .into_iter()
        .map(|row| Question {
            id: row.get::<i32, _>("id"),
            item_id: row.get::<i32, _>("item_id"),
            question_type: row.get::<String, _>("question_type"),
        })
        .collect())
}

async fn update_attempt_question_score(
    pool: &PgPool,
    attempt_id: i64,
    question_id: i64,
    score: f64,
) -> Result<(), String> {
    sqlx::query(
        "UPDATE attempt_question SET score = $3, is_correct = $4 WHERE attempt_id = $1 AND question_id = $2"
    )
    .bind(attempt_id as i32)
    .bind(question_id as i32)
    .bind(BigDecimal::from_str(&score.to_string()).unwrap())
    .bind(score >= 100.0)
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
    sqlx::query(
        "UPDATE attempts SET score = $2, progress = $3 WHERE id = $1"
    )
    .bind(attempt_id as i32)
    .bind(BigDecimal::from_str(&score.to_string()).unwrap())
    .bind(progress)
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