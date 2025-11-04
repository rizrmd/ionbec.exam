<?php

namespace App\Http\Requests;

use App\Services\QuestionDuplicatePreventionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'question' => 'required|string|min:10|max:10000',
            'type' => 'required|string|in:multiple-choice,true-false,essay,fill-blank',
            'score' => 'nullable|numeric|min:0|max:100',
            'is_random' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Item is required',
            'item_id.exists' => 'Selected item does not exist',
            'question.required' => 'Question content is required',
            'question.min' => 'Question must be at least 10 characters',
            'question.max' => 'Question cannot exceed 10,000 characters',
            'type.required' => 'Question type is required',
            'type.in' => 'Invalid question type selected',
            'score.numeric' => 'Score must be a number',
            'score.min' => 'Score cannot be negative',
            'score.max' => 'Score cannot exceed 100',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->failed()) {
                return;
            }

            $duplicatePrevention = app(QuestionDuplicatePreventionService::class);
            $validation = $duplicatePrevention->validateQuestion(
                $this->item_id,
                $this->question
            );

            if (!$validation['valid']) {
                $validator->errors()->add('question', $validation['error']);
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
                'duplicate_detected' => $validator->errors()->has('question') &&
                    str_contains($validator->errors()->first('question'), 'already exists'),
            ], 422)
        );
    }
}