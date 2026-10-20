<?php

namespace App\Policies;

use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePackagePolicy extends AgencyScopedPolicy
{
    public function update(User $user, ServicePackage $servicePackage): Response
    {
        return $this->ownsAgency($user, $servicePackage->agency_id);
    }

    public function delete(User $user, ServicePackage $servicePackage): Response
    {
        return $this->ownsAgency($user, $servicePackage->agency_id);
    }
}
