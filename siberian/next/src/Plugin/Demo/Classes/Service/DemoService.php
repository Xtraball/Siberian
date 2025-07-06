<?php

declare(strict_types=1);

namespace App\Plugin\Demo\Classes\Service;

use App\Plugin\Demo\Classes\Entity\Demo;

class DemoService
{
    public function getDemos(): array
    {
        return [
            new Demo(),
            new Demo(),
        ];
    }
}