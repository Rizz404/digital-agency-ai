<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\ServicePackage;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Catalog\Queries\SearchCatalog;

final class SearchCatalogTool implements Tool
{
    public function __construct(private readonly SearchCatalog $search) {}

    public function name(): string
    {
        return 'search_catalog';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search agency service packages by business need, category, service tag or budget. Prices are estimates, not binding quotes. Returns scope, requirements and whether new projects are accepted.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords such as "branding", "website", "campaign" — optional'],
                        'category' => [
                            'type' => 'string',
                            'enum' => ServicePackage::categories(),
                            'description' => 'Optional category filter',
                        ],
                        'service_tag' => [
                            'type' => 'string',
                            'enum' => ServicePackage::SERVICE_TAGS,
                            'description' => 'Optional business service tag',
                        ],
                        'max_price' => ['type' => 'number', 'description' => 'Optional budget per item, in the agency currency'],
                    ],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle(
            $context->agency,
            (string) ($arguments['query'] ?? ''),
            $arguments['category'] ?? null,
            $arguments['service_tag'] ?? null,
            isset($arguments['max_price']) ? (float) $arguments['max_price'] : null,
        );

        if ($items->isEmpty()) {
            return new ToolResult('No catalog item matches that request. Tell the guest honestly and suggest a nearby alternative from another search_catalog call, or call request_human_handover for custom requests.');
        }

        $results = $items->map(fn (ServicePackage $item) => CatalogSummaries::servicePackage($item, $context->agency, $context->locale))->values()->all();

        return ToolResult::json($results, ['type' => 'catalog_results', 'items' => $results]);
    }
}
