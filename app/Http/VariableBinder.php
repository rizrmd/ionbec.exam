<?php

namespace App\Http;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use Dentro\Yalr\BaseRoute;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use App\Models\Accounts\User;
use App\Models\Attempts\Attempt;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;
use App\Models\Attachments\Attachment;

class VariableBinder extends BaseRoute
{
    public function register(): void
    {
        $this->router->bind('category_hash', static fn ($categoryHash) => Category::byHashOrFail($categoryHash));
        $this->router->bind('user_hash', static fn ($userHash) => User::byHashOrFail($userHash));
        $this->router->bind('taker_hash', static fn ($takerHash) => Taker::byHashOrFail($takerHash));
        $this->router->bind('group_hash', static fn ($groupHash) => Group::byHashOrFail($groupHash));
        $this->router->bind('exam_hash', static fn ($examHash) => Exam::byHashOrFail($examHash));
        $this->router->bind('item_hash', static fn ($itemHash) => Item::byHashOrFail($itemHash));
        $this->router->bind('delivery_hash', static fn ($deliveryHash) => Delivery::byHashOrFail($deliveryHash));
        $this->router->bind('attempt_hash', static fn ($attemptHash) => Attempt::byHashOrFail($attemptHash));
        $this->router->bind('attachment_uuid', static fn ($attachUuid) => Attachment::query()->where('id', $attachUuid)->first());
    }
}
