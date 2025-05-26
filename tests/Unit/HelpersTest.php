<?php
namespace Tests\Unit;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_format_currency()
    {
        $this->assertEquals('1.000.000 VND', format_currency(1000000, 'VND'));
    }
}