<?php

namespace Tests\Support;

use App\Models\Agency;
use App\Models\Conversation;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolExecutor;

/**
 * Runs one AI Digital Strategist tool the way the model would, against real agency data.
 */
trait RunsStrategistTools
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{text: string, ui: ?array<string, mixed>}
     */
    protected function runTool(Agency $agency, Conversation $conversation, string $name, array $input): array
    {
        $result = app(ToolExecutor::class)->execute(new ToolContext($agency, $conversation, $conversation->locale), $name, $input);

        return ['text' => $result->text, 'ui' => $result->ui];
    }
}
