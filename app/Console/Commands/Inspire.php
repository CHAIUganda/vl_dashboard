<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use EID\LiveData;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire';

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

        $this->rejections();
    }

     private function rejections(){
      $rejections = LiveData::getRejections();
      $rejections_array=[];
      foreach ($rejections as $key => $value) {
         
          $id=$value->id;
          $appendix=$value->appendix;
          $rejections_array[$id]=$appendix;
      }

      return $rejections_array;
    }
}
