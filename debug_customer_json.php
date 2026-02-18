<?php

use App\Models\Customer;

$customer = Customer::first();
if (!$customer) {
    echo "No customers found.\n";
    exit;
}

// Ensure dummy middle name
$customer->middle_name = "TestMiddle";
$json = json_encode($customer);

echo "JSON Output: " . $json . "\n";

if (strpos($json, 'middle_name') !== false) {
    echo "SUCCESS: middle_name is present in JSON.\n";
} else {
    echo "FAILURE: middle_name is MISSING from JSON.\n";
}
