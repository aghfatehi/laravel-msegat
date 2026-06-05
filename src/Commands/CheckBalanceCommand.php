<?php

namespace Aghfatehi\Msegat\Commands;

use Aghfatehi\Msegat\Facades\Msegat;
use Illuminate\Console\Command;

/**
 * Artisan command to check the Msegat account balance.
 *
 * Usage: php artisan msegat:balance
 */
class CheckBalanceCommand extends Command
{
    /** @var string The console command name and signature. */
    protected $signature = 'msegat:balance';

    /** @var string The console command description. */
    protected $description = 'Check Msegat account balance';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure).
     */
    public function handle(): int
    {
        try {
            $balance = Msegat::getBalance();

            if ($balance->successful) {
                $this->info("Msegat balance: {$balance->balance} SMS credits");

                return self::SUCCESS;
            }

            $this->error('Failed to retrieve Msegat balance.');

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
