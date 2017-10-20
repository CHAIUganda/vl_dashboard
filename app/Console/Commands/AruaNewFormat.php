<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;

class AruaNewFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arua:onwards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads Arua Data';

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
        echo "---April Onwards--\n";
    }
}
