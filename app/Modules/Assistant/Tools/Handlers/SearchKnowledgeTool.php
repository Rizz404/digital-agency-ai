<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\AgencyKnowledgeItem;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Knowledge\Queries\SearchKnowledge;

final class SearchKnowledgeTool implements Tool
{
    public function __construct(private readonly SearchKnowledge $search) {}

    public function name(): string
    {
        return 'search_knowledge';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search the approved agency knowledge base for collaboration process, policies, final quote rules, office hours, location and FAQs. Use stored information instead of guessing. Returns up to 5 matching entries.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords from the visitor question, e.g. "brief", "proposal", "process" or "cancellation"'],
                        'category' => [
                            'type' => 'string',
                            'enum' => ['general', 'facilities', 'policies', 'events', 'location', 'faq'],
                            'description' => 'Optional category filter',
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle($context->agency, (string) ($arguments['query'] ?? ''), $arguments['category'] ?? null);

        if ($items->isEmpty()) {
            return new ToolResult('No matching knowledge base entry was found. Do not guess the answer — tell the guest you will check with the team, or call request_human_handover.');
        }

        return ToolResult::json($items->map(fn (AgencyKnowledgeItem $item) => [
            'category' => $item->category,
            'title' => $item->translatedTitle($context->locale),
            'body' => $item->translatedBody($context->locale),
        ])->values()->all());
    }
}
