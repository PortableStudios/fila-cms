<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Portable\FilaCms\Notifications\WelcomeNotification;
use Portable\FilaCms\Observers\AuthenticatableObserver;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Tests\User;
use ReflectionClass;

class WelcomeNotificationTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected string $userModel;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->userModel = config('auth.providers.users.model');
    }

    public function test_can_notify_users(): void
    {
        $this->userModel::unsetEventDispatcher();

        $user = $this->userModel::factory()
            ->unverified()
            ->create();

        Notification::send(
            $user,
            new WelcomeNotification($user)
        );

        Notification::assertSentToTimes(
            $user,
            WelcomeNotification::class,
            1
        );
    }

    public function test_user_notified_on_created_without_email_verification(): void
    {
        $featuresKey = 'fortify.features';
        $featureName = Features::emailVerification();

        $features = Config::get($featuresKey);

        if (Arr::exists($features, $featureName)) {
            Arr::pull($features, $featureName);
        }

        $this->assertNotContains($featureName, $features);

        Config::set($featuresKey, $features);

        $userData = $this->userModel::factory()
            ->unverified()
            ->make();

        Notification::assertNothingSent();

        $user = $this->userModel::create([
            ...$userData->toArray(),
            'password' => Hash::make(Str::password()),
        ]);

        Notification::assertSentToTimes(
            $user,
            WelcomeNotification::class,
            1
        );
    }

    public function test_user_notified_on_email_verification(): void
    {
        Notification::assertNothingSent();

        $featuresKey = 'fortify.features';
        $featureName = Features::emailVerification();

        $features = Config::get($featuresKey);

        if (!Arr::exists($features, $featureName)) {
            $features[] = $featureName;
        }

        $this->assertContains($featureName, $features);

        Config::set($featuresKey, $features);

        $user = $this->userModel::factory()
            ->unverified()
            ->create();

        Notification::assertNotSentTo($user, WelcomeNotification::class);

        event(new Verified($user));

        Notification::assertSentToTimes($user, WelcomeNotification::class, 1);
    }
}
