<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Conversation;
use App\Models\HandoverRequest;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Conversation\Enums\HandoverReason;

final class RequestHumanHandoverTool implements Tool
{
    public function name(): string
    {
        return 'request_human_handover';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Hand this conversation over to a human agency team member. Use this for custom briefs, final quotes, complaints, multi-company consultations, strategy workshops, scope changes, cancellations and payment questions, or any question you cannot answer confidently from the available tools. Always write a clear summary so staff do not need to ask the guest to repeat themselves.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'reason' => [
                            'type' => 'string',
                            'enum' => ['special_request', 'complaint', 'group_consultation', 'strategy_workshop', 'custom_scope', 'payment_issue', 'low_confidence'],
                        ],
                        'summary' => ['type' => 'string', 'description' => 'What the guest wants and the relevant context gathered so far, written for a team member who has not seen this conversation'],
                    ],
                    'required' => ['reason', 'summary'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $reason = $arguments['reason'] ?? '';
        $summary = trim((string) ($arguments['summary'] ?? ''));

        if (! in_array($reason, HandoverReason::values(), true) || $summary === '' || mb_strlen($summary) > 2000) {
            return new ToolResult('Provide a supported handover reason and a clear summary of at most 2000 characters.');
        }

        HandoverRequest::create([
            'conversation_id' => $context->conversation->id,
            'reason' => $reason,
            'summary' => $summary,
            'status' => HandoverRequest::STATUS_OPEN,
        ]);

        $context->conversation->update([
            'status' => Conversation::STATUS_HANDED_OVER,
            'handover_summary' => $summary,
        ]);

        return ToolResult::json([
            'ok' => true,
            'message' => 'Your request is saved for the agency team. They can respond in this conversation; no response time is guaranteed.',
        ], ['type' => 'handover', 'reason' => $reason, 'summary' => $summary]);
    }
}
