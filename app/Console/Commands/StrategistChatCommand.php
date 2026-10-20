<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Modules\Conversation\Actions\ReplyToGuest;
use App\Modules\Conversation\Actions\StartConversation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('strategist:chat {agency? : Agency slug} {--locale=id : id|en|ja}')]
#[Description('Talk to the AI Digital Strategist for a agency from the terminal, for testing the tool-calling loop without the web UI.')]
class StrategistChatCommand extends Command
{
    public function handle(StartConversation $start, ReplyToGuest $reply): int
    {
        $agency = $this->argument('agency')
            ? Agency::where('slug', $this->argument('agency'))->first()
            : Agency::first();

        if (! $agency) {
            $this->error('No agency found. Seed one first: php artisan db:seed');

            return self::FAILURE;
        }

        if (blank(config('services.local_llm.base_url'))) {
            $this->error('LOCAL_LLM_BASE_URL is not set in .env — the strategist has no model endpoint to call.');

            return self::FAILURE;
        }

        $locale = $this->option('locale');
        $conversation = $start->handle($agency, $locale);

        $this->info("Chatting with the AI Digital Strategist for {$agency->name} ({$locale}). Type 'exit' to quit.");
        $this->newLine();

        while (true) {
            $guestMessage = $this->ask('You');

            if ($guestMessage === null || in_array(trim($guestMessage), ['exit', 'quit'], true)) {
                break;
            }

            $message = $reply->handle($agency, $conversation, $guestMessage);

            $this->newLine();
            $this->line('<fg=cyan>Strategist:</> '.($message->content ?? '(no text — see UI payload below)'));

            if ($message->ui_payload) {
                $this->line('<fg=gray>[ui_payload] '.json_encode($message->ui_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).'</>');
            }

            $conversation->refresh();
            if ($conversation->isHandedOver()) {
                $this->warn('Conversation handed over to the agency team. Ending session.');
                break;
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
