<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class GetServicePackageTool implements Tool
{
    public function name(): string
    {
        return 'get_service_package';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Read one agency service package by slug, including its scope, estimated price, requirements, delivery model and estimated working days. Final quote is confirmed by the agency team.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'service_package_slug' => ['type' => 'string'],
                    ],
                    'required' => ['service_package_slug'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $item = CatalogSummaries::servicePackageBySlug($context->agency, (string) ($arguments['service_package_slug'] ?? ''));

        if (! $item) {
            return new ToolResult('Catalog item not found.');
        }

        $detail = [
            ...CatalogSummaries::servicePackage($item, $context->agency, $context->locale),
            'description' => $item->translatedDescription($context->locale),
            'estimated_days' => $item->estimated_days,
            'note' => 'Price is an estimate. Scope, delivery schedule and the final quote require agency team confirmation. No automatic payment.',
        ];

        return ToolResult::json($detail, ['type' => 'catalog_detail', 'item' => $detail]);
    }
}
