<?php

use App\Models\Requirement;
use Tests\TestCase;

uses(TestCase::class);

it('orders by priority using standard SQL instead of MySQL FIELD', function () {
    $sql = Requirement::query()->byPriority()->toSql();

    expect($sql)
        ->toContain('CASE priority')
        ->not->toContain('FIELD(');
});
