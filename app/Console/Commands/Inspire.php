<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use EID\LiveData;
use EID\User;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire:run {name} {--oauth_name=} {--oauth_username=} {--oauth_password=} {--oauth_email=}';

    /**
     * The console command description. 
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);



        // or get all as array
        $arguments = $this->argument();

        $options = $this->option();

        var_dump($arguments);
        var_dump($options);

        
         $user = new User();
         $user->name=$options['oauth_name'];
         $user->email=$options['oauth_email'];
         $user->username=$options['oauth_username'];
         $user->setPasswordAttribute($options['oauth_password']);
        
         $user->hub_id=0;
         $user->facility_id=0;
         $user->deactivated=0;
         $user->save();
       }
        
}
