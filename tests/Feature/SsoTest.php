<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\UserSsoLink;
use Portable\FilaCms\Tests\TestCase;

class SsoTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_no_facebook_redirect_blank_config()
    {
        config(['settings.sso.facebook' => []]);
        $response = $this->get('/login/facebook');
        $response->assertStatus(404);
    }

    public function test_facebook_redirect()
    {
        config(['settings.sso.facebook' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $response = $this->get('/login/facebook');
        $response->assertStatus(302);
    }

    public function test_no_google_redirect_blank_config()
    {
        config(['settings.sso.google' => []]);
        $response = $this->get('/login/google');
        $response->assertStatus(404);
    }

    public function test_google_redirect()
    {
        config(['settings.sso.google' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $response = $this->get('/login/google');
        $response->assertStatus(302);
    }

    public function test_no_linkedin_redirect_blank_config()
    {
        config(['settings.sso.linkedin' => []]);
        $response = $this->get('/login/linkedin');
        $response->assertStatus(404);
    }

    public function test_linkedin_redirect()
    {
        config(['settings.sso.linkedin' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $response = $this->get('/login/linkedin');
        $response->assertStatus(302);
    }

    public function test_facebook_callback_create_user()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($this->faker->uuid)
            ->shouldReceive('getName')
            ->andReturn($this->faker->name)
            ->shouldReceive('getEmail')
            ->andReturn($this->faker->email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.facebook' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $response = $this->get('/login/facebook/callback');
        $response->assertStatus(302);

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $abstractUser->getEmail())->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'facebook',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_facebook_callback_link_user()
    {
        $email = $this->faker->email;
        $name = $this->faker->name;
        $providerId = $this->faker->uuid;

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($providerId)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getEmail')
            ->andReturn($email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.facebook' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(28))
        ]);

        $this->assertDatabaseMissing('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'facebook',
            'provider_id' => $abstractUser->getId(),
        ]);

        $response = $this->get('/login/facebook/callback');
        $response->assertStatus(302);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'facebook',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }



    public function test_facebook_callback_login_user()
    {
        $email = $this->faker->email;
        $name = $this->faker->name;
        $providerId = $this->faker->uuid;

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($providerId)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getEmail')
            ->andReturn($email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.facebook' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(28))
        ]);

        UserSsoLink::create([
            'user_id' => $user->id,
            'driver' => 'facebook',
            'provider_id' => $providerId,
            'provider_token' => $abstractUser->token,
            'provider_refresh_token' => $abstractUser->refreshToken,
        ]);

        $response = $this->get('/login/facebook/callback');
        $response->assertStatus(302);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'facebook',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_linkedin_callback_create_user()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($this->faker->uuid)
            ->shouldReceive('getName')
            ->andReturn($this->faker->name)
            ->shouldReceive('getEmail')
            ->andReturn($this->faker->email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.linkedin' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $response = $this->get('/login/linkedin/callback');
        $response->assertStatus(302);

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $abstractUser->getEmail())->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_linkedin_callback_link_user()
    {
        $email = $this->faker->email;
        $name = $this->faker->name;
        $providerId = $this->faker->uuid;

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($providerId)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getEmail')
            ->andReturn($email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.linkedin' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(28))
        ]);

        $this->assertDatabaseMissing('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $abstractUser->getId(),
        ]);

        $response = $this->get('/login/linkedin/callback');
        $response->assertStatus(302);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_linkedin_callback_login_user()
    {
        $email = $this->faker->email;
        $name = $this->faker->name;
        $providerId = $this->faker->uuid;

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($providerId)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getEmail')
            ->andReturn($email);
        $abstractUser->token = $this->faker->uuid;
        $abstractUser->refreshToken = $this->faker->uuid;

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        config(['settings.sso.linkedin' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(28))
        ]);

        UserSsoLink::create([
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $providerId,
            'provider_token' => $abstractUser->token,
            'provider_refresh_token' => $abstractUser->refreshToken,
        ]);

        $response = $this->get('/login/linkedin/callback');
        $response->assertStatus(302);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }
}
