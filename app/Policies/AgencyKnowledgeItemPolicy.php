<?php

namespace App\Policies;

use App\Models\AgencyKnowledgeItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgencyKnowledgeItemPolicy extends AgencyScopedPolicy
{
    public function update(User $user, AgencyKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsAgency($user, $knowledgeItem->agency_id);
    }

    public function delete(User $user, AgencyKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsAgency($user, $knowledgeItem->agency_id);
    }
}
