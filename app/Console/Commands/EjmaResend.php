<?php

namespace App\Console\Commands;

use App\Mail\EjmaCalculationReport;
use App\Models\EjmaCalculation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EjmaResend extends Command
{
    protected $signature = 'ejma:resend {reference : Calculation reference, e.g. EJ-2026-0042}';

    protected $description = 'Resend the EJMA calculation report email (with PDF) for a stored calculation';

    public function handle(): int
    {
        $calculation = EjmaCalculation::where('reference', $this->argument('reference'))->first();

        if (! $calculation) {
            $this->error('No calculation found with reference '.$this->argument('reference').'.');

            return self::FAILURE;
        }

        Mail::to(config('mail.contact_to'))->send(new EjmaCalculationReport($calculation));
        $calculation->forceFill(['emailed_at' => now()])->save();

        $this->info("Report for {$calculation->reference} sent.");

        return self::SUCCESS;
    }
}
