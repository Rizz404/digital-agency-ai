<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Modules\Consultation\Actions\OpenConsultationSlots;
use Illuminate\Console\Command;

class OpenConsultationSlotsCommand extends Command
{
    protected $signature = 'agency:open-slots {--agency= : Limit to a tenant slug}';

    protected $description = 'Open a rolling 60-day window of weekday consultation slots without overwriting held capacity';

    public function handle(OpenConsultationSlots $open): int
    {
        $query = Agency::query()->when($this->option('agency'), fn ($q) => $q->where('slug', $this->option('agency')));
        if (! $query->exists()) {
            $this->error('No matching agency found.');

            return self::FAILURE;
        }
        foreach ($query->orderBy('id')->cursor() as $agency) {
            $count = $open->handle($agency);
            $this->info($agency->slug.': '.$count.' future slot records prepared; existing holds preserved.');
        }

        return self::SUCCESS;
    }
}
