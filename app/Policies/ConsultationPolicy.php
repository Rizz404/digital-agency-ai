<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy extends AgencyScopedPolicy
{
    public function update(User $user, Consultation $consultation): Response
    {
        return $this->ownsAgency($user, $consultation->agency_id);
    }
}
