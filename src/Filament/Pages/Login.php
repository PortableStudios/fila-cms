<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Auth\Login as FilamentLogin;
use Filament\Support\Enums\ActionSize;

class Login extends FilamentLogin
{
    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
            ActionGroup::make($this->getSSOActions())->label('Single Sign-On')
            ->icon('heroicon-m-ellipsis-vertical')
            ->size(ActionSize::Medium)
            ->color('primary')
            ->button()
        ];
    }

    protected function getSSOActions()
    {
        $ssoButtons = [];
        $providers = config('fila-cms.sso.providers', ['google','facebook','linkedin']);
        foreach ($providers as $provider) {
            if (config('settings.sso.' . $provider . '.client_id') && config('settings.sso.' . $provider . '.client_secret')) {
                $ssoButtons[] = Action::make($provider)->label('Login with ' . ucfirst($provider))->action(function () use ($provider) {
                    return redirect()->route('sso.login', ['provider' => $provider]);
                });
            }
        }

        return $ssoButtons;
    }
}
