<?php

namespace Tests\Unit;

use App\Services\Facturador\RetentionAdditionalInfoService;
use PHPUnit\Framework\TestCase;

class RetentionAdditionalInfoServiceTest extends TestCase
{
    public function test_usd_retention_base_and_amount_are_displayed_in_pen(): void
    {
        $service = new RetentionAdditionalInfoService();

        $text = $service->build('USD', 11977.66, 3, 359.33, 11618.33, 3.478);

        $this->assertSame(
            "Base imponible retencion: PEN 41,658.30\n" .
            "Porcentaje retencion: 3.00%\n" .
            "Monto retencion: PEN 1,249.75\n" .
            "Monto neto pendiente de pago: USD 11,618.33",
            $text
        );
        $this->assertStringNotContainsString('Informacion Retencion:', $text);
        $this->assertStringNotContainsString('Codigo retencion:', $text);
    }
}
