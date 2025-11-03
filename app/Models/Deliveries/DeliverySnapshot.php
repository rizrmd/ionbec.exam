<?php

namespace App\Models\Deliveries;

use App\Models\Exams\Exam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Delivery Snapshot Model
 *
 * Stores a snapshot of the exam structure at the time a delivery is created.
 * This ensures exam integrity - all participants see the same exam regardless
 * of changes made to the master exam after delivery creation.
 *
 * @property int $id
 * @property int $delivery_id
 * @property int $exam_id
 * @property array $exam_structure Complete exam structure: items, questions, answers
 * @property int $total_questions Total questions in the snapshot
 * @property int $total_items Total items in the snapshot
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Delivery $delivery
 * @property-read Exam $exam
 */
class DeliverySnapshot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'delivery_snapshots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'delivery_id',
        'exam_id',
        'exam_structure',
        'total_questions',
        'total_items',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exam_structure' => 'array',
        'total_questions' => 'integer',
        'total_items' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the delivery that owns this snapshot.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    /**
     * Get the exam that this snapshot is based on.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Get items from the snapshot structure.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems()
    {
        return collect($this->exam_structure['items'] ?? []);
    }

    /**
     * Get a specific item by ID from the snapshot.
     *
     * @param int $itemId
     * @return array|null
     */
    public function getItem(int $itemId): ?array
    {
        return $this->getItems()->firstWhere('id', $itemId);
    }

    /**
     * Get all questions from the snapshot.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllQuestions()
    {
        $questions = [];
        foreach ($this->getItems() as $item) {
            if (isset($item['questions']) && is_array($item['questions'])) {
                $questions = array_merge($questions, $item['questions']);
            }
        }
        return collect($questions);
    }

    /**
     * Check if snapshot contains a specific question.
     *
     * @param int $questionId
     * @return bool
     */
    public function hasQuestion(int $questionId): bool
    {
        return $this->getAllQuestions()->contains('id', $questionId);
    }
}
