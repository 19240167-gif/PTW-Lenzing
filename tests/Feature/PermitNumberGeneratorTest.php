<?php

namespace Tests\Feature;

use App\Services\PermitNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermitNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function generates_correctly_formatted_permit_number(): void
    {
        $generator = app(PermitNumberGenerator::class);
        $number = $generator->next("2026");

        $this->assertMatchesRegularExpression('/^PTW-2026-\d{6}$/', $number);
    }

    /** @test */
    public function generates_sequential_numbers(): void
    {
        $generator = app(PermitNumberGenerator::class);

        $first  = $generator->next("2026");
        $second = $generator->next("2026");
        $third  = $generator->next("2026");

        $this->assertEquals("PTW-2026-000001", $first);
        $this->assertEquals("PTW-2026-000002", $second);
        $this->assertEquals("PTW-2026-000003", $third);
    }

    /** @test */
    public function different_scopes_have_independent_sequences(): void
    {
        $generator = app(PermitNumberGenerator::class);

        $y2026 = $generator->next("2026");
        $y2027 = $generator->next("2027");

        $this->assertEquals("PTW-2026-000001", $y2026);
        $this->assertEquals("PTW-2027-000001", $y2027);
    }
}
