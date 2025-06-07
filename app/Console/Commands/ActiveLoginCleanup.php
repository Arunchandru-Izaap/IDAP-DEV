<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Employee;
use Carbon\Carbon;

class ActiveLoginCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activeLogin:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired Login';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get users whose last login time is greater than 2 hours from current time
        $usersToUpdate = Employee::where('last_login', '<=', Carbon::now()->subHours(2))->get();

        // Update the is_login_now field to 0 for each user
        foreach ($usersToUpdate as $user) {
            $user->update(['is_login_now' => 0]);
        }

        $this->info('Inactive logins updated successfully.');
        return 0;
    }
}
