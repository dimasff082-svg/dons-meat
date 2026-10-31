<?php

namespace App\Enums;

enum OrderChannel: string
{
    case Online = 'online';
    case InStore = 'in_store';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online (Website)',
            self::InStore => 'Pembelian Langsung',
        };
    }
}
