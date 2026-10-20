<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    public const PASSWORD_MIN = 12;

    protected $signature = 'agency:create-admin {--email=} {--name=} {--agency= : Tenant slug}';

    protected $description = 'Create an agency owner using a privately entered password';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Interactive password input is required; stdin is supported.');

            return self::FAILURE;
        }
        $data = ['email' => $this->option('email'), 'name' => $this->option('name'), 'agency' => $this->option('agency')];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'agency' => ['required', 'string', 'exists:agencies,slug'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $agency = Agency::where('slug', $data['agency'])->firstOrFail();
        $password = $this->secret('Password (at least '.self::PASSWORD_MIN.' characters)', false);
        $confirmation = $this->secret('Confirm password', false);
        if (! is_string($password) || mb_strlen($password) < self::PASSWORD_MIN || $password !== $confirmation) {
            $this->error('Password must meet the minimum length and match its confirmation.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($data, $password, $agency) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $password]);
            $agency->users()->syncWithoutDetaching([$user->id => ['role' => 'owner', 'status' => 'active']]);
        });
        $this->info('Admin '.$data['email'].' created.');

        return self::SUCCESS;
    }
}
