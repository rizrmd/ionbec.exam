<?php

namespace App\Jobs;

use App\Events\ClientCloneProgress;
use App\Models\Client;
use App\Models\Categories\Category;
use App\Models\Exams\Question;
use App\Models\Exams\Exam;
use App\Models\Takers\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloneClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Client $originalClient;
    protected string $newClientName;
    protected string $newClientSlug;
    protected array $options;
    protected string $jobId;

    public function __construct(Client $originalClient, string $newClientName, string $newClientSlug, array $options = [])
    {
        $this->originalClient = $originalClient;
        $this->newClientName = $newClientName;
        $this->newClientSlug = $newClientSlug;
        $this->options = $options;
        $this->jobId = uniqid('clone_', true);
    }

    public function handle()
    {
        DB::beginTransaction();

        try {
            $this->broadcastProgress(0, 'Initializing clone process...');

            // Step 1: Clone basic client info (10%)
            $newClient = $this->cloneBasicClientInfo();
            $this->broadcastProgress(10, 'Client information copied');

            // Step 2: Clone categories if requested (25%)
            $categoryMapping = [];
            if ($this->options['clone_categories'] ?? true) {
                $categoryMapping = $this->cloneCategories($newClient);
                $this->broadcastProgress(25, 'Categories cloned');
            }

            // Step 3: Clone questions if requested (50%)
            $questionMapping = [];
            if ($this->options['clone_questions'] ?? true) {
                $questionMapping = $this->cloneQuestions($newClient, $categoryMapping);
                $this->broadcastProgress(50, 'Questions cloned');
            }

            // Step 4: Clone exams if requested (75%)
            if ($this->options['clone_exams'] ?? true) {
                $this->cloneExams($newClient, $questionMapping);
                $this->broadcastProgress(75, 'Exams cloned');
            }

            // Step 5: Clone groups if requested (90%)
            if ($this->options['clone_groups'] ?? true) {
                $this->cloneGroups($newClient);
                $this->broadcastProgress(90, 'Groups cloned');
            }

            // Step 6: Finalize (100%)
            $this->broadcastProgress(95, 'Finalizing...');
            $this->broadcastProgress(100, 'Clone completed successfully!', [
                'client_id' => $newClient->id,
                'client_name' => $newClient->name
            ]);

            DB::commit();

            Log::info('Client cloned successfully', [
                'original_client_id' => $this->originalClient->id,
                'new_client_id' => $newClient->id,
                'job_id' => $this->jobId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->broadcastProgress(-1, 'Clone failed: ' . $e->getMessage(), ['error' => $e->getMessage()]);
            
            Log::error('Client clone failed', [
                'original_client_id' => $this->originalClient->id,
                'error' => $e->getMessage(),
                'job_id' => $this->jobId
            ]);

            throw $e;
        }
    }

    protected function cloneBasicClientInfo(): Client
    {
        $clientData = $this->originalClient->toArray();
        
        // Remove id and timestamps
        unset($clientData['id'], $clientData['created_at'], $clientData['updated_at'], $clientData['deleted_at']);
        
        // Set new name and slug
        $clientData['name'] = $this->newClientName;
        $clientData['slug'] = $this->newClientSlug;
        
        // Reset domains to empty array (require manual configuration)
        $clientData['domains'] = [];
        
        // Clone logo if exists
        if ($this->originalClient->logo && !filter_var($this->originalClient->logo, FILTER_VALIDATE_URL)) {
            $originalPath = $this->originalClient->logo;
            if (Storage::disk('public')->exists($originalPath)) {
                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $newPath = 'client-logos/' . $this->newClientSlug . '_logo.' . $extension;
                Storage::disk('public')->copy($originalPath, $newPath);
                $clientData['logo'] = $newPath;
            }
        }

        return Client::create($clientData);
    }

    protected function cloneCategories(Client $newClient): array
    {
        $categories = $this->originalClient->categories()->get();
        $mapping = [];
        $totalCategories = $categories->count();
        
        foreach ($categories as $index => $category) {
            $categoryData = $category->toArray();
            unset($categoryData['id'], $categoryData['created_at'], $categoryData['updated_at'], $categoryData['deleted_at']);
            
            $categoryData['client_id'] = $newClient->id;
            
            $newCategory = Category::create($categoryData);
            $mapping[$category->id] = $newCategory->id;
            
            // Broadcast progress for each category
            if ($totalCategories > 10) { // Only update for larger sets
                $progress = 25 + (($index + 1) / $totalCategories) * 25;
                $this->broadcastProgress($progress, "Cloning categories... (" . ($index + 1) . "/{$totalCategories})");
            }
        }
        
        return $mapping;
    }

    protected function cloneQuestions(Client $newClient, array $categoryMapping): array
    {
        $questions = $this->originalClient->questions()->get();
        $mapping = [];
        $totalQuestions = $questions->count();
        
        foreach ($questions as $index => $question) {
            $questionData = $question->toArray();
            unset($questionData['id'], $questionData['created_at'], $questionData['updated_at'], $questionData['deleted_at']);
            
            $questionData['client_id'] = $newClient->id;
            
            // Map category if it was cloned
            if (isset($categoryMapping[$question->category_id])) {
                $questionData['category_id'] = $categoryMapping[$question->category_id];
            } else {
                $questionData['category_id'] = null;
            }
            
            $newQuestion = Question::create($questionData);
            $mapping[$question->id] = $newQuestion->id;
            
            // Broadcast progress for larger question sets
            if ($totalQuestions > 50) {
                $progress = 25 + (($index + 1) / $totalQuestions) * 25;
                $this->broadcastProgress($progress, "Cloning questions... (" . ($index + 1) . "/{$totalQuestions})");
            }
        }
        
        return $mapping;
    }

    protected function cloneExams(Client $newClient, array $questionMapping)
    {
        $exams = $this->originalClient->exams()->with(['questions'])->get();
        $totalExams = $exams->count();
        
        foreach ($exams as $index => $exam) {
            $examData = $exam->toArray();
            unset($examData['id'], $examData['created_at'], $examData['updated_at'], $examData['deleted_at'], $examData['questions']);
            
            $examData['client_id'] = $newClient->id;
            $examData['is_published'] = false; // Set as draft
            
            $newExam = Exam::create($examData);
            
            // Clone question associations
            $questionIds = [];
            foreach ($exam->questions as $question) {
                if (isset($questionMapping[$question->id])) {
                    $questionIds[] = $questionMapping[$question->id];
                }
            }
            
            if (!empty($questionIds)) {
                $newExam->questions()->attach($questionIds);
            }
            
            $progress = 50 + (($index + 1) / $totalExams) * 25;
            $this->broadcastProgress($progress, "Cloning exams... (" . ($index + 1) . "/{$totalExams})");
        }
    }

    protected function cloneGroups(Client $newClient)
    {
        $groups = $this->originalClient->groups()->get();
        $totalGroups = $groups->count();
        
        foreach ($groups as $index => $group) {
            $groupData = $group->toArray();
            unset($groupData['id'], $groupData['created_at'], $groupData['updated_at'], $groupData['deleted_at']);
            
            $groupData['client_id'] = $newClient->id;
            // Don't clone takers - empty groups only
            
            Group::create($groupData);
            
            $progress = 75 + (($index + 1) / $totalGroups) * 15;
            $this->broadcastProgress($progress, "Cloning groups... (" . ($index + 1) . "/{$totalGroups})");
        }
    }

    protected function broadcastProgress(int $percentage, string $message, array $data = [])
    {
        broadcast(new ClientCloneProgress(
            $this->jobId,
            $percentage,
            $message,
            array_merge($data, [
                'original_client_id' => $this->originalClient->id,
                'timestamp' => now()
            ])
        ));
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}