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
    	$this->comment("Your name is $x\n") ;

    	$this->comment(Dashboard::find(90)->valid_results);
    }



}

