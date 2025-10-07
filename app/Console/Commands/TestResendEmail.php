<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestResendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email via Resend';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        Mail::raw('This is a test email from CounselWise using Resend!', function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email - Resend Integration');
        });

        $this->info("Test email sent to {$email}");
        $this->info('Check your inbox (and spam folder) for the test email.');

        return 0;
    }
}
