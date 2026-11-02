<?php

use App\Models\PlanningWeek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Bug Condition Exploration Test - Accommodation Route Fix
 *
 * **Validates: Requirements 1.1, 1.2, 2.1, 2.2**
 *
 * CRITICAL: This test is EXPECTED TO FAIL on unfixed code.
 * The failure confirms the bug exists (RouteNotFoundException during view rendering).
 *
 * After the fix is implemented, this test will pass by verifying:
 * - The planning week show page renders successfully without route exceptions
 * - The accommodation button href contains a valid URL
 * - The URL uses the correct route name with admin prefix
 */
test('planning week show page renders without route exception and contains valid accommodation link', function () {
    // Property 1: Bug Condition - Accommodation Button Route Exception
    //
    // For any planning week detail page, the page should render without throwing
    // a RouteNotFoundException, and the accommodation button should contain a valid
    // URL to the accommodation edit page.
    //
    // ON UNFIXED CODE: This will throw RouteNotFoundException during view rendering
    // "Route [accommodation.edit] not defined" at line 28 of show.blade.php
    //
    // ON FIXED CODE: Page renders successfully with valid accommodation URL

    $admin = User::factory()->admin()->create();
    $week = PlanningWeek::factory()->create();

    // This will fail on unfixed code with 500 error:
    // RouteNotFoundException: Route [accommodation.edit] not defined
    // thrown when the view tries to render: route('accommodation.edit', $week)
    //
    // After fix, it should return 200 OK
    $response = $this->actingAs($admin)
        ->get(route('admin.planning-weeks.show', $week));

    // Verify page renders successfully (no route exception)
    $response->assertStatus(200);

    // Verify the accommodation button href contains the correct URL pattern
    $expectedUrl = route('admin.accommodation.edit', $week);
    $response->assertSee($expectedUrl, false); // false = don't escape HTML

    // Additional verification: URL follows expected pattern
    expect($expectedUrl)
        ->toBeString()
        ->toContain('/admin/planning-weeks/')
        ->toContain('/accommodation')
        ->toContain((string) $week->id);
});

test('planning week show page renders for multiple weeks without route errors', function () {
    // Scoped PBT approach: Test multiple planning week IDs to ensure
    // the route fix works consistently across different planning weeks
    //
    // ON UNFIXED CODE: Each iteration will fail with RouteNotFoundException
    // ON FIXED CODE: All iterations pass

    $admin = User::factory()->admin()->create();
    $weeks = PlanningWeek::factory()->count(3)->create();

    foreach ($weeks as $week) {
        // Each visit will fail on unfixed code with route exception
        $response = $this->actingAs($admin)
            ->get(route('admin.planning-weeks.show', $week));

        $response->assertStatus(200);

        // Verify each page contains valid accommodation link
        $expectedUrl = route('admin.accommodation.edit', $week);
        $response->assertSee($expectedUrl, false);

        expect($expectedUrl)
            ->toContain('/admin/planning-weeks/')
            ->toContain((string) $week->id);
    }
});
