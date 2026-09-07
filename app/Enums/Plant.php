<?php

namespace App\Enums;

enum Plant: int
{
    
    case Tejar = 1;
    case Parramos = 2;

    public function label(): string
    {
        return match ($this) {
            self::Tejar => 'Planta Tejar',
            self::Parramos => 'Planta Parramos',
        };
    }
}

