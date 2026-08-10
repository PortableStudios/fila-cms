<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Fortify;
use Portable\FilaCms\Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected string $userModel;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Fortify::class;
        $this->userModel = config('auth.providers.users.model');
    }

    public function test_can_see_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertSuccessful()
            ->assertSeeText('forgot password')
            ->assertSee(route('password.request'));
    }

    public function test_can_view_forgot_password_page(): void
    {
        $this->get(route('password.request'))
            ->assertSuccessful()
            ->assertSeeText('Reset Password');
    }

    public function test_can_required_password_reset(): void
    {
        $user = $this->userModel::factory()->create();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertRedirect(url('/'));
    }
}
