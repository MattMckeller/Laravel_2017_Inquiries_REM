<?php

namespace App\Console\Commands\Cron;

use App\Models\Inquiries\Inquiry;
use Illuminate\Console\Command;
use REM\AccessControl\AddPermissions;

class ResetAvailableInquiriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:resetAvailableInquiries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looks for inquiries that are past expiration and updates them to be unowned inquiries.';

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
     * @return mixed
     */
    public function handle()
    {
        $inqRepo = \Illuminate\Support\Facades\App::make('InquiryRepository');
        $inqRepo->cleanAvailableInquiries();
    }
}
