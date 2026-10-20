<?php

namespace App\Policies;

use App\Models\Consultant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultantPolicy extends AgencyScopedPolicy
{
    public function update(User $user, Consultant $consultant): Response
    {
        return $this->ownsAgency($user, $consultant->agency_id);
    }

    public function delete(User $user, Consultant $consultant): Response
    {
        return $this->ownsAgency($user, $consultant->agency_id);
    }
}
