<?php

namespace Tests\Unit\Enums;

use App\Enums\StatusLayanan;
use PHPUnit\Framework\TestCase;

class StatusLayananTest extends TestCase
{
    public function test_buka_label(): void
    {
        $this->assertEquals('Buka', StatusLayanan::BUKA->getLabel());
    }

    public function test_libur_label(): void
    {
        $this->assertEquals('Libur', StatusLayanan::LIBUR->getLabel());
    }

    public function test_buka_color_is_success(): void
    {
        $this->assertEquals('success', StatusLayanan::BUKA->getColor());
    }

    public function test_libur_color_is_danger(): void
    {
        $this->assertEquals('danger', StatusLayanan::LIBUR->getColor());
    }

    public function test_from_string(): void
    {
        $this->assertEquals(StatusLayanan::BUKA, StatusLayanan::from('BUKA'));
        $this->assertEquals(StatusLayanan::LIBUR, StatusLayanan::from('LIBUR'));
    }

    public function test_all_cases(): void
    {
        $this->assertCount(2, StatusLayanan::cases());
    }
}
