<?php

namespace Tests\Unit;

use App\Services\QrisService;
use PHPUnit\Framework\TestCase;

class QrisServiceTest extends TestCase
{
    private QrisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QrisService();
    }

    public function test_crc16_ccitt_false_calculation(): void
    {
        // Known test vector: the string "123456789" should produce CRC 0x29B1
        // using CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF).
        $this->assertEquals('29B1', $this->service->calculateCrc16('123456789'));
    }

    public function test_convert_to_dynamic_injects_tag54_and_recalculates_crc(): void
    {
        // A minimal synthetic static QRIS string ending with CRC tag "6304" + 4 hex.
        // We use a known short EMVCo-like payload for testing:
        //   "00020101021126"  (Point of Initiation Method = Static)
        //   + "6304" + CRC
        $basePayload = '00020101021126';
        $crcOfBase = $this->service->calculateCrc16($basePayload . '6304');
        $staticQris = $basePayload . '6304' . $crcOfBase;

        $dynamicQris = $this->service->convertToDynamic($staticQris, 25000);

        // After conversion, Tag 54 should be present: "540525000"
        $this->assertStringContainsString('540525000', $dynamicQris);

        // The string should end with "6304" + a 4-char hex checksum
        $this->assertMatchesRegularExpression('/6304[0-9A-F]{4}$/', $dynamicQris);

        // Verify the CRC is correct by recalculating
        $withoutCrc = substr($dynamicQris, 0, -4);
        $expectedCrc = $this->service->calculateCrc16($withoutCrc);
        $actualCrc = substr($dynamicQris, -4);
        $this->assertEquals($expectedCrc, $actualCrc);
    }

    public function test_convert_to_dynamic_handles_float_amount(): void
    {
        $basePayload = '00020101021126';
        $crcOfBase = $this->service->calculateCrc16($basePayload . '6304');
        $staticQris = $basePayload . '6304' . $crcOfBase;

        $dynamicQris = $this->service->convertToDynamic($staticQris, 15000.75);

        // Float should be truncated to integer: 15000
        $this->assertStringContainsString('540515000', $dynamicQris);
    }

    public function test_rejects_zero_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->convertToDynamic('00020101021126630400000000', 0);
    }

    public function test_rejects_negative_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->convertToDynamic('00020101021126630400000000', -500);
    }

    public function test_rejects_too_short_qris(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->convertToDynamic('short', 1000);
    }
}
