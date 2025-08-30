use axum::Json;
use serde::{Deserialize, Serialize};
use sqlx::PgPool;
use std::collections::HashMap;
use std::fs::File;
use std::io::BufReader;
use std::path::Path;
use csv::ReaderBuilder;
use std::time::Instant;

#[derive(Debug, Serialize, Deserialize)]
pub struct CsvProcessRequest {
    pub file_path: String,
    pub table_name: String,
    pub batch_size: Option<usize>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct CsvBatchProcessRequest {
    pub files: Vec<CsvFileInfo>,
    pub batch_size: Option<usize>,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct CsvFileInfo {
    pub file_path: String,
    pub table_name: String,
}

#[derive(Debug, Serialize)]
pub struct CsvProcessResponse {
    pub success: bool,
    pub table_name: String,
    pub records_processed: usize,
    pub processing_time_ms: u64,
    pub error: Option<String>,
}

#[derive(Debug, Serialize)]
pub struct CsvBatchProcessResponse {
    pub success: bool,
    pub total_files: usize,
    pub successful_files: usize,
    pub failed_files: usize,
    pub total_records: usize,
    pub total_processing_time_ms: u64,
    pub results: Vec<CsvProcessResponse>,
}

/// Process a single CSV file and insert into database
pub async fn process_csv_file(
    pool: &PgPool,
    file_path: &str,
    table_name: &str,
    batch_size: usize,
) -> Result<CsvProcessResponse, String> {
    let start_time = Instant::now();
    
    // Check if file exists
    if !Path::new(file_path).exists() {
        return Ok(CsvProcessResponse {
            success: false,
            table_name: table_name.to_string(),
            records_processed: 0,
            processing_time_ms: start_time.elapsed().as_millis() as u64,
            error: Some(format!("File not found: {}", file_path)),
        });
    }

    // Read and parse CSV file
    let file = File::open(file_path)
        .map_err(|e| format!("Failed to open file {}: {}", file_path, e))?;
    
    let mut reader = ReaderBuilder::new()
        .has_headers(true)
        .from_reader(BufReader::new(file));
    
    // Get headers
    let headers = reader.headers()
        .map_err(|e| format!("Failed to read CSV headers: {}", e))?
        .clone();
    
    let column_names: Vec<String> = headers.iter().map(|h| h.to_string()).collect();
    
    // Validate table exists and get column info
    let table_columns = get_table_columns(pool, table_name).await?;
    
    // Filter columns that exist in the target table
    let valid_columns: Vec<String> = column_names
        .into_iter()
        .filter(|col| table_columns.contains(col))
        .collect();
    
    if valid_columns.is_empty() {
        return Ok(CsvProcessResponse {
            success: false,
            table_name: table_name.to_string(),
            records_processed: 0,
            processing_time_ms: start_time.elapsed().as_millis() as u64,
            error: Some("No valid columns found in CSV file".to_string()),
        });
    }

    let mut records = Vec::new();
    let mut total_processed = 0;

    // Process records in batches
    for result in reader.records() {
        let record = result.map_err(|e| format!("Failed to read CSV record: {}", e))?;
        
        let mut row_data = HashMap::new();
        
        // Map CSV values to columns, filtering empty values
        for (i, header) in headers.iter().enumerate() {
            if valid_columns.contains(&header.to_string()) {
                if let Some(value) = record.get(i) {
                    if !value.is_empty() {
                        row_data.insert(header.to_string(), value.to_string());
                    }
                }
            }
        }
        
        if !row_data.is_empty() {
            records.push(row_data);
        }
        
        // Process batch when it reaches the batch size
        if records.len() >= batch_size {
            let processed = bulk_insert(pool, table_name, &records, &valid_columns).await?;
            total_processed += processed;
            records.clear();
        }
    }
    
    // Process remaining records
    if !records.is_empty() {
        let processed = bulk_insert(pool, table_name, &records, &valid_columns).await?;
        total_processed += processed;
    }

    Ok(CsvProcessResponse {
        success: true,
        table_name: table_name.to_string(),
        records_processed: total_processed,
        processing_time_ms: start_time.elapsed().as_millis() as u64,
        error: None,
    })
}

/// Process multiple CSV files in parallel
pub async fn process_csv_batch(
    pool: &PgPool,
    files: Vec<CsvFileInfo>,
    batch_size: usize,
) -> Result<CsvBatchProcessResponse, String> {
    let start_time = Instant::now();
    let total_files = files.len();
    
    // Process files in parallel with controlled concurrency
    let semaphore = std::sync::Arc::new(tokio::sync::Semaphore::new(4)); // Max 4 concurrent
    let mut handles = Vec::new();
    
    for file_info in files {
        let pool_clone = pool.clone();
        let semaphore_clone = semaphore.clone();
        
        let handle = tokio::spawn(async move {
            let _permit = semaphore_clone.acquire().await.unwrap();
            process_csv_file(&pool_clone, &file_info.file_path, &file_info.table_name, batch_size).await
        });
        
        handles.push(handle);
    }
    
    // Collect results
    let mut results = Vec::new();
    let mut successful_files = 0;
    let mut failed_files = 0;
    let mut total_records = 0;
    
    for handle in handles {
        match handle.await {
            Ok(Ok(result)) => {
                if result.success {
                    successful_files += 1;
                    total_records += result.records_processed;
                } else {
                    failed_files += 1;
                }
                results.push(result);
            }
            Ok(Err(e)) => {
                failed_files += 1;
                results.push(CsvProcessResponse {
                    success: false,
                    table_name: "unknown".to_string(),
                    records_processed: 0,
                    processing_time_ms: 0,
                    error: Some(e),
                });
            }
            Err(e) => {
                failed_files += 1;
                results.push(CsvProcessResponse {
                    success: false,
                    table_name: "unknown".to_string(),
                    records_processed: 0,
                    processing_time_ms: 0,
                    error: Some(format!("Task join error: {}", e)),
                });
            }
        }
    }

    Ok(CsvBatchProcessResponse {
        success: failed_files == 0,
        total_files,
        successful_files,
        failed_files,
        total_records,
        total_processing_time_ms: start_time.elapsed().as_millis() as u64,
        results,
    })
}

/// Get table columns from database
async fn get_table_columns(pool: &PgPool, table_name: &str) -> Result<Vec<String>, String> {
    let query = r#"
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = $1 AND table_schema = 'public'
        ORDER BY ordinal_position
    "#;
    
    let rows = sqlx::query_scalar::<_, String>(query)
        .bind(table_name)
        .fetch_all(pool)
        .await
        .map_err(|e| format!("Failed to get table columns for {}: {}", table_name, e))?;
    
    Ok(rows)
}

/// Perform bulk insert using PostgreSQL's COPY or batch INSERT
async fn bulk_insert(
    pool: &PgPool,
    table_name: &str,
    records: &[HashMap<String, String>],
    valid_columns: &[String],
) -> Result<usize, String> {
    if records.is_empty() || valid_columns.is_empty() {
        return Ok(0);
    }

    // Build the INSERT query
    let placeholders: Vec<String> = (0..records.len())
        .map(|i| {
            let row_placeholders: Vec<String> = (0..valid_columns.len())
                .map(|j| format!("${}", i * valid_columns.len() + j + 1))
                .collect();
            format!("({})", row_placeholders.join(", "))
        })
        .collect();

    let query = format!(
        "INSERT INTO {} ({}) VALUES {} ON CONFLICT DO NOTHING",
        table_name,
        valid_columns.join(", "),
        placeholders.join(", ")
    );

    // Build parameters
    let mut query_builder = sqlx::query(&query);
    
    for record in records {
        for column in valid_columns {
            let value = record.get(column).cloned().unwrap_or_default();
            query_builder = query_builder.bind(value);
        }
    }

    // Execute the query
    let result = query_builder
        .execute(pool)
        .await
        .map_err(|e| format!("Failed to execute bulk insert: {}", e))?;

    Ok(result.rows_affected() as usize)
}

// HTTP handlers
pub async fn process_csv_handler(
    axum::extract::State(pool): axum::extract::State<PgPool>,
    Json(request): Json<CsvProcessRequest>,
) -> Json<CsvProcessResponse> {
    let batch_size = request.batch_size.unwrap_or(1000);
    
    match process_csv_file(&pool, &request.file_path, &request.table_name, batch_size).await {
        Ok(response) => Json(response),
        Err(e) => Json(CsvProcessResponse {
            success: false,
            table_name: request.table_name,
            records_processed: 0,
            processing_time_ms: 0,
            error: Some(e),
        }),
    }
}

pub async fn process_csv_batch_handler(
    axum::extract::State(pool): axum::extract::State<PgPool>,
    Json(request): Json<CsvBatchProcessRequest>,
) -> Json<CsvBatchProcessResponse> {
    let batch_size = request.batch_size.unwrap_or(1000);
    
    match process_csv_batch(&pool, request.files, batch_size).await {
        Ok(response) => Json(response),
        Err(e) => Json(CsvBatchProcessResponse {
            success: false,
            total_files: 0,
            successful_files: 0,
            failed_files: 0,
            total_records: 0,
            total_processing_time_ms: 0,
            results: vec![CsvProcessResponse {
                success: false,
                table_name: "unknown".to_string(),
                records_processed: 0,
                processing_time_ms: 0,
                error: Some(e),
            }],
        }),
    }
}