<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Staff manage the records of their own agency and nothing else. A record that
 * belongs to another agency is answered with "not found", so its existence is
 * never revealed.
 */
abstract class AgencyScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentAgency() !== null;
    }

    public function create(User $user): bool
    {
        return $user->currentAgency() !== null;
    }

    protected function ownsAgency(User $user, ?int $agencyId): Response
    {
        $agency = $user->currentAgency();

        return $agency !== null && $agency->id === $agencyId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
