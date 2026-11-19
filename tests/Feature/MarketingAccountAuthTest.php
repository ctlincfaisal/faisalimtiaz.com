<?php

namespace Tests\Feature;

use App\Models\MarketingAccount;
use App\Models\MarketingTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingAccountAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_creates_an_admin_with_a_private_account(): void
    {
        $this->post(route('marketing.register.submit'), [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertRedirect(route('marketing'));

        $user = User::where('email', 'customer@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('admin', $user->role);
        $this->assertNotNull($user->marketing_account_id);
        $this->assertSame($user->id, $user->account->owner_user_id);
    }

    public function test_new_user_can_open_settings_without_a_paddle_configuration_error(): void
    {
        $this->post(route('marketing.register.submit'), [
            'name' => 'New Customer',
            'email' => 'settings-customer@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertRedirect(route('marketing'));

        $user = User::where('email', 'settings-customer@example.com')->firstOrFail();
        $user->update(['marketing_onboarding_completed_at' => now()]);
        $this->actingAs($user->fresh());
        $this->get(route('marketing', ['tab' => 'settings', 'settings_tab' => 'billing']))
            ->assertOk()
            ->assertSee('Your plan')
            ->assertSee('Upgrade your workspace');
    }

    public function test_legacy_login_bootstraps_the_existing_owner_as_super_admin(): void
    {
        config()->set('marketing.auth.username', 'legacy-admin');
        config()->set('marketing.auth.bootstrap_password', 'legacy-secret');
        config()->set('marketing.super_admin.email', null);
        config()->set('marketing.super_admin.password', null);

        $this->post(route('marketing.login.submit'), [
            'identifier' => 'legacy-admin',
            'password' => 'legacy-secret',
        ])->assertRedirect(route('marketing'));

        $user = User::where('username', 'legacy-admin')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('super_admin', $user->role);
        $this->assertNotNull($user->marketing_account_id);
    }

    public function test_admin_can_add_a_moderator_to_their_account(): void
    {
        $account = MarketingAccount::create(['name' => 'Owner workspace', 'slug' => 'owner-workspace']);
        $admin = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'password-123',
            'marketing_account_id' => $account->id,
            'role' => 'admin',
        ]);
        $account->update(['owner_user_id' => $admin->id]);
        Auth::login($admin);

        $this->post(route('marketing.moderators.store'), [
            'name' => 'Moderator',
            'email' => 'moderator@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertRedirect(route('marketing', ['tab' => 'settings', 'settings_tab' => 'team']));

        $this->assertDatabaseHas('users', [
            'email' => 'moderator@example.com',
            'marketing_account_id' => $account->id,
            'role' => 'moderator',
        ]);
    }

    public function test_marketing_models_are_scoped_to_the_authenticated_account(): void
    {
        $firstAccount = MarketingAccount::create(['name' => 'First workspace', 'slug' => 'first-workspace']);
        $firstUser = User::create(['name' => 'First', 'email' => 'first@example.com', 'password' => 'password-123', 'marketing_account_id' => $firstAccount->id, 'role' => 'admin']);
        $secondAccount = MarketingAccount::create(['name' => 'Second workspace', 'slug' => 'second-workspace']);
        $secondUser = User::create(['name' => 'Second', 'email' => 'second@example.com', 'password' => 'password-123', 'marketing_account_id' => $secondAccount->id, 'role' => 'admin']);

        Auth::login($firstUser);
        MarketingTemplate::create(['name' => 'First template', 'subject' => 'Hello', 'content' => 'First']);
        Auth::login($secondUser);
        MarketingTemplate::create(['name' => 'Second template', 'subject' => 'Hello', 'content' => 'Second']);

        $this->assertSame(['Second template'], MarketingTemplate::query()->pluck('name')->all());
    }

    public function test_account_settings_update_profile_password_and_theme(): void
    {
        Storage::fake('local');
        $account = MarketingAccount::create(['name' => 'Account workspace', 'slug' => 'account-workspace']);
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'old-password', 'marketing_account_id' => $account->id, 'role' => 'admin']);
        Auth::login($user);

        $this->post(route('marketing.account.profile.update'), [
            'name' => 'Updated Owner',
            'email' => 'updated@example.com',
            'profile_photo' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect(route('marketing', ['tab' => 'settings', 'settings_tab' => 'account']));

        $this->post(route('marketing.account.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('marketing', ['tab' => 'settings', 'settings_tab' => 'account']));

        $this->post(route('marketing.account.theme.update'), ['theme' => 'dark'])
            ->assertRedirect(route('marketing', ['tab' => 'settings', 'settings_tab' => 'account']));

        $user = $user->fresh();
        $this->assertSame('Updated Owner', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('dark', $user->theme);
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertNotNull($user->profile_photo_path);
    }
}
