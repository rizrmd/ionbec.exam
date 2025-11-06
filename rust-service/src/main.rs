use axum::{
    extract::State,
    http::StatusCode,
    response::Json,
    routing::{get, post},
    Router,
};
use serde::{Deserialize, Serialize};
use std::sync::Arc;
use tower_http::cors::CorsLayer;
use tracing::{info, error};
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};
use sqlx::Row;
use harsh::Harsh;
use std::sync::OnceLock;

// Laravel hashid configuration
const HASH_LENGTH: usize = 8;
const HASH_ALPHABET: &str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

// Get Laravel app key from environment
fn get_app_key() -> String {
    std::env::var("APP_KEY")
        .unwrap_or_else(|_| "BXmuQm+4JdpqL3GD+pTWlCBmE2+VjQjn2+yjLjqF43s=".to_string())
}

// Global Harsh instances for different model types
static ITEM_HARSH: OnceLock<Harsh> = OnceLock::new();
static QUESTION_HARSH: OnceLock<Harsh> = OnceLock::new();  
static ANSWER_HARSH: OnceLock<Harsh> = OnceLock::new();

// Helper function to generate hash from ID using the exact Laravel algorithm
fn generate_hash_from_id(id: i32, model_type: &str) -> String {
    if id == 0 {
        return String::new();
    }
    
    let harsh = match model_type {
        "Item" => ITEM_HARSH.get_or_init(|| create_harsh_for_model("App\\Models\\Exams\\Item")),
        "Question" => QUESTION_HARSH.get_or_init(|| create_harsh_for_model("App\\Models\\Exams\\Question")),
        "Answer" => ANSWER_HARSH.get_or_init(|| create_harsh_for_model("App\\Models\\Exams\\Answer")),
        _ => ITEM_HARSH.get_or_init(|| create_harsh_for_model("default")),
    };
    
    harsh.encode(&[id as u64])
}

// Create Harsh instance using Laravel's algorithm
fn create_harsh_for_model(class_name: &str) -> Harsh {
    // Laravel algorithm: substr($key, -4) . substr(config('app.key', 'lara'), -4)
    let key = if class_name.len() > 4 {
        class_name.to_string()
    } else {
        format!("default{}", class_name)
    };
    
    let key_suffix = if key.len() >= 4 {
        &key[key.len()-4..]
    } else {
        &key
    };
    
    let app_key = get_app_key();
    let app_key_suffix = if app_key.len() >= 4 {
        &app_key[app_key.len()-4..]
    } else {
        "lara"
    };
    
    let salt = format!("{}{}", key_suffix, app_key_suffix);
    
    Harsh::builder()
        .salt(salt.as_bytes())
        .length(HASH_LENGTH)
        .alphabet(HASH_ALPHABET)
        .build()
        .expect("Failed to build Harsh encoder")
}

mod scoring;
mod csv_processor;

#[derive(Clone)]
struct AppState {
    redis_client: Option<redis::Client>,
    db_pool: Option<sqlx::PgPool>,
}

#[derive(Serialize)]
struct HealthResponse {
    status: String,
    service: String,
    version: String,
    database: bool,
    redis: bool,
}

#[derive(Deserialize)]
#[allow(dead_code)]
struct ProcessRequest {
    task_type: String,
    data: serde_json::Value,
}

#[derive(Serialize)]
struct ProcessResponse {
    success: bool,
    message: String,
    result: Option<serde_json::Value>,
}

#[tokio::main]
async fn main() {
    // Initialize tracing
    tracing_subscriber::registry()
        .with(
            tracing_subscriber::EnvFilter::try_from_default_env()
                .unwrap_or_else(|_| "ionbec_rust_service=debug,tower_http=debug".into()),
        )
        .with(tracing_subscriber::fmt::layer())
        .init();

    info!("Starting Ionbec Rust Service");

    // Initialize database connection using Laravel .env variables
    let db_pool = if let (Ok(host), Ok(port), Ok(database), Ok(username), Ok(password)) = (
        std::env::var("DB_HOST"),
        std::env::var("DB_PORT"),
        std::env::var("DB_DATABASE"),
        std::env::var("DB_USERNAME"),
        std::env::var("DB_PASSWORD"),
    ) {
        let database_url = format!(
            "postgresql://{}:{}@{}:{}/{}",
            username, password, host, port, database
        );
        
        match sqlx::postgres::PgPoolOptions::new()
            .max_connections(5)
            .connect(&database_url)
            .await
        {
            Ok(pool) => {
                info!("Connected to PostgreSQL database: {}:{}/{}", host, port, database);
                Some(pool)
            }
            Err(e) => {
                error!("Failed to connect to database {}: {}", database_url, e);
                None
            }
        }
    } else {
        info!("Database environment variables not set, running without database");
        None
    };

    // Initialize Redis connection (optional)
    let redis_client = if let Ok(redis_url) = std::env::var("REDIS_URL") {
        match redis::Client::open(redis_url) {
            Ok(client) => {
                info!("Connected to Redis");
                Some(client)
            }
            Err(e) => {
                error!("Failed to connect to Redis: {}", e);
                None
            }
        }
    } else {
        info!("REDIS_URL not set, running without Redis");
        None
    };

    let state = Arc::new(AppState {
        redis_client,
        db_pool,
    });

    // Build router
    let app = Router::new()
        .route("/health", get(health_check))
        .route("/api/process", post(process_task))
        .route("/api/score", post(calculate_score))
        .route("/api/score/calculate", post(calculate_score_real))
        .route("/api/score/batch", post(calculate_scores_batch_handler))
        .route("/api/validate", post(validate_exam))
        .route("/api/csv/process", post(csv_process_handler))
        .route("/api/csv/batch", post(csv_batch_process_handler))
        .route("/api/exam/load", post(load_exam_data_handler))
        .layer(CorsLayer::permissive())
        .with_state(state);

    let addr = std::env::var("RUST_SERVICE_ADDR").unwrap_or_else(|_| "0.0.0.0:3001".to_string());
    info!("Rust service listening on {}", addr);
    
    let listener = tokio::net::TcpListener::bind(addr)
        .await
        .expect("Failed to bind address");
    
    axum::serve(listener, app)
        .await
        .expect("Failed to start server");
}

async fn health_check(State(state): State<Arc<AppState>>) -> Json<HealthResponse> {
    let db_healthy = if let Some(pool) = &state.db_pool {
        sqlx::query("SELECT 1").fetch_one(pool).await.is_ok()
    } else {
        false
    };

    let redis_healthy = if let Some(client) = &state.redis_client {
        client.get_connection().is_ok()
    } else {
        false
    };

    Json(HealthResponse {
        status: "healthy".to_string(),
        service: "ionbec-rust-service".to_string(),
        version: env!("CARGO_PKG_VERSION").to_string(),
        database: db_healthy,
        redis: redis_healthy,
    })
}

async fn process_task(
    State(_state): State<Arc<AppState>>,
    Json(payload): Json<ProcessRequest>,
) -> Result<Json<ProcessResponse>, StatusCode> {
    info!("Processing task: {}", payload.task_type);

    // Example task processing logic
    let result = match payload.task_type.as_str() {
        "exam_validation" => {
            // Validate exam data
            Some(serde_json::json!({
                "valid": true,
                "processed_at": chrono::Utc::now().to_rfc3339()
            }))
        }
        "score_calculation" => {
            // Calculate scores
            Some(serde_json::json!({
                "score": 85,
                "max_score": 100,
                "percentage": 85.0
            }))
        }
        _ => None,
    };

    Ok(Json(ProcessResponse {
        success: result.is_some(),
        message: if result.is_some() {
            format!("Task '{}' processed successfully", payload.task_type)
        } else {
            format!("Unknown task type: {}", payload.task_type)
        },
        result,
    }))
}

#[derive(Deserialize)]
struct ScoreRequest {
    exam_id: i32,
    answers: Vec<Answer>,
}

#[derive(Deserialize)]
#[allow(dead_code)]
struct Answer {
    question_id: i32,
    answer: String,
}

#[derive(Serialize)]
struct ScoreResponse {
    exam_id: i32,
    total_score: f64,
    correct_answers: usize,
    total_questions: usize,
}

async fn calculate_score(
    Json(payload): Json<ScoreRequest>,
) -> Result<Json<ScoreResponse>, StatusCode> {
    info!("Calculating score for exam {}", payload.exam_id);
    
    // Example scoring logic
    let total_questions = payload.answers.len();
    let correct_answers = payload.answers.iter()
        .filter(|a| a.answer.len() > 0)  // Simple validation
        .count();
    
    let total_score = (correct_answers as f64 / total_questions as f64) * 100.0;
    
    Ok(Json(ScoreResponse {
        exam_id: payload.exam_id,
        total_score,
        correct_answers,
        total_questions,
    }))
}

#[derive(Deserialize)]
struct ExamValidationRequest {
    exam_id: i32,
    questions: Vec<Question>,
}

#[derive(Deserialize)]
#[allow(dead_code)]
struct Question {
    id: i32,
    text: String,
    options: Vec<String>,
    correct_answer: Option<String>,
}

#[derive(Serialize)]
struct ExamValidationResponse {
    valid: bool,
    errors: Vec<String>,
    warnings: Vec<String>,
}

async fn validate_exam(
    Json(payload): Json<ExamValidationRequest>,
) -> Result<Json<ExamValidationResponse>, StatusCode> {
    info!("Validating exam {}", payload.exam_id);
    
    let mut errors = Vec::new();
    let mut warnings = Vec::new();
    
    // Validation logic
    if payload.questions.is_empty() {
        errors.push("Exam has no questions".to_string());
    }
    
    for (i, question) in payload.questions.iter().enumerate() {
        if question.text.trim().is_empty() {
            errors.push(format!("Question {} has empty text", i + 1));
        }
        
        if question.options.len() < 2 {
            warnings.push(format!("Question {} has less than 2 options", i + 1));
        }
        
        if question.correct_answer.is_none() {
            warnings.push(format!("Question {} has no correct answer specified", i + 1));
        }
    }
    
    Ok(Json(ExamValidationResponse {
        valid: errors.is_empty(),
        errors,
        warnings,
    }))
}

// New handler for real score calculation
async fn calculate_score_real(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<scoring::CalculateScoreRequest>,
) -> Result<Json<scoring::CalculateScoreResponse>, StatusCode> {
    let pool = state.db_pool.as_ref()
        .ok_or_else(|| {
            error!("Database connection not available");
            StatusCode::SERVICE_UNAVAILABLE
        })?;
    
    match scoring::calculate_score_for_attempt(pool, payload.attempt_id).await {
        Ok(response) => Ok(Json(response)),
        Err(e) => {
            error!("Score calculation failed: {}", e);
            Err(StatusCode::INTERNAL_SERVER_ERROR)
        }
    }
}

#[derive(Deserialize)]
struct BatchScoreRequest {
    attempt_ids: Vec<i64>,
}

#[derive(Serialize)]
struct BatchScoreResponse {
    results: Vec<BatchScoreResult>,
    total_processing_time_ms: u64,
}

#[derive(Serialize)]
struct BatchScoreResult {
    attempt_id: i64,
    success: bool,
    score: Option<f64>,
    error: Option<String>,
}

// Handler for batch score calculation
async fn calculate_scores_batch_handler(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<BatchScoreRequest>,
) -> Result<Json<BatchScoreResponse>, StatusCode> {
    let start = std::time::Instant::now();
    
    let pool = state.db_pool.as_ref()
        .ok_or_else(|| {
            error!("Database connection not available");
            StatusCode::SERVICE_UNAVAILABLE
        })?;
    
    let results = scoring::calculate_scores_batch(pool, payload.attempt_ids).await;
    
    let batch_results: Vec<BatchScoreResult> = results
        .into_iter()
        .map(|result| match result {
            Ok(response) => BatchScoreResult {
                attempt_id: response.attempt_id,
                success: true,
                score: Some(response.score),
                error: None,
            },
            Err(e) => BatchScoreResult {
                attempt_id: 0, // We don't have the ID on error
                success: false,
                score: None,
                error: Some(e),
            },
        })
        .collect();
    
    Ok(Json(BatchScoreResponse {
        results: batch_results,
        total_processing_time_ms: start.elapsed().as_millis() as u64,
    }))
}

// CSV processing handlers
async fn csv_process_handler(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<csv_processor::CsvProcessRequest>,
) -> Result<Json<csv_processor::CsvProcessResponse>, StatusCode> {
    let pool = state.db_pool.as_ref()
        .ok_or_else(|| {
            error!("Database connection not available");
            StatusCode::SERVICE_UNAVAILABLE
        })?;
    
    Ok(Json(csv_processor::process_csv_handler(State(pool.clone()), Json(payload)).await.0))
}

async fn csv_batch_process_handler(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<csv_processor::CsvBatchProcessRequest>,
) -> Result<Json<csv_processor::CsvBatchProcessResponse>, StatusCode> {
    let pool = state.db_pool.as_ref()
        .ok_or_else(|| {
            error!("Database connection not available");
            StatusCode::SERVICE_UNAVAILABLE
        })?;
    
    Ok(Json(csv_processor::process_csv_batch_handler(State(pool.clone()), Json(payload)).await.0))
}

// Exam data loading structs and handler
#[derive(Deserialize)]
struct ExamDataRequest {
    exam_id: i32,
    delivery_id: i32,
    taker_id: Option<i32>,
}

#[derive(Serialize)]
struct ExamDataResponse {
    success: bool,
    items: Vec<ExamItem>,
    remaining_seconds: Option<i32>,
    message: Option<String>,
}

#[derive(Serialize)]
struct ExamItem {
    hash: String,
    name: String,
    content: Option<String>,
    is_vignette: bool,
    is_random: bool,
    item_type: ExamItemType,
    questions: Vec<ExamQuestion>,
    questions_count: i32,
    attachments: Vec<ExamAttachment>,
}

#[derive(Serialize)]
struct ExamItemType {
    id: i32,
    name: String,
    value: String,
}

#[derive(Serialize)]
struct ExamQuestion {
    id: i32,
    hash: String,
    question: String,
    score: i32,
    is_random: bool,
    #[serde(rename = "type")]
    question_type: Option<QuestionType>,
    answers: Vec<ExamAnswer>,
}

#[derive(Serialize)]
struct QuestionType {
    id: i32,
    name: String,
    value: String,
}

#[derive(Serialize)]
struct ExamAnswer {
    hash: String,
    answer: Option<String>,
    // is_correct_answer is intentionally omitted for security
}

#[derive(Serialize)]
struct ExamAttachment {
    id: String,
    filename: String,
    url: String,
    description: Option<String>,
}

async fn load_exam_data_handler(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<ExamDataRequest>,
) -> Result<Json<ExamDataResponse>, StatusCode> {
    let start = std::time::Instant::now();
    info!("Loading exam data for exam_id: {}, delivery_id: {}", payload.exam_id, payload.delivery_id);
    
    let pool = state.db_pool.as_ref()
        .ok_or_else(|| {
            error!("Database connection not available");
            StatusCode::SERVICE_UNAVAILABLE
        })?;
    
    match load_exam_data(pool, payload.exam_id, payload.delivery_id, payload.taker_id).await {
        Ok(items) => {
            let load_time = start.elapsed().as_millis();
            info!("Loaded {} items in {}ms", items.len(), load_time);
            
            Ok(Json(ExamDataResponse {
                success: true,
                items,
                remaining_seconds: None, // TODO: Calculate if needed
                message: Some(format!("Loaded in {}ms", load_time)),
            }))
        }
        Err(e) => {
            error!("Failed to load exam data: {}", e);
            Err(StatusCode::INTERNAL_SERVER_ERROR)
        }
    }
}

async fn load_exam_data(
    pool: &sqlx::PgPool,
    exam_id: i32,
    _delivery_id: i32,
    _taker_id: Option<i32>,
) -> Result<Vec<ExamItem>, sqlx::Error> {
    // Load all data in 4 optimized queries instead of N+1 queries
    
    // 1. Load all items for this exam
    let items_query = r#"
        SELECT 
            i.id, i.hash, i.title, i.content, i.type, i.is_vignette, i.is_random,
            ei.order as pivot_order
        FROM items i
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = $1
        ORDER BY ei.order ASC
    "#;
    
    let item_rows = sqlx::query(items_query)
        .bind(exam_id)
        .fetch_all(pool)
        .await?;
    
    if item_rows.is_empty() {
        return Ok(Vec::new());
    }
    
    let item_ids: Vec<i32> = item_rows.iter().map(|row| row.get::<i32, _>("id")).collect();

    info!("Found {} item IDs: {:?}", item_ids.len(), item_ids);

    // 2. Load all questions for these items in one query
    let questions_query = r#"
        SELECT 
            q.id, q.hash, q.question, q.score, q.type, q.is_random, q.item_id
        FROM questions q
        WHERE q.item_id = ANY($1)
        ORDER BY q.item_id, q.id ASC
    "#;
    
    let question_rows = sqlx::query(questions_query)
        .bind(&item_ids)
        .fetch_all(pool)
        .await?;
    
    let question_ids: Vec<i32> = question_rows.iter().map(|row| row.get::<i32, _>("id")).collect();
    
    // 3 & 4. Load answers and attachments in parallel (they don't depend on each other)
    let answers_query = r#"
        SELECT id, hash, answer, question_id
        FROM answers
        WHERE question_id = ANY($1)
        ORDER BY question_id, id ASC
    "#;
    
    let attachments_query = r#"
        SELECT a.id, a.title, a.path, a.description, att.attachable_id as item_id
        FROM attachments a
        JOIN attachables att ON a.id = att.attachment_id
        WHERE att.attachable_id = ANY($1) AND att.attachable_type = 'App\\Models\\Exams\\Item'
        ORDER BY att.attachable_id, a.id ASC
    "#;

    info!("Attachments SQL: {}", attachments_query);
    
    info!("Executing queries for {} questions and {} items", question_ids.len(), item_ids.len());
    info!("Item IDs for attachments query: {:?}", &item_ids);

    // Execute answers and attachments queries in parallel
    let (answer_rows, attachment_rows) = tokio::try_join!(
        sqlx::query(answers_query).bind(&question_ids).fetch_all(pool),
        sqlx::query(attachments_query).bind(&item_ids).fetch_all(pool)
    )?;

    info!("Successfully loaded {} answers and {} attachments", answer_rows.len(), attachment_rows.len());
    
    // Group data by their parent IDs
    let mut answers_by_question: std::collections::HashMap<i32, Vec<ExamAnswer>> = std::collections::HashMap::new();
    for answer_row in answer_rows {
        let question_id: i32 = answer_row.get("question_id");
        let answer_id: i32 = answer_row.get("id");
        let answer = ExamAnswer {
            hash: generate_hash_from_id(answer_id, "Answer"),
            answer: answer_row.get::<Option<String>, _>("answer"),
        };
        answers_by_question.entry(question_id).or_default().push(answer);
    }
    
    let mut questions_by_item: std::collections::HashMap<i32, Vec<ExamQuestion>> = std::collections::HashMap::new();
    for question_row in question_rows {
        let item_id: i32 = question_row.get("item_id");
        let question_id: i32 = question_row.get("id");
        
        let question_type = if let Ok(type_str) = question_row.try_get::<String, _>("type") {
            // First try to parse as JSON (for legacy format)
            if let Ok(parsed) = serde_json::from_str::<serde_json::Value>(&type_str) {
                Some(QuestionType {
                    id: parsed["id"].as_i64().unwrap_or(0) as i32,
                    name: parsed["name"].as_str().unwrap_or("multiple-choice").to_string(),
                    value: parsed["value"].as_str().unwrap_or("multiple-choice").to_string(),
                })
            } else {
                // Handle simple string format (new format)
                Some(QuestionType {
                    id: 0,
                    name: type_str.clone(),
                    value: type_str,
                })
            }
        } else {
            Some(QuestionType {
                id: 0,
                name: "multiple-choice".to_string(),
                value: "multiple-choice".to_string(),
            })
        };
        
        let question = ExamQuestion {
            id: question_id,
            hash: generate_hash_from_id(question_id, "Question"),
            question: question_row.get("question"),
            score: question_row.get("score"),
            is_random: question_row.get("is_random"),
            question_type,
            answers: answers_by_question.remove(&question_id).unwrap_or_default(),
        };
        questions_by_item.entry(item_id).or_default().push(question);
    }
    
    let mut attachments_by_item: std::collections::HashMap<i32, Vec<ExamAttachment>> = std::collections::HashMap::new();
    for attachment_row in attachment_rows {
        let item_id: i32 = attachment_row.get("item_id");
        let attachment_id: String = attachment_row.get("id");
        let attachment = ExamAttachment {
            id: attachment_id.clone(),
            filename: attachment_row.get::<Option<String>, _>("title").unwrap_or_default(),
            url: format!("/attachment/stream/{}", attachment_id),
            description: attachment_row.get("description"),
        };
        attachments_by_item.entry(item_id).or_default().push(attachment);
    }
    
    // Build final result
    let mut items = Vec::new();
    for item_row in item_rows {
        let item_id: i32 = item_row.get("id");
        let questions = questions_by_item.remove(&item_id).unwrap_or_default();
        let attachments = attachments_by_item.remove(&item_id).unwrap_or_default();
        let questions_count = questions.len() as i32;
        
        // Parse the type JSON from database
        let item_type_str: String = item_row.get("type");
        let item_type = if let Ok(parsed) = serde_json::from_str::<serde_json::Value>(&item_type_str) {
            ExamItemType {
                id: parsed["id"].as_i64().unwrap_or(0) as i32,
                name: parsed["name"].as_str().unwrap_or("unknown").to_string(),
                value: parsed["value"].as_str().unwrap_or("unknown").to_string(),
            }
        } else {
            // Handle simple string format (new format)
            ExamItemType {
                id: 0,
                name: item_type_str.clone(),
                value: item_type_str,
            }
        };

        items.push(ExamItem {
            hash: generate_hash_from_id(item_id, "Item"),
            name: item_row.get("title"),
            content: item_row.get("content"),
            is_vignette: item_row.get("is_vignette"),
            is_random: item_row.get("is_random"),  
            item_type,
            questions,
            questions_count,
            attachments,
        });
    }
    
    Ok(items)
}