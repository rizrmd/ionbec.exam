<?php

namespace App\Providers\Question;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Question service provider.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class QuestionServiceProvider extends LaravelServiceProvider
{
    /**
     * Register the factory.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('exam.questions', function () {
            $factory = new Factory();

            $factory->push(new Types\BasicQuestionType([
                'name' => 'simple',
                'label' => 'Simple',
                'is_visible' => false,
                'is_scoreable' => false,
                'has_answers' => false,
                'description' => null,
            ]));

            $factory->push(new Types\BasicQuestionType([
                'name' => 'multiple-choice',
                'label' => 'Multiple Choice',
                'is_visible' => true,
                'is_scoreable' => true,
                'has_answers' => true,
                'description' => null,
            ]));

            $factory->push(new Types\BasicQuestionType([
                'name' => 'essay',
                'label' => 'Essay',
                'is_visible' => true,
                'is_scoreable' => false,
                'has_answers' => false,
                'description' => null,
            ]));

            $factory->push(new Types\BasicQuestionType([
                'name' => 'interview',
                'label' => 'Interview',
                'is_visible' => true,
                'is_scoreable' => true,
                'has_answers' => true,
                'description' => null,
            ]));

            return $factory;
        });
    }
}
