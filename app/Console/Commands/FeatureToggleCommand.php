<?php

namespace App\Console\Commands;

use App\Support\Feature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class FeatureToggleCommand extends Command
{
    protected $signature = 'feature:toggle {key} {--enable} {--disable}';

    protected $description = 'Enable or disable application modules';

    public function handle(): int
    {
        $key = $this->argument('key');
        $modules = array_keys(Feature::all());

        if (! in_array($key, $modules, true)) {
            $this->error("Feature '{$key}' tidak dikenal.");
            $this->info('Feature tersedia: ' . implode(', ', $modules));
            return self::FAILURE;
        }

        $enable = $this->option('enable');
        $disable = $this->option('disable');

        if ($enable && $disable) {
            $this->error('Gunakan hanya salah satu: --enable atau --disable.');
            return self::FAILURE;
        }

        if (! $enable && ! $disable) {
            $current = Feature::enabled($key) ? 'ON' : 'OFF';
            $this->info("Status feature '{$key}': {$current}");
            return self::SUCCESS;
        }

        if (! Schema::hasTable('feature_flags')) {
            $this->error('Tabel feature_flags belum tersedia. Jalankan migrasi terlebih dahulu.');
            return self::FAILURE;
        }

        Feature::set($key, $enable);
        $status = $enable ? 'ON' : 'OFF';
        $this->info("Feature '{$key}' di-set ke {$status}.");

        return self::SUCCESS;
    }
}
