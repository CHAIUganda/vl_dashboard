@extends('layout')

@section('content')
    <style type="text/css">
    .nv-point {
        stroke-opacity: 1!important;
        stroke-width: 5px!important;
        fill-opacity: 1!important;
    }
    /** hides the search button on the current_regimen tab*/
    #current_regimen_table_filter{
        visibility: hidden;
    }

    /** hides text on table*/
    
    #time_on_treatment_column::first-letter{
        color: white;
    }
    </style>
    <script>
        
        $(document).ready(function() {
             

            $("#exportButton").click(function(){
                $("#samples_received_table").tableToCSV();
            });
        } );

        
    </script>

    
    <?php //if(!isset($filter_val)) $filter_val="National Metrics, ".$time." thus far" ?>
     <!--       
      <span style="font-size:20px">eid</span>
      <span style="font-size:20px">scd</span>
      <span style="font-size:20px">vl</span> -->
     <?php

    function latestNMonths($n=12){
        $ret=[];
        $m=date('m');
        $y=date('Y');
        for($i=1;$i<=$n;$i++){
            if($m==0){
                $m=12;
                $y--;
            }
            array_unshift($ret, $y.str_pad($m, 2,0, STR_PAD_LEFT));
            $m--;
        }
        return $ret;
    }

    function yearByMonths($from_year=1900,$from_month=1,$to_year="",$to_month=""){
        if(empty($to_year)) $to_year=date("Y");
        if(empty($to_month)) $to_month=date("m");
        $ret=[];
        $i=$from_year;
        while($i<=$to_year){
            $stat=($i==$from_year)?$from_month:1;
            $end=($i==$to_year)?$to_month:12;
            $j=$stat;
            while($j<=$end){
                $ret[$i][]=str_pad($j, 2,0, STR_PAD_LEFT);
                $j++;   
            } 
            $i++; 
        }
        return $ret;
    }

    //echo "<br><br><br>xxx".$sample_data;

     //$start_year=2011,$start_month=1;
    $init_duration=latestNMonths(12);  
    $months_by_years=yearByMonths(2014,8); 
     //krsort($months_by_years);
     ?>
     
    <div>
        <span style="font-size: 10px; color: #F44336;">
        
    </span>
    </div>
   
   
    

   

     @include('sections._filters')
     <span ng-model="loading" ng-init="loading=true"></span>
     <div ng-show="loading" style="text-align: center;padding:10px;"> <img src="{{ asset('/images/loading.gif') }}" height="20" width="20"> processing</div>
     <br>
     <label class='hdr hdr-grey'> KEY METRICS</label>
     <br>
     <div class="tabss tabs-style-flip">
        <nav>
            <ul>
                <li id='tb_hd1'>
                    <a href="#tab1" id='tb_lnk1' ng-click="displaySamplesRecieved()">
                        <span class="num ng-cloak" ng-model="samples_received" ng-init="samples_received=0">
                            <% samples_received|number %>
                        </span>
                        <span class="desc">samples received</span>
                    </a>
                </li>
                <li id='tb_hd2'>
                    <a href="#tab2" id='tb_lnk2'  ng-click="displaySupressionRate()">
                        <span ng-model="valid_results" ng-init="valid_results=0"></span>
                        <span class="num ng-cloak" ng-model="suppressed" ng-init="suppressed=0">
                            <% ((suppressed/valid_results)*100) |number:1 %>%
                        </span>
                        <span class="desc">suppression rate</span>
                    </a>
                </li>
                <li id='tb_hd3'>
                    <a href="#tab3" id='tb_lnk3' ng-click="displayRejectionRate()">
                        <span class="num ng-cloak" ng-model="rejected_samples" ng-init="rejected_samples=0">
                            <% ((rejected_samples/samples_received)*100)|number:1 %>%
                        </span>
                        <span class="desc">rejection rate</span>
                    </a>
                </li>
                <li id='tb_hd4'>
                    <a href="#tab4" id='tb_lnk4' ng-click="displayRegimenGroups()">
                        <span class="num ng-cloak" >
                           <% ((line_numbers[1]/samples_received)*100)|number:1 %>% <span style="font-size:10px">on 1st line</span>                           
                        </span>
                        <span class="desc">current regimen</span>
                    </a>
                </li>
                <li id='tb_hd5'>
                    <a href="#tab5" id='tb_lnk5' ng-click="displayRegimenGroups()">
                        <span style="font-size:10px">Reg All</span> 
                        <span class="desc">All Regimens</span>
                    </a>
                </li>
                <li id='tb_hd6'>

                    <a href="#tab6" id='tb_lnk6'>
                        <span style="font-size:10px">Results Printing Stats</span> 
                        <span class="desc">Results Printing Statistics</span>

                    </a>
                </li>
            </ul>
        </nav>
        <div class="content-wrap">
            <section id="tab1"> @include('sections._samples_received') </section>
            <section id="tab2"> @include('sections._suppression_rate') </section>
            <section id="tab3"> @include('sections._rejections')</section>
            <section id="tab4"> @include('sections._current_regimen')</section>
            <section id="tab5"> @include('sections._all_regimens')</section>
            <section id="tab6"> @include('sections._results_printing_statistics')</section>
            
        </div><!-- /content -->
    </div><!-- /tabs -->
    <br>
        <label class='hdr hdr-grey'> SUMMARY OF KEY INDICATORS</label>
        @include('sections._viral_load_indicators')
    <br>
    
    <label class='hdr hdr-grey'> TREATMENT INDICATION (as indicated on the form)</label>
    <div class='addition-metrics'> @include('sections._treatment_indication') </div>
    <br>
    <script src=" {{ asset('js/cbpFWTabs.js') }} "></script>
    
    <script type="text/javascript" src=" {{ asset('js/live.js') }} "></script>
    
    <script>
    $('#dashboard').addClass('active');
    (function() {
        [].slice.call( document.querySelectorAll( '.tabss' ) ).forEach( function( el ) {
            new CBPFWTabs( el );
        });
    })();
    </script>

@endsection()

