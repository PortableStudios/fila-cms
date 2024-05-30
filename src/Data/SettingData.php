<?php

namespace Portable\FilaCms\Data;

use Closure;

class SettingData
{
    public function __construct(
        public string $tabName,
        public string $groupName,
        public int $order,
        public Closure $fields,
    ) {
    }
}
