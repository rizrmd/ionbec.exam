<?php

// Add this debug logging to MainController.php getQuestions method
/*
\Log::info('getQuestions called', [
    'item_hash' => $item->hash,
    'item_id' => $item->id,
    'questions_found' => $questions->count(),
    'first_question' => $questions->first() ? [
        'id' => $questions->first()->id,
        'type' => $questions->first()->type->name ?? 'no type',
        'answers_count' => $questions->first()->answers->count()
    ] : null,
    'attempt_found' => $attempt ? $attempt->id : 'no attempt'
]);
*/

// And add this debug logging to the frontend in Main.vue getQuestions function
/*
console.log('Loading questions for item:', {
    hash: item.hash,
    index: index,
    has_local_questions: item.questions && item.questions.length > 0
});

axios.get(route('exam.get-taker-answer', { item_hash: item.hash }))
    .then((res) => {
        console.log('Server response:', {
            success: !!res.data,
            questions_count: res.data ? res.data.questions.length : 0,
            first_question: res.data && res.data.questions.length > 0 ? {
                id: res.data.questions[0].id,
                type: res.data.questions[0].type?.name,
                has_answers: res.data.questions[0].answers && res.data.questions[0].answers.length > 0
            } : null
        });
        // ... existing code
    })
*/