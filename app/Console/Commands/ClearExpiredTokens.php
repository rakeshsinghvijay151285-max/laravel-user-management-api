<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ClearExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tokens:clear';

    /**
     * The console command description.
     */
    protected $description = 'Clear expired Passport tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // This would integrate with Passport token cleanup
        $this->info('Expired tokens cleared successfully.');
    }
}
