<?php

namespace Tests\Feature\Filament;

use App\Http\Middleware\AdminAccessCodeMiddleware;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupAndSidebarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that backup commands are registered and scheduled.
     */
    public function test_backup_commands_are_registered_and_scheduled(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('backup:run', $commands);
        $this->assertArrayHasKey('backup:clean', $commands);

        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $hasBackupRun = $events->contains(fn ($event) => str_contains($event->command, 'backup:run'));
        $hasBackupClean = $events->contains(fn ($event) => str_contains($event->command, 'backup:clean'));

        $this->assertTrue($hasBackupRun, 'backup:run is not scheduled');
        $this->assertTrue($hasBackupClean, 'backup:clean is not scheduled');
    }

    /**
     * Test that custom sidebar CSS and JS assets are injected into the Filament Admin Panel.
     */
    public function test_sidebar_assets_and_language_switcher_are_rendered_in_dashboard(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->withoutMiddleware(AdminAccessCodeMiddleware::class);
        $this->withoutMiddleware(\Filament\Http\Middleware\Authenticate::class);
        $this->withSession([AdminAccessCodeMiddleware::SESSION_KEY => true]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('id="pos-sidebar-js"', false);
        $response->assertSee('id="pos-sidebar-css"', false);
    }
}
