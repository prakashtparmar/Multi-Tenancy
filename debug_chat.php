<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Chat\ChatServices;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock Request
$user = User::find(2); // Assume User 2 (Prakash Parmar), or logic might need specific user
Auth::login($user);

echo "Testing as User: " . $user->name . " (ID: " . $user->id . ")\n";

$service = new ChatServices();
$response = $service->getUser([]);

$data = $response->getData(true);

echo "Total Users/Groups Found: " . count($data['data']) . "\n";
foreach ($data['data'] as $item) {
    echo " - [Score: " . ($item['sort_score'] ?? 'N/A') . "] " . $item['name'] . " (" . ($item['last_message_by'] ?? 'No Msg') . ") - Online: " . ($item['is_online'] ?? 'No') . "\n";
}
