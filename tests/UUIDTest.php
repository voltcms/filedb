<?php

use \PHPUnit\Framework\TestCase;
use \VoltCMS\FileDB\UUID;

class UUIDTest extends TestCase
{

    public function testGenerateReturnsValidV4UuidAndUniqueValues(): void
    {
        $uuid = UUID::generate();
        $this->assertNotEmpty($uuid);
        $this->assertTrue(strlen($uuid) === 36);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);

        $parts = explode('-', $uuid);
        $this->assertCount(5, $parts);
        $this->assertSame('4', strtolower($parts[2][0]));
        $this->assertContains(strtolower($parts[3][0]), ['8', '9', 'a', 'b']);

        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            $generated = UUID::generate();
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $generated);
            $uuids[] = $generated;
        }
        $this->assertCount(100, array_unique($uuids));
    }

}