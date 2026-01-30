<?php

use Database\Seeders\CentralAdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Container\Container;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Bootstrapped application.\n";

$seeder = new DatabaseSeeder();
$seeder->setContainer($app);
// Mock command object to avoid errors if seeder uses command I/O
$seeder->setCommand(new class extends \Illuminate\Console\Command {
    public function getOutput() {
        return new class extends \Illuminate\Console\OutputStyle {
            public function __construct() {}
            public function writeln(iterable|string $messages, int $type = self::OUTPUT_NORMAL): void { echo (is_array($messages) ? implode("\n", $messages) : $messages) . "\n"; }
        };
    }
});

echo "Running DatabaseSeeder...\n";
try {
    $seeder->__invoke();
    echo "DatabaseSeeder completed successfully.\n";
} catch (Throwable $e) {
    echo "Caught Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . substr($e->getTraceAsString(), 0, 1000) . "\n...\n";
}
