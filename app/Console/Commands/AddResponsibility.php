<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use REM\AccessControl\AddPermissions;

class AddResponsibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acl:addResponsibility {permissionValue} {roleName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a responsibility to the acl responsibilities table. Optionally associate it with an acl role.';

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
        $response = (new AddPermissions())->newResponsibility($this->argument('permissionValue'), $this->argument('roleName'));
        echo $response;
        echo "\n\r";
    }
}
