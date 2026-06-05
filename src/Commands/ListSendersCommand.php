<?php

namespace Aghfatehi\Msegat\Commands;

use Aghfatehi\Msegat\Facades\Msegat;
use Illuminate\Console\Command;

class ListSendersCommand extends Command
{
    protected $signature = 'msegat:senders';

    protected $description = 'List all registered Msegat senders';

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
