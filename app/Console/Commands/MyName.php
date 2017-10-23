<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\Mongo;


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


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->mongo=Mongo::connect();
    }

    public function handle($x="p") {
    	//$this->comment("Your name is $x\n") ;

    	//$this->comment(Dashboard::find(90)->valid_results);

        
        $turnAroundTimeInMonths=3;
        for ($month=0; $month < $turnAroundTimeInMonths; $month++) { 
            $turnAroundYear=intval(date("Y",strtotime("-$month month")));
            $turnAroundMonth=intval(date("m",strtotime("-$month month")));

            echo "Year: $turnAroundYear";
            echo " Month: $turnAroundMonth\n";
        }

        $sample_instance = $this->mongo->dashboard_new_backend->findOne(array('sample_id' => 378));
        //var_dump($sample_instance);
        $indentifier=(string)$sample_instance['_id'];
        //var_dump($indentifier);
        $options=[];
        $options['justOne']=false;
        $result=$this->mongo->dashboard_new_backend->remove(array('sample_id' => 378), $options);
       var_dump($result);
    }

  private function removeSample($numberSampleID){
    $options=[];
    $options['justOne']=false;
    $result=$this->mongo->dashboard_new_backend->remove(array('sample_id' => $numberSampleID), $options);
    return $result['n'];
  }
  

}

