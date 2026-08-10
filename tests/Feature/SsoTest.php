<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    /** Stands in for the authorization code a provider appends to a successful callback. */
    protected const AUTH_CODE = 'test-authorization-code';

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

        $response = $this->get('/login/facebook/callback?code=' . static::AUTH_CODE);
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

        $response = $this->get('/login/facebook/callback?code=' . static::AUTH_CODE);
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

        $response = $this->get('/login/facebook/callback?code=' . static::AUTH_CODE);
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

        $response = $this->get('/login/linkedin/callback?code=' . static::AUTH_CODE);
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

        $response = $this->get('/login/linkedin/callback?code=' . static::AUTH_CODE);
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

        $response = $this->get('/login/linkedin/callback?code=' . static::AUTH_CODE);
        $response->assertStatus(302);

        $this->assertDatabaseHas('user_sso_links', [
            'user_id' => $user->id,
            'driver' => 'linkedin',
            'provider_id' => $abstractUser->getId(),
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_cancelled_callback_redirects_to_login_without_calling_the_provider()
    {
        Socialite::shouldReceive('driver')->never();

        $this->configureFacebookSso();

        $response = $this->get('/login/facebook/callback?error=access_denied&error_code=200&error_reason=user_denied');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Facebook login was cancelled.');
        $this->assertGuest();
    }

    public function test_cancelled_callback_returns_an_authenticated_user_to_their_profile()
    {
        Socialite::shouldReceive('driver')->never();

        $this->configureFacebookSso();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => Hash::make(Str::random(28))
        ]);

        $response = $this->actingAs($user)
            ->get('/login/facebook/callback?error=access_denied&error_reason=user_denied');

        $response->assertRedirect(route('user-profile-information.show'));
        $response->assertSessionHas('error', 'Facebook login was cancelled.');
    }

    public function test_bare_callback_hit_is_turned_away_without_logging()
    {
        Log::spy();
        Socialite::shouldReceive('driver')->never();

        $this->configureFacebookSso();

        $this->get('/login/facebook/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        Log::shouldNotHaveReceived('warning');
    }

    public function test_provider_reported_failure_is_logged()
    {
        Log::spy();
        Socialite::shouldReceive('driver')->never();

        $this->configureFacebookSso();

        $this->get('/login/facebook/callback?error=invalid_scope&error_description=Bad+scope')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'We could not complete your Facebook login. Please try again.');

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message, $context = []) => $context['driver'] === 'facebook'
                && $context['error'] === 'invalid_scope'
                && $context['error_description'] === 'Bad scope')
            ->once();
    }

    public function test_array_error_parameter_is_ignored_rather_than_cast()
    {
        Socialite::shouldReceive('driver')->never();

        $this->configureFacebookSso();

        $this->get('/login/facebook/callback?error[]=access_denied&error_reason[]=user_denied')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'We could not complete your Facebook login. Please try again.');
    }

    public function test_login_page_shows_the_sso_failure_message()
    {
        $this->withSession(['error' => 'Facebook login was cancelled.'])
            ->get(route('login'))
            ->assertSuccessful()
            ->assertSeeText('Facebook login was cancelled.');
    }

    protected function configureFacebookSso(): void
    {
        config(['settings.sso.facebook' => [
            'client_id' => 'test',
            'client_secret' => 'test']
        ]);
        FilaCms::ssoRoutes();
    }
}
