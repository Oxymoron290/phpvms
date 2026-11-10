<?php

declare(strict_types=1);

use App\Enums\UserState;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Notifications\Messages\AdminPilotEmail;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

// ident is an accessor built from the airline code and pilot_id, not a column,
// so sorting on it has to go through pilot_id.
it('sorts the ident column by pilot_id', function (): void {
    $admin = createAdminUser(['pilot_id' => 50]);
    $this->actingAs($admin);

    $third = User::factory()->create(['pilot_id' => 30]);
    $first = User::factory()->create(['pilot_id' => 10]);
    $second = User::factory()->create(['pilot_id' => 20]);

    Livewire::test(ListUsers::class)
        ->sortTable('ident')
        ->assertCanSeeTableRecords([$first, $second, $third, $admin], inOrder: true)
        ->sortTable('ident', 'desc')
        ->assertCanSeeTableRecords([$admin, $third, $second, $first], inOrder: true);
});

it('emails only the selected pilots from the bulk action', function (): void {
    $this->actingAs(createAdminUser());

    $selectedPilots = User::factory()->count(2)->create();
    $unselectedPilot = User::factory()->create();
    Notification::fake();

    Livewire::test(ListUsers::class)
        ->selectTableRecords($selectedPilots->pluck('id')->all())
        ->callAction(TestAction::make('email_pilots')->table()->bulk(), [
            'subject' => 'Schedule update',
            'message' => 'Please review the new schedule.',
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    Notification::assertSentTo($selectedPilots->all(), AdminPilotEmail::class);
    Notification::assertNotSentTo([$unselectedPilot], AdminPilotEmail::class);
});

it('does not email soft-deleted or deleted-state pilots', function (): void {
    $this->actingAs(createAdminUser());

    $selectedPilot = User::factory()->create();
    $softDeletedPilot = User::factory()->create();
    $softDeletedPilot->delete();
    $deletedStatePilot = User::factory()->create(['state' => UserState::DELETED]);
    Notification::fake();

    Livewire::test(ListUsers::class)
        ->selectTableRecords([$selectedPilot->id, $softDeletedPilot->id, $deletedStatePilot->id])
        ->callAction(TestAction::make('email_pilots')->table()->bulk(), [
            'subject' => 'Schedule update',
            'message' => 'Please review the new schedule.',
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    Notification::assertSentTo([$selectedPilot], AdminPilotEmail::class);
    Notification::assertNotSentTo([$softDeletedPilot, $deletedStatePilot], AdminPilotEmail::class);
});

it('does not queue an email when all selected pilots are deleted', function (): void {
    $this->actingAs(createAdminUser());

    $deletedPilot = User::factory()->create(['state' => UserState::DELETED]);
    Notification::fake();

    Livewire::test(ListUsers::class)
        ->selectTableRecords([$deletedPilot->id])
        ->callAction(TestAction::make('email_pilots')->table()->bulk(), [
            'subject' => 'Schedule update',
            'message' => 'Please review the new schedule.',
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    Notification::assertNothingSent();
});
