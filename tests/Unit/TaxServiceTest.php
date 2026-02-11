<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TaxService;
use App\Models\Product;
use App\Models\TaxClass;
use App\Models\TaxRate;

class TaxServiceTest extends TestCase
{
    public function test_calculate_with_tax_class()
    {
        // Simulate Product with Tax Class relation loaded
        $taxClass = new TaxClass(['id' => 1, 'name' => 'Standard Rate']);
        $taxRate = new TaxRate(['rate' => 18.00, 'breakdown' => ['cgst' => 9, 'sgst' => 9]]);

        $product = new Product();
        $product->tax_class_id = 1;
        $product->setRelation('taxClass', $taxClass);
        $taxClass->setRelation('rates', collect([$taxRate]));

        $service = new TaxService();
        $result = $service->calculate($product, 100, 2);
        // 100 * 2 = 200. Tax 18% = 36.

        $this->assertEquals(36.00, $result['amount']);
        $this->assertEquals(18.00, $result['rate']);
    }

    public function test_calculate_with_manual_rate()
    {
        $product = new Product();
        $product->tax_class_id = null; // No tax class
        $product->tax_rate = 5.00;

        $service = new TaxService();
        $result = $service->calculate($product, 100, 1);
        // 100 * 1 = 100. Tax 5% = 5.

        $this->assertEquals(5.00, $result['amount']);
        $this->assertEquals(5.00, $result['rate']);
    }

    public function test_calculate_with_no_tax()
    {
        $product = new Product();
        $product->tax_class_id = null;
        $product->tax_rate = 0;

        $service = new TaxService();
        $result = $service->calculate($product, 100, 1);

        $this->assertEquals(0.00, $result['amount']);
        $this->assertEquals(0, $result['rate']);
    }

    public function test_priority_tax_class_over_manual()
    {
        // Both Tax Class (18%) and Manual Rate (5%) present
        $taxClass = new TaxClass(['id' => 1, 'name' => 'Standard Rate']);
        $taxRate = new TaxRate(['rate' => 18.00]);

        $product = new Product();
        $product->tax_class_id = 1;
        $product->tax_rate = 5.00;
        $product->setRelation('taxClass', $taxClass);
        $taxClass->setRelation('rates', collect([$taxRate]));

        $service = new TaxService();
        $result = $service->calculate($product, 100, 1);

        // Should use Tax Class (18%)
        $this->assertEquals(18.00, $result['amount']);
        $this->assertEquals(18.00, $result['rate']);
    }
}
