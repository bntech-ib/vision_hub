<?php

require_once 'vendor/autoload.php';

use App\Models\SupportOption;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get support options
$supportOptions = SupportOption::active()
    ->orderBy('sort_order')
    ->get();

echo "Found " . $supportOptions->count() . " support options\n";

foreach ($supportOptions as $option) {
    echo "ID: " . $option->id . "\n";
    echo "Title: " . $option->title . "\n";
    echo "WhatsApp Link: " . ($option->whatsapp_link ?? 'NULL') . "\n";
    echo "WhatsApp Number: " . ($option->whatsapp_number ?? 'NULL') . "\n";
    echo "WhatsApp Message: " . ($option->whatsapp_message ?? 'NULL') . "\n";
    echo "---\n";
}