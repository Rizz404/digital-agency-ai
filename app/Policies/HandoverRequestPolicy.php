<?php

namespace App\Policies;

use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoverRequestPolicy extends AgencyScopedPolicy
{
    public function view(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsAgency($user, $handover->conversation->agency_id);
    }

    public function update(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsAgency($user, $handover->conversation->agency_id);
    }
}
