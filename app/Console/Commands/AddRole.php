<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use REM\AccessControl\AddPermissions;

class AddRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acl:addRole {permissionValue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a role to the acl roles database table';

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
        $response = (new AddPermissions())->newRole($this->argument('permissionValue'));
        echo $response;
        echo "\n\r";
    }
}
