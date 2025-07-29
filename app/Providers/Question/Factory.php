<?php

namespace App\Providers\Question;

use Illuminate\Support\Collection;
use App\Providers\Question\Contracts\QuestionType;

/**
 * Question type factory.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class Factory
{
    /**
     * Laravel collection instance.
     *
     * @var Collection
     */
    protected Collection $collection;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->collection = new Collection();
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param QuestionType $type
     *
     * @return $this
     */
    public function push(QuestionType $type): static
    {
        $this->collection->offsetSet($type->name, $type);

        return $this;
    }

    /**
     * Proxy all method call through laravel collection instance.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->collection, $method], $arguments);
    }
}
