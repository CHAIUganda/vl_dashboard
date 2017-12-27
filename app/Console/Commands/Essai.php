<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;


use EID\Mongo;


class Essai extends Command
{
    /*
    Initial api pick - year(y) and month(m)  - when sample was created
    New samples today or results released today - today(t)
    */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'essai:run {--t|today} {--m|month=} {--y|year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from the Viral Load 2 API';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '2024M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));
        //
        //$this->comment($this->_get('facilities'));
        //$facilities = $this->_get('facilities');
        //$this->mongo->api_facilities->drop();
        //$this->mongo->api_facilities->batchInsert(json_decode($facilities));
        // $samples = $this->_get('samples');
        // $this->mongo->api_samples->drop();
        // $this->mongo->api_samples->batchInsert(json_decode($samples));

        // $this->comment("today is ".$this->option('today'));
        // $this->comment("month is ".$this->option('month'));
        // $this->comment("year is ".$this->argument('year'));

        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        $this->mongo->dashboard_data_refined->drop();
       
    }

    private function _loadHubs(){
        $this->mongo->hubs->drop();
        $res=LiveData::getHubs();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->hub];
            $this->mongo->hubs->insert($data);
        }
    }

    private function _get($resouce){
        $api = env('API')."/api/$resouce/";
        $api_key = env('API_KEY');
        $results = exec("curl -X GET $api -H 'Authorization: Token $api_key'");
        return $results;
    }




}
