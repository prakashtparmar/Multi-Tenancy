<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TaxClass;

class TaxService
{
    /**
     * Calculate tax details for a given product and price.
     * 
     * Priority:
     * 1. Tax Class (if assigned to product)
     * 2. Manual Tax Rate (if defined on product)
     * 3. Default (0)
     * 
     * @param Product $product
     * @param float $price
     * @param float $quantity
     * @return array{amount: float, rate: float, breakdown: array}
     */
    public function calculate(Product $product, float $price, float $quantity): array
    {
        $taxRate = 0;
        $taxAmount = 0;
        $breakdown = [];

        // 1. Check for Tax Class
        if ($product->tax_class_id && $product->taxClass) {
            // Get the first applicable rate (Logic can be expanded for zones later)
            // For now, we take the first rate or a default if multiple exist (assuming 1-1 for now based on seeder)
            $rateObj = $product->taxClass->rates->first();

            if ($rateObj) {
                $taxRate = (float) $rateObj->rate;
                $breakdown = $rateObj->breakdown ?? [];
            }
        }
        // 2. Fallback to Manual Rate
        elseif ($product->tax_rate > 0) {
            $taxRate = (float) $product->tax_rate;
        }

        // Calculate Tax Amount
        if ($taxRate > 0) {
            $totalPrice = $price * $quantity;
            $taxAmount = $totalPrice * ($taxRate / 100);
        }

        return [
            'amount' => round($taxAmount, 2),
            'rate' => $taxRate,
            'breakdown' => $breakdown,
        ];
    }
}
