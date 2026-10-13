<?php

namespace App\Console\Commands;

use App\Mail\CareerApplication as CareerApplicationMail;
use App\Models\CareerApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CareerResend extends Command
{
    protected $signature = 'career:resend {id? : Application ID; omit to send every application not yet emailed}';

    protected $description = 'Email stored job applications (with CV) that failed to send, or resend one by ID';

    public function handle(): int
    {
        $applications = $this->argument('id')
            ? CareerApplication::whereKey($this->argument('id'))->get()
            : CareerApplication::whereNull('emailed_at')->get();

        if ($applications->isEmpty()) {
            $this->info('No applications to send.');

            return self::SUCCESS;
        }

        $failed = false;

        foreach ($applications as $application) {
            try {
                Mail::to(config('mail.career_to'))->send(new CareerApplicationMail($application));
            } catch (\Throwable $e) {
                report($e);
                $this->error("Could not send application #{$application->id}: ".$e->getMessage());
                $failed = true;

                continue;
            }
            $application->forceFill(['emailed_at' => now()])->save();
            $this->info("Application #{$application->id} ({$application->name}, {$application->position}) sent.");
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
