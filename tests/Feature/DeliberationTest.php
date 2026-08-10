<?php

use App\Enums\DecisionOutcome;
use App\Enums\DeliberationStatus;
use App\Enums\RequirementStatus;
use App\Models\Deliberation;
use App\Models\Department;
use App\Models\Event;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->department = Department::factory()->create();
    $this->otherDepartment = Department::factory()->create();
    $this->event = Event::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->director = User::factory()->director()->create();
    $this->hod = User::factory()->hod($this->department->id)->create();
    $this->otherHod = User::factory()->hod($this->otherDepartment->id)->create();
    $this->employee = User::factory()->employee($this->department->id)->create();
    $this->requirement = Requirement::factory()->create([
        'event_id' => $this->event->id,
        'department_id' => $this->department->id,
    ]);
});

it('allows admin to create a deliberation for any requirement', function () {
    $this->actingAs($this->admin)
        ->post(route('deliberations.store', $this->event), [
            'requirement_id' => $this->requirement->id,
            'title' => 'Budget concern',
            'impact_level' => 'critical',
            'description' => 'Need clarity on budget.',
        ])
        ->assertRedirect();

    expect(Deliberation::count())->toBe(1)
        ->and($this->requirement->fresh()->status)->toBe(RequirementStatus::UnderDeliberation);
});

it('allows hod to create a deliberation for their own department only', function () {
    $this->actingAs($this->hod)
        ->post(route('deliberations.store', $this->event), [
            'requirement_id' => $this->requirement->id,
            'title' => 'Budget concern',
            'impact_level' => 'critical',
        ])
        ->assertRedirect();

    expect(Deliberation::count())->toBe(1);
});

it('forbids hod from creating a deliberation for another department', function () {
    $this->actingAs($this->otherHod)
        ->post(route('deliberations.store', $this->event), [
            'requirement_id' => $this->requirement->id,
            'title' => 'Budget concern',
            'impact_level' => 'critical',
        ])
        ->assertForbidden();

    expect(Deliberation::count())->toBe(0);
});

it('forbids employees from creating deliberations', function () {
    $this->actingAs($this->employee)
        ->post(route('deliberations.store', $this->event), [
            'requirement_id' => $this->requirement->id,
            'title' => 'Budget concern',
            'impact_level' => 'critical',
        ])
        ->assertForbidden();
});

it('closes a deliberation with an outcome and updates requirement status', function (DecisionOutcome $outcome, RequirementStatus $expectedStatus) {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();

    $this->actingAs($this->admin)
        ->patch(route('deliberations.close', [$this->event, $deliberation]), [
            'outcome' => $outcome->value,
            'decision_notes' => 'Decision made.',
        ])
        ->assertRedirect();

    $deliberation->refresh();

    expect($deliberation->status)->toBe(DeliberationStatus::Closed)
        ->and($deliberation->outcome)->toBe($outcome)
        ->and($deliberation->decision_made)->toBeTrue()
        ->and($this->requirement->fresh()->status)->toBe($expectedStatus);
})->with([
    [DecisionOutcome::Approved, RequirementStatus::Approved],
    [DecisionOutcome::Rejected, RequirementStatus::Blocked],
    [DecisionOutcome::Deferred, RequirementStatus::AwaitingApproval],
    [DecisionOutcome::NoAction, RequirementStatus::Blocked],
]);

it('allows director to escalate an open deliberation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();

    $this->actingAs($this->director)
        ->patch(route('deliberations.escalate', [$this->event, $deliberation]), [
            'escalated_to' => $this->admin->id,
        ])
        ->assertRedirect();

    $deliberation->refresh();

    expect($deliberation->status)->toBe(DeliberationStatus::Escalated)
        ->and($deliberation->escalated_to)->toBe($this->admin->id);
});

it('allows hod to edit their own departments open deliberation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create([
        'title' => 'Old title',
    ]);

    $this->actingAs($this->hod)
        ->put(route('deliberations.update', [$this->event, $deliberation]), [
            'requirement_id' => $this->requirement->id,
            'title' => 'New title',
            'impact_level' => 'medium',
        ])
        ->assertRedirect(route('deliberations.show', [$this->event, $deliberation]));

    expect($deliberation->fresh()->title)->toBe('New title');
});

it('forbids hod from editing another departments deliberation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();

    $this->actingAs($this->otherHod)
        ->put(route('deliberations.update', [$this->event, $deliberation]), [
            'requirement_id' => $this->requirement->id,
            'title' => 'Hacked title',
            'impact_level' => 'medium',
        ])
        ->assertForbidden();
});

it('allows admin to delete a deliberation with comments', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();
    $deliberation->comments()->create([
        'user_id' => $this->employee->id,
        'comment' => 'A comment',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('deliberations.destroy', [$this->event, $deliberation]))
        ->assertRedirect(route('deliberations.index', $this->event));

    expect(Deliberation::count())->toBe(0)
        ->and($deliberation->comments()->count())->toBe(0);
});

it('allows employees to comment on open deliberations', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();

    $this->actingAs($this->employee)
        ->post(route('deliberations.comments.store', [$this->event, $deliberation]), [
            'comment' => 'My take on this.',
        ])
        ->assertRedirect();

    expect($deliberation->comments()->count())->toBe(1);
});

it('forbids commenting on closed deliberations', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->closed()->create();

    $this->actingAs($this->employee)
        ->post(route('deliberations.comments.store', [$this->event, $deliberation]), [
            'comment' => 'Too late.',
        ])
        ->assertForbidden();
});

it('directs a deliberation to selected participants', function () {
    $this->actingAs($this->hod)
        ->post(route('deliberations.store', $this->event), [
            'title' => 'PA system hiring',
            'impact_level' => 'critical',
            'recommendation' => 'Hire Sound Masters Ltd for PA systems.',
            'participants' => [$this->director->id, $this->admin->id],
        ])
        ->assertRedirect();

    $deliberation = Deliberation::latest()->first();

    expect($deliberation->participants->pluck('id')->toArray())
        ->toContain($this->director->id, $this->admin->id)
        ->and($deliberation->recommendation)->toBe('Hire Sound Masters Ltd for PA systems.');
});

it('allows a participant to approve a recommendation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create([
        'created_by' => $this->hod->id,
    ]);
    $deliberation->participants()->attach($this->director->id);

    $this->actingAs($this->director)
        ->post(route('deliberations.approve', [$this->event, $deliberation]), [
            'notes' => 'Approved, proceed with procurement.',
        ])
        ->assertRedirect();

    $pivot = $deliberation->participantResponseFor($this->director);

    expect($pivot->approved_at)->not->toBeNull()
        ->and($pivot->notes)->toBe('Approved, proceed with procurement.');
});

it('forbids non-participants from approving a recommendation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create();

    $this->actingAs($this->director)
        ->post(route('deliberations.approve', [$this->event, $deliberation]), [])
        ->assertForbidden();
});

it('allows a participant to reject a recommendation', function () {
    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create([
        'created_by' => $this->hod->id,
    ]);
    $deliberation->participants()->attach($this->director->id);

    $this->actingAs($this->director)
        ->post(route('deliberations.reject', [$this->event, $deliberation]), [
            'notes' => 'Too expensive.',
        ])
        ->assertRedirect();

    $pivot = $deliberation->participantResponseFor($this->director);

    expect($pivot->rejected_at)->not->toBeNull()
        ->and($pivot->notes)->toBe('Too expensive.');
});

it('stores attachments when creating a deliberation', function () {
    Storage::fake('local');

    $this->actingAs($this->hod)
        ->post(route('deliberations.store', $this->event), [
            'title' => 'PA system hiring',
            'impact_level' => 'critical',
            'recommendation' => 'Hire Sound Masters Ltd.',
            'attachments' => [
                UploadedFile::fake()->create('memo.pdf', 500),
            ],
        ])
        ->assertRedirect();

    $deliberation = Deliberation::latest()->first();

    expect($deliberation->attachments)->toHaveCount(1)
        ->and($deliberation->attachments->first()->original_name)->toBe('memo.pdf');
});

it('allows participants to upload additional attachments', function () {
    Storage::fake('local');

    $deliberation = Deliberation::factory()->forRequirement($this->requirement)->create([
        'created_by' => $this->hod->id,
    ]);
    $deliberation->participants()->attach($this->director->id);

    $this->actingAs($this->director)
        ->post(route('deliberations.attachments.store', [$this->event, $deliberation]), [
            'attachments' => [
                UploadedFile::fake()->create('quote.pdf', 300),
            ],
        ])
        ->assertRedirect();

    expect($deliberation->fresh()->attachments)->toHaveCount(1);
});
