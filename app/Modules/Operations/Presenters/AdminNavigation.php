<?php

namespace App\Modules\Operations\Presenters;

/**
 * The admin catalog: where each entry leads and what it is called.
 */
final class AdminNavigation
{
    /**
     * @return list<array{route: string, label: string}>
     */
    public static function items(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.service-packages.index', 'label' => 'Catalog'],
            ['route' => 'admin.consultants.index', 'label' => 'Consultants'],
            ['route' => 'admin.knowledge-items.index', 'label' => 'Knowledge base'],
            ['route' => 'admin.consultations.index', 'label' => 'Consultations'],
            ['route' => 'admin.handovers.index', 'label' => 'Handovers'],
        ];
    }
}
