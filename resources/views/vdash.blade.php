<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/jquery.dataTables.css') }}" rel="stylesheet">    
    <link href="{{ asset('/css/jquery-ui.css')}}" rel="stylesheet" >

     <link href="{{ asset('/css/nv.d3.min.css') }}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="{{ asset('/css/demo.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabs.css') }} " />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabstyles.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/angular-datatables.css') }}" />
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"> -->
    <link href="{{ asset('/css/dash.css') }}" rel="stylesheet">
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />

    
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript" ></script>

    
    <script src="{{ asset('/js/jquery-ui.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/twitter-bootstrap-3.3/js/bootstrap.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/angular.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular-route.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/angular-datatables.min.js') }}" type="text/javascript"></script>


   <script src="{{ asset('/js/modernizr.custom.js') }}"></script>

    
    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>
   
    <script src="{{ asset('/js/d3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('/js/nv.d3.min.js') }}"></script>
    
    <!--script src="{{ asset('/js/jquery-1.11.1.min.js') }}" type="text/javascript"></script -->
    <script src="{{ asset('/js/jquery.tabletoCSV.js')}}" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular-sanitize.min.js"></script>
<script src="{{ asset('/js/ng-csv.min.js') }}"></script>

<!-- <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<script src="{{ asset('/js/angular-datatables.buttons.min.js') }}"></script> -->
    <!-- data-->
    
    
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
</head>

<body ng-app="dashboard" ng-controller="DashController">
<div class="navbar-custom navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header"> 
            <!-- <img src="{{ asset('/images/icon.png') }}" height="20" width="20"> -->
            <a class="navbar-brand" href="/" style="font-weight:800px;color:#FFF"> UGANDA VIRAL LOAD</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                    <li id='l1' class='active'>{!! link_to("/","DASHBOARD",['class'=>'hdr']) !!}</li>         
                    <li id='l2' >{!! link_to("/results","RESULTS",['class'=>'hdr']) !!}</li>    
            </ul>
        </div>
    </div>
</div> 

<div class='container'>
    <br>
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

               <!--  <li id='tb_hd5'>
                    <a href="#tab5" id='tb_lnk5' ng-click="displayRegimenTime()">
                        <span class="num ng-cloak">
                            RD                          
                        </span>
                        <span class="desc">regimen durations</span>
                    </a>
                </li> -->
            </ul>
        </nav>
        <div class="content-wrap">
            <section id="tab1"> @include('sections._samples_received') </section>
            <section id="tab2"> @include('sections._suppression_rate') </section>
            <section id="tab3"> @include('sections._rejections')</section>
            <section id="tab4"> @include('sections._current_regimen')</section>
            <!-- <section id="tab4"> @include('sections._regimen_groups')</section>
            <section id="tab5"> @include('sections._regimen_time')</section> -->
        </div><!-- /content -->
    </div><!-- /tabs -->
    <br>
        <label class='hdr hdr-grey'> SUMMARY OF KEY INDICATORS</label>
        @include('sections._viral_load_indicators')
    <br>
    
    <label class='hdr hdr-grey'> TREATMENT INDICATION (as indicated on the form)</label>
    <div class='addition-metrics'> @include('sections._treatment_indication') </div>
    <br>

    
</div>
<script src=" {{ asset('js/cbpFWTabs.js') }} "></script>
<script>
(function() {
    [].slice.call( document.querySelectorAll( '.tabss' ) ).forEach( function( el ) {
        new CBPFWTabs( el );
    });
})();
</script>


</body>
<!-- <script src="//code.angularjs.org/1.2.20/angular-sanitize.min.js"></script> -->
<script type="text/javascript" src=" {{ asset('js/ng-csv.js') }} "></script>
<script src="https://rawgithub.com/eligrey/FileSaver.js/master/FileSaver.js" type="text/javascript"></script>
<script type="text/javascript" src=" {{ asset('js/live.js') }} "></script>

</html>
