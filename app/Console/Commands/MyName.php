<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;


class MyName extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display my name';

    /**
     * Execute the console command.
     *
     * @return mixed
     */


    public function handle($x="p") {
    	//$this->comment("Your name is $x\n") ;

    	//$this->comment(Dashboard::find(90)->valid_results);
        $age=-1;
        
        $arr=[];
        for ($index=1; $index < 100; $index ++) { 
            $from_age = $index - 1;
            $to_age = $index;
            $arr[$index]="$age >=$from_age && $age < $to_age";

            # code...
        }
        var_dump($arr);
    }



}

