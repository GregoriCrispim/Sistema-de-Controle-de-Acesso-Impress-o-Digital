<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;

class ResetDailyVouchers extends Command
{
    protected $signature = 'vouchers:reset';
    protected $description = 'Reset daily meal vouchers (runs at midnight - RF24)';

    public function handle()
    {
        // Vouchers are automatically "reset" because the system checks
        // if a student has eaten TODAY. At midnight, "today" changes,
        // so all students automatically get their daily voucher.

        AuditLog::create([
            'action' => 'daily_voucher_reset',
            'details' => ['message' => 'Daily voucher reset triggered at midnight'],
        ]);

        $this->info('Daily vouchers reset completed. All active students can now receive meals.');
        return Command::SUCCESS;
    }
}
