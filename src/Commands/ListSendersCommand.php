<?php

namespace Aghfatehi\Msegat\Commands;

use Aghfatehi\Msegat\Facades\Msegat;
use Illuminate\Console\Command;

/**
 * Artisan command to list all registered sender names on the Msegat account.
 *
 * Usage: php artisan msegat:senders
 */
class ListSendersCommand extends Command
{
    /** @var string The console command name and signature. */
    protected $signature = 'msegat:senders';

    /** @var string The console command description. */
    protected $description = 'List all registered Msegat senders';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure).
     */
    public function handle(): int
    {
        try {
            $senders = Msegat::getSenders();

            $data = $senders['data'] ?? $senders;

            if (empty($data)) {
                $this->warn('No senders found.');

                return self::SUCCESS;
            }

            $rows = [];
            foreach ($data as $sender) {
                $rows[] = [
                    'name' => $sender['sender'] ?? $sender['name'] ?? 'N/A',
                    'status' => $sender['status'] ?? 'N/A',
                ];
            }

            $this->table(['Sender Name', 'Status'], $rows);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
