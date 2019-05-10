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
    protected $signature = 'inspire:run';

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
        /*
        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
        $new_directory='hiv_drug_resistance/';
        $directory_path="./docs/".$new_directory;
        if (!is_dir($directory_path)) {
            mkdir($directory_path, 0777, true);
        }

        //======
        $sample_id="019759/1810";
        $sample_id=str_replace("/", "_", $sample_id);
        $file_url=$directory_path.$sample_id.".json";
        $sample_result_file = fopen($file_url, "w") or die("Unable to open file!");
        $result_string = "";
        fwrite($sample_result_file, $result_string);
        
        fclose($sample_result_file);
        */
        
        
        
    }

    
        
}
