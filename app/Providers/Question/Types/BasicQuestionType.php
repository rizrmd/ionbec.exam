<?php

namespace App\Providers\Question\Types;

use Illuminate\Support\Fluent;
use App\Providers\Question\Contracts\QuestionType;

/**
 * Basic Question type.
 *
 * @author      veelasky <veelasky@gmail.com>
 *
 * @property string $name
 * @property string $label
 * @property bool   $is_visible
 * @property bool   $is_scoreable
 * @property bool   $has_answers
 * @property string $description
 */
class BasicQuestionType extends Fluent implements QuestionType
{
    /**
     * BasicQuestionType constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->validateAttributes($attributes);

        parent::__construct($attributes);
    }

    /**
     * Validate required keys.
     *
     * @param array $attributes
     *
     * @return bool
     */
    protected function validateAttributes(array $attributes = []): bool
    {
        $requiredKeys = ['name', 'label', 'is_scoreable', 'has_answers'];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $attributes)) {
                throw new \RuntimeException('Question Type must have `'.$key.'` attribute');
            }
        }

        return true;
    }
}
