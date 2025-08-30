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

    // Initialize database connection (optional)
    let db_pool = if let Ok(database_url) = std::env::var("DATABASE_URL") {
        match sqlx::postgres::PgPoolOptions::new()
            .max_connections(5)
            .connect(&database_url)
            .await
        {
            Ok(pool) => {
                info!("Connected to PostgreSQL database");
                Some(pool)
            }
            Err(e) => {
                error!("Failed to connect to database: {}", e);
                None
            }
        }
    } else {
        info!("DATABASE_URL not set, running without database");
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
        .layer(CorsLayer::permissive())
        .with_state(state);

    let addr = "0.0.0.0:3000";
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
    State(state): State<Arc<AppState>>,
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