<?php

namespace Aghfatehi\Msegat\Commands;

use Aghfatehi\Msegat\Facades\Msegat;
use Illuminate\Console\Command;

class CheckBalanceCommand extends Command
{
    protected $signature = 'msegat:balance';

    protected $description = 'Check Msegat account balance';

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
