<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetPasswordUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:password-user {userid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the password for a specific user by their ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $user = User::find($this->argument('userid'));
        if (!$user) {
            $this->error("User not found with ID: " . $this->argument('userid'));
            return;
        }
        if ($this->confirm("Are you sure you want to reset the password for user: " . $user->name . " (ID: " . $user->id . ")?")) {
            // Reset password logic here
            $user->password = bcrypt('123456'); // Set a default password or generate a random one
            $user->save();

            $this->info("Password has been reset for user: " . $user->name . " (ID: " . $user->id . ")");
        }
    }
}
