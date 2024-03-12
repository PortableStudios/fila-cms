<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasBadge;
use Filament\Support\Concerns\HasColor;

class StatusBadge extends Field
{
    use HasBadge;
    use HasColor;

    public const BADGE_VIEW = 'fila-cms::filament.forms.components.status-badge';
}
