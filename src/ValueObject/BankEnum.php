<?php
declare(strict_types=1);

namespace App\ValueObject;

use MyCLabs\Enum\Enum;

final class BankEnum extends Enum
{
    private const ECB = 'ecb';
    private const CBR = 'cbr';
}
