<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RustService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('RUST_SERVICE_URL', 'http://rust-service:3000');
    }

    /**
     * Check if the Rust service is healthy
     */
    public function health(): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/health");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'status' => 'error',
                'message' => 'Health check failed'
            ];
        } catch (\Exception $e) {
            Log::error('Rust service health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a task through the Rust service
     */
    public function processTask(string $taskType, array $data): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/process", [
                'task_type' => $taskType,
                'data' => $data
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'message' => 'Task processing failed',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service task processing failed', [
                'task_type' => $taskType,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate exam score using Rust service
     */
    public function calculateScore(int $examId, array $answers): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/score", [
                'exam_id' => $examId,
                'answers' => $answers
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'error' => 'Score calculation failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service score calculation failed', [
                'exam_id' => $examId,
                'error' => $e->getMessage()
            ]);
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate exam structure using Rust service
     */
    public function validateExam(int $examId, array $questions): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/validate", [
                'exam_id' => $examId,
                'questions' => $questions
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'valid' => false,
                'errors' => ['Validation request failed'],
                'warnings' => []
            ];
        } catch (\Exception $e) {
            Log::error('Rust service exam validation failed', [
                'exam_id' => $examId,
                'error' => $e->getMessage()
            ]);
            return [
                'valid' => false,
                'errors' => [$e->getMessage()],
                'warnings' => []
            ];
        }
    }

    /**
     * Calculate score for a single attempt using the real Rust implementation
     */
    public function calculateScoreForAttempt(int $attemptId): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/score/calculate", [
                'attempt_id' => $attemptId
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'error' => 'Score calculation failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service score calculation failed', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate scores for multiple attempts in batch
     */
    public function calculateScoresBatch(array $attemptIds): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/score/batch", [
                'attempt_ids' => $attemptIds
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'error' => 'Batch score calculation failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service batch score calculation failed', [
                'attempt_ids' => $attemptIds,
                'error' => $e->getMessage()
            ]);
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a single CSV file using Rust service
     */
    public function processCsvFile(string $filePath, string $tableName, int $batchSize = 1000): array
    {
        try {
            $response = Http::timeout(300)->post("{$this->baseUrl}/api/csv/process", [
                'file_path' => $filePath,
                'table_name' => $tableName,
                'batch_size' => $batchSize
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'CSV processing failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service CSV processing failed', [
                'file_path' => $filePath,
                'table_name' => $tableName,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Load exam data efficiently using Rust service
     */
    public function loadExamData(int $examId, int $deliveryId, ?int $takerId = null): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/exam/load", [
                'exam_id' => $examId,
                'delivery_id' => $deliveryId,
                'taker_id' => $takerId
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Exam data loading failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service exam data loading failed', [
                'exam_id' => $examId,
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process multiple CSV files in batch using Rust service
     */
    public function processCsvBatch(array $files, int $batchSize = 1000): array
    {
        try {
            $response = Http::timeout(600)->post("{$this->baseUrl}/api/csv/batch", [
                'files' => $files,
                'batch_size' => $batchSize
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Batch CSV processing failed',
                'details' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Rust service batch CSV processing failed', [
                'files_count' => count($files),
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}