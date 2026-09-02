<?php

namespace App\Enums;

enum EquipmentType: string
{
    case MOUSE = 'mouse';
    case KEYBOARD = 'keyboard';
    case CHARGER = 'charger';
    case HEADSET = 'headset';
    case WEBCAM = 'webcam';
    case MONITOR = 'monitor';
    case LAPTOP = 'laptop';
    case DESKTOP = 'desktop';
    case PRINTER = 'printer';
    case CABLE = 'cable';
    case ADAPTER = 'adapter';
    case OTHER = 'other';
}
