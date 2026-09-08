<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Order;
use App\Models\LoyaltyChallengeProgress;
use App\Models\LoyaltyChallengeAward;

echo "--- CUSTOMERS ---\n";
foreach (Customer::all() as $c) {
    echo "ID: {$c->id}, Name: {$c->name}, Points: {$c->points}\n";
}

echo "--- ORDERS ---\n";
foreach (Order::all() as $o) {
    echo "ID: {$o->id}, Customer ID: " . ($o->customer_id ?: 'NULL') . ", Status: {$o->status}, Total: {$o->total_order}, Created: " . ($o->created_at ? $o->created_at->toIso8601String() : 'NULL') . "\n";
}

echo "--- PROGRESS ---\n";
foreach (LoyaltyChallengeProgress::all() as $p) {
    echo "ID: {$p->id}, Challenge: {$p->loyalty_challenge_id}, Customer: {$p->customer_id}, Value: {$p->current_value}, Completed: {$p->completed_at}, Rewarded: {$p->rewarded_at}\n";
}

echo "--- AWARDS ---\n";
foreach (LoyaltyChallengeAward::all() as $a) {
    echo "ID: {$a->id}, Customer: {$a->customer_id}, Challenge: {$a->loyalty_challenge_id}, Points: {$a->points_awarded}\n";
}
