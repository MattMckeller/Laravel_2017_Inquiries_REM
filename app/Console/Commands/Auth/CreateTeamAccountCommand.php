<?php

namespace App\Console\Commands\Auth;

use App\Models\Auth\Team\UserAccount;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Inquiries\Inquiry;
use Illuminate\Console\Command;
use REM\AccessControl\AddPermissions;

class CreateTeamAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:teamAccount {userName} {firstName} {lastName} {emailAddress} {webServer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick access to creating a users account without actually having to be logged in or active in the application.';

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
        $TeamAuth = \Illuminate\Support\Facades\App::make('TeamAuth');
        $userAccount = new UserAccount();
        $userAccount->username = $this->argument('userName');
        $userAccount->webserver = $this->argument('webServer');
        $userAccount->activated = 1;

        $userAccountInformation = new UserAccountInformation();
        $userAccountInformation->first_name = $this->argument('firstName');
        $userAccountInformation->last_name = $this->argument('lastName');
        $userAccountInformation->email_address = $this->argument('emailAddress');

        $response = $TeamAuth->createAccount(
            $userAccount,
            $userAccountInformation
        );
        if($response === false){
            echo "An error has occurred!".PHP_EOL;
        }else{
            echo "Success! The user's Authentication word is $response->word.".PHP_EOL;
        }
    }
}
