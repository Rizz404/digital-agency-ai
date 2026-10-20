<?php

namespace App\Modules\Assistant\Tools;

use App\Models\Agency;
use App\Models\Conversation;

/**
 * What a tool may act on: one agency, one conversation, in the guest's language.
 */
final readonly class ToolContext
{
    public function __construct(
        public Agency $agency,
        public Conversation $conversation,
        public string $locale,
    ) {}
}
