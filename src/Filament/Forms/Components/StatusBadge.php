<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasBadge;
use Filament\Support\Concerns\HasColor;
use Illuminate\Database\Eloquent\Model;

class StatusBadge extends Field
{

    use HasBadge;
    use HasColor;

    // protected string $view = 'forms.components.status-badge';

    public const BADGE_VIEW = 'forms.components.status-badge';
}
