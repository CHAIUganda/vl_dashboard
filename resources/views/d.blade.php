<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'VLS')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/jquery.dataTables.css') }}" rel="stylesheet">    
    <link href="{{ asset('/css/jquery-ui.css')}}" rel="stylesheet" >


    <link rel="stylesheet" type="text/css" href="{{ asset('/css/demo.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabs.css') }} " />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabstyles.css') }}" />

        <link href="{{ asset('/css/eid.css') }}" rel="stylesheet">

    <script src="{{ asset('/js/modernizr.custom.js') }}"></script>


    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery-ui.js')}}" type="text/javascript"></script>


    <script src="{{ asset('/js/Chart.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular-route.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/angular-datatables.min.js') }}" type="text/javascript"></script>


    <link href="{{ asset('/css/nv.d3.css') }}" rel="stylesheet" type="text/css">
    <script src="{{ asset('/js/d3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('/js/nv.d3.js') }}"></script>
    <script src="{{ asset('/js/stream_layers.js') }}"></script>

    
</head>

<body ng-app="dashboard" ng-controller="DashController">
<div class="navbar-custom navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/"> <span class='glyphicon glyphicon-home'></span> VIRAL LOAD</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                    <li id='l1' class='active'>{!! link_to("/","DASHBOARD",['class'=>'hdr']) !!}</li>            
            </ul>
        </div>
    </div>
</div> 

<div class='container'>
    <br>
    <?php //if(!isset($filter_val)) $filter_val="National Metrics, ".$time." thus far" ?>
      
     <?php 

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
                $ret[$i][]=$j;
                $j++;   
            } 
            $i++; 
        }
        return $ret;
    }

     //$start_year=2011,$start_month=1;
     $current_year=date('Y');$current_month=date('m');
     $init_duration=[];
     $m=1;
     while($m<=$current_month){
        $init_duration[]="$current_year-$m";
        $m++;
     }
     //print_r(yearByMonths(2011,1));    
     ?>


     <div class="filter-section">
        <label class='hdr hdr-grey'> FILTERS:</label>
        <span ng-model='filter_duration' ng-init='filter_duration={!! json_encode($init_duration) !!}'>
            <span class="filter-val ng-cloak"><% filter_duration[0] %> TO <% filter_duration[filter_duration.length-1] %></span>
        </span>

        <span ng-model='filter_districts' ng-init='filter_districts={}'>
            <span ng-repeat="(d_nr,d_name) in filter_districts"> 
                <span class="filter-val ng-cloak"><% d_name %> (d) <x ng-click='removeTag("district",d_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_hubs' ng-init='filter_hubs={}'>
            <span ng-repeat="(h_nr,h_name) in filter_hubs">
                <span class="filter-val ng-cloak"><% h_name %> (h) <x ng-click='removeTag("hub",h_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_age_group' ng-init='filter_age_group={}'>
            <span ng-repeat="(ag_nr,ag_name) in filter_age_group">
                <span class="filter-val ng-cloak"><% ag_name %> (a) <x ng-click='removeTag("age_group",ag_nr)'>&#120;</x></span> 
            </span>
        </span>
     </div>

     <table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
        <tr>
            <td width='20%'>
                <span style="text-alignment:left;cursor:pointer">FROM DATE</span>

                <!-- {!! MyHTML::selectYearMonth(2013,date('Y'),"from_date","",["id"=>"from_date","class"=>"selectpicker"],"FROM DATE") !!} -->
            </td>
            <td width='20%'>
               {!! MyHTML::selectYearMonth(2013,date('Y'),"to_date","",["id"=>"to_date","class"=>"selectpicker"],"TO DATE") !!}   
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="district" ng-init="district='all'" ng-change="filter('district')">
                    <option value='all'>DISTRICTS</option>
                    <option class="ng-cloak" ng-repeat="(d_nr,dist) in districts_slct" value="<% d_nr %>">
                        <% dist %>
                    </option>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="hub" ng-init="hub='all'" ng-change="filter('hub')">
                    <option value='all'>HUBS</option>
                    <option class="ng-cloak" ng-repeat="(h_nr,hub) in hubs_slct" value="<% h_nr %>">
                        <% hub %>
                    </option>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="age_group" ng-init="age_group='all'" ng-change="filter('age_group')">
                    <option value='all'>AGE GROUP</option>
                    <option class="ng-cloak" ng-repeat="(ag_nr,ag) in age_group_slct" value="<% ag_nr %>">
                        <% ag %>
                    </option>
                </select>
            </td>

             
        </tr>
     </table>
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
            </ul>
        </nav>
        <div class="content-wrap">
            <section id="tab1">
                <div class="row">
                    <div class="col-lg-6">                        
                        <div id="samples_received" class="db-charts">
                            <svg></svg>
                        </div>                        
                    </div>
                   
                    <div class="col-lg-6 facilties-sect " >
                        <span class='dist_faclty_toggle' ng-model="show_fclties" ng-init="show_fclties=false" ng-click="nana()">
                            <span class='active' id='d_shw'>&nbsp;&nbsp;DISTRICTS&nbsp;&nbsp;</span>
                            <span id='f_shw'>&nbsp;&nbsp;FACILITIES &nbsp;&nbsp;</span>
                        </span>
                        <div ng-hide="show_fclties">
                        <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th width='70%'>District</th>
                                    <th width='10%'>Samples Received</th>
                                    <th width='10%'>DBS (%)</th>
                                    <th width='10%'>Samples Tested</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr ng-repeat="d in district_numbers" >
                                    <td class="ng-cloak"><% d.name %></td>
                                    <td class="ng-cloak"><% d.samples_received %></td>
                                    <td class="ng-cloak"><% ((d.dbs_samples/d.samples_received)*100 )| number:1 %> %</td>
                                    <td class="ng-cloak"><% d.total_results %></td>

                                </tr>                        
                             </tbody>
                         </table>
                         </div>
                         <div ng-show="show_fclties">
                         <table datatable="ng" ng-hide="checked" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th width='70%'>Facility</th>
                                    <th width='10%'>Samples Received</th>
                                    <th width='10%'>DBS (%)</th>
                                    <th width='10%'>Samples Tested</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr ng-repeat="f in facility_numbers" >
                                    <td class="ng-cloak"><% f.name %></td>
                                    <td class="ng-cloak"><% f.samples_received %></td>
                                    <td class="ng-cloak"><% ((f.dbs_samples/f.samples_received)*100 )| number:1 %> %</td>
                                    <td class="ng-cloak"><% f.total_results %></td>
                                </tr>                        
                             </tbody>
                         </table>
                         </div>

                    </div>

                </div>
            </section>

            <section id="tab2">
                <div class="row">

                    <div class="col-lg-6">
                       <div id="supression_rate" class="db-charts">
                            <svg></svg>
                        </div>
                    </div>
                   
                    <div class="col-lg-6 facilties-sect" >
                        <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th width='84%'>Facility</th>                                   
                                    <th width='8%'>Valid Results </th>
                                    <th width='8%'>Suppression Rate (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="f in facility_numbers">
                                    <td class="ng-cloak"><% f.name %></td>
                                    <td class="ng-cloak"><% f.valid_results %></td>
                                    <td class="ng-cloak"><% ((f.suppressed/f.valid_results)*100)|number:1 %> %</td>
                                </tr>                        
                             </tbody>
                         </table>
                    </div>
                </div> 
            </section>
            <section id="tab3">
                <div class="row">
                    <div class="col-lg-6">
                        <div id="rejection_rate" class="db-charts">
                            <svg></svg>
                        </div>
                    </div>
                   
                    <div class="col-lg-6 facilties-sect" >
                        <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th width='80%'>Facility</th>
                                    <th width='10%'>Samples Received</th>
                                    <th width='10%'>Rejection Rate (%)</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="f in facility_numbers">
                                    <td class="ng-cloak"><% f.name %></td>                                    
                                    <td class="ng-cloak"><% f.samples_received %></td>
                                    <td class="ng-cloak"><% ((f.suppressed/f.samples_received)*100)|number:1 %> %</td>
                                </tr>                        
                             </tbody>
                         </table>
                    </div>
                </div>                
            </section>
        </div><!-- /content -->
    </div><!-- /tabs -->
    
    <br>
    <label class='hdr hdr-grey'> TREATMENT INDICATION</label>
    <div class='addition-metrics'>
       <div class='row'>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="cd4_less_than_500=0" ng-model='cd4_less_than_500'>
                <% ((cd4_less_than_500/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>CD4 < 500</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="pmtct_option_b_plus=0" ng-model='pmtct_option_b_plus'>
                <% ((pmtct_option_b_plus/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>PMTCT/OPTION B+</font>            
        </div>       
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="children_under_15=0" ng-model="children_under_15">
                <% ((children_under_15/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>CHILDREN UNDER 15</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="other_treatment=0" ng-model="other_treatment">
                <% ((other_treatment/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>OTHER</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="treatment_blank_on_form=0" ng-model="treatment_blank_on_form">
                <% ((treatment_blank_on_form/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>BLANK ON FORM</font>            
        </div>
       </div>
    </div>
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
<?php
/*
national -- #6D6D6D
blue -- #357BB8
green-- #5EA361
yellow -- #F5A623
purple -- #9F82D1

*/
$chart_stuff=[
    "fillColor"=>"rgba(0,0,0, 0)",
    "strokeColor"=>"#6D6D6D",
    "pointColor"=>"#6D6D6D",
    "pointStrokeColor"=>"#fff",
    "pointHighlightFill"=>"#fff",
    "pointHighlightStroke"=> "#6D6D6D"
    ];

$chart_stuff2=[
    "fillColor"=>"#FFFFCC",
    "strokeColor"=>"#FFCC99",
    "pointColor"=>"#FFCC99",
    "pointStrokeColor"=>"#fff",
    "pointHighlightFill"=>"#fff",
    "pointHighlightStroke"=> "#FFCC99"
    ];


$st2= ["Jan"=>2, "Feb"=>2, "Mar"=>3, "Apr"=>6, "May"=>3, "Jun"=>6, "Jul"=>6,"Aug"=>6,"Sept"=>6,"Oct"=>2,"Nov"=>6,"Dec"=>2];
?>

<script type="text/javascript">

function count(json_obj){
    return Object.keys(json_obj).length;
}

//angular stuff
var app=angular.module('dashboard', ['datatables'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    });
var ctrllers={};

ctrllers.DashController=function($scope,$timeout,$http){

    var districts_json={};
    var hubs_json={};
    var age_group_json={};    
    var facilities_json={};   
    var results_json={}; //to hold a big map will all processed data to later on be used in the generalFilter

    var vvvrrr=0;

    /*
    $http.get("http://vl.trailanalytics.com/json/districts/amg299281fmlasd5dc02bd238919260fg6ad261d094zafd9/").success(function(data) {
        for(var i in data){
            console.log(" first piece is "+i+" and "+JSON.stringify(data[i]));
        }

        //Cross-Origin Request Blocked: The Same Origin Policy disallows reading the remote resource at http://vl.trailanalytics.com/json/districts/amg299281fmlasd5dc02bd238919260fg6ad261d094zafd9/. This can be fixed by moving the resource to the same domain or enabling CORS.
    
    })*/

    /*$http.get("{{ asset('/json/hh.json') }}").success(function(data) {
        for(var i in data){
            console.log(" first piece is "+i+" and "+JSON.stringify(data[i]));
        }
    })*/

    $http.get("{{ asset('/json/data.json') }}").success(function(data) {
        districts_json=data['districts']||{};
        hubs_json=data['hubs']||{};
        age_group_json=data['age_group']||{};
        facilities_json=data['facilities']||{};
  

        $scope.districts_slct=districts_json;
        $scope.hubs_slct=hubs_json;
        $scope.age_group_slct=age_group_json;

        var res=data['results']||{};
        for(var i in res){
           var that=res[i];
           var facility_details=facilities_json[that.facility_id];
        
           results_json[i]=that;
           results_json[i].year_month=that.year+"-"+that.month;
           results_json[i].facility_name=facility_details.name;
           results_json[i].hub_id=facility_details.hub_id;
           results_json[i].district_id=facility_details.district_id;
           results_json[i].district_name=districts_json[facility_details.district_id];

        }

       generalFilter(); //call the filter for the first time
    });

    $scope.filter=function(mode){
        switch(mode){
            case "district":
            $scope.filter_districts[$scope.district]=districts_json[$scope.district];
            $scope.district='all';
            
            break;

            case "hub":
            $scope.filter_hubs[$scope.hub]=hubs_json[$scope.hub];
            $scope.hub='all';
            break;

            case "age_group":
            $scope.filter_age_group[$scope.age_group]=age_group_json[$scope.age_group];
            $scope.age_group='all';
            break;
        }

        delete $scope.filter_districts["all"];
        delete $scope.filter_hubs["all"];
        delete $scope.filter_age_group["all"];

        generalFilter(); //filter the results for each required event
    }



    var evaluator=function(that){  
        var d_num=count($scope.filter_districts);
        var h_num=count($scope.filter_hubs);
        var a_num=count($scope.filter_age_group);

        var time_eval=inArray(that.year_month,$scope.filter_duration);
        var dist_eval=$scope.filter_districts.hasOwnProperty(that.district_id);
        var hub_eval=$scope.filter_hubs.hasOwnProperty(that.hub_id);
        var ag_eval=$scope.filter_age_group.hasOwnProperty(that.age_group);

        var eval1=d_num==0&&h_num==0&&a_num==0;     // districts(OFF) and hubs(OFF) and age_groups (OFF)
        var eval2=dist_eval&&h_num==0&&a_num==0;    // districts(ON) and hubs(OFF) and age_groups (OFF)
        var eval3=(dist_eval||hub_eval)&&a_num==0;  // districts(ON) or hubs(ON) and age_groups (OFF)
        var eval4=dist_eval&&h_num==0&&ag_eval;     // districts(ON) and hubs(OFF) and age_groups (ON)
        var eval5=(dist_eval||hub_eval)&&ag_eval;   // districts(ON) or hubs(ON) and age_groups (ON)
        var eval6=d_num==0&&hub_eval&&ag_eval;      // districts(OFF) and hubs(ON) and age_groups (ON)
        var eval7=d_num==0&&hub_eval&&a_num==0;     // districts(OFF) and hubs(ON) and age_groups (OFF)
        var eval8=d_num==0&&h_num==0&&ag_eval;      // districts(OFF) and hubs(OFF) and age_groups (ON)

        if( time_eval && (eval1||eval2||eval3||eval4||eval5||eval6||eval7||eval8)){
            return true;
        }else{
            return false;
        }
    }

    var setKeyIndicators=function(that){
        $scope.samples_received+=that.samples_received;
        $scope.suppressed+=that.suppressed;
        $scope.valid_results+=that.valid_results;
        $scope.rejected_samples+=that.rejected_samples;
    }

    var setOtherIndicators=function(that){
        $scope.cd4_less_than_500+=that.cd4_less_than_500;
        $scope.pmtct_option_b_plus+=that.pmtct_option_b_plus;
        $scope.children_under_15+=that.children_under_15;
        $scope.other_treatment+=that.other_treatment;
        $scope.treatment_blank_on_form+=that.treatment_blank_on_form;  
    }

    var setDataByDuration=function(that){
        var prev_plasma=$scope.samples_received_data.plasma[that.year_month]||0;
        var prev_dbs= $scope.samples_received_data.dbs[that.year_month]||0;
        $scope.samples_received_data.plasma[that.year_month]=prev_plasma+(that.samples_received-that.dbs_samples);
        $scope.samples_received_data.dbs[that.year_month]=prev_dbs+that.dbs_samples;
        
        var prev_sprsd= $scope.suppressed_by_duration[that.year_month]||0;
        $scope.suppressed_by_duration[that.year_month]=prev_sprsd+that.suppressed;
        
        var prev_vld= $scope.valid_res_by_duration[that.year_month]||0;
        $scope.valid_res_by_duration[that.year_month]=prev_vld+that.valid_results;

        rjrctionSetter(that);//for rejection graphs
    }

    var rjrctionSetter=function(that){
        var prev_sq=$scope.rejected_by_duration.sample_quality[that.year_month]||0;
        var prev_eli=$scope.rejected_by_duration.eligibility[that.year_month]||0;
        var prev_inc=$scope.rejected_by_duration.incomplete_form[that.year_month]||0;

        $scope.rejected_by_duration.sample_quality[that.year_month]=prev_sq+that.sample_quality_rejections;
        $scope.rejected_by_duration.eligibility[that.year_month]=prev_eli+that.eligibility_rejections;
        $scope.rejected_by_duration.incomplete_form[that.year_month]=prev_inc+that.incomplete_form_rejections;
    }

    var setDataByFacility=function(that){
        $scope.facility_numbers[that.facility_id]=$scope.facility_numbers[that.facility_id]||{};
        var f_smpls_rvd=$scope.facility_numbers[that.facility_id].samples_received||0;
        var f_vls_rsts=$scope.facility_numbers[that.facility_id].valid_results||0;
        var f_rjctd_smpls=$scope.facility_numbers[that.facility_id].rejected_samples||0;
        var f_sprrsd=$scope.facility_numbers[that.facility_id].suppressed||0;
        var f_dbs_smpls=$scope.facility_numbers[that.facility_id].dbs_samples||0;
        var f_ttl_results=$scope.facility_numbers[that.facility_id].total_results||0;

        $scope.facility_numbers[that.facility_id].samples_received=f_smpls_rvd+that.samples_received;
        $scope.facility_numbers[that.facility_id].valid_results=f_vls_rsts+that.valid_results;
        $scope.facility_numbers[that.facility_id].rejected_samples=f_rjctd_smpls+that.rejected_samples;
        $scope.facility_numbers[that.facility_id].suppressed=f_sprrsd+that.suppressed;
        $scope.facility_numbers[that.facility_id].dbs_samples=f_dbs_smpls+that.dbs_samples;
        $scope.facility_numbers[that.facility_id].total_results=f_ttl_results+that.total_results;
        $scope.facility_numbers[that.facility_id].name=that.facility_name;
    }

    var setDistrictData=function(that){
        $scope.district_numbers[that.district_id]=$scope.district_numbers[that.district_id]||{};

        var d_smpls_rvd=$scope.district_numbers[that.district_id].samples_received||0;
        var d_dbs_smpls=$scope.district_numbers[that.district_id].dbs_samples||0;
        var d_ttl_results=$scope.district_numbers[that.district_id].total_results||0;

        $scope.district_numbers[that.district_id].samples_received=d_smpls_rvd+that.samples_received;
        $scope.district_numbers[that.district_id].dbs_samples=d_dbs_smpls+that.dbs_samples;
        $scope.district_numbers[that.district_id].total_results=d_ttl_results+that.total_results;
        $scope.district_numbers[that.district_id].name=that.district_name;
    }

    var generalFilter=function(){
        $scope.samples_received=0;$scope.suppressed=0;$scope.valid_results=0;$scope.rejected_samples=0;   
        $scope.cd4_less_than_500=0;$scope.pmtct_option_b_plus=0;$scope.children_under_15=0;
        $scope.other_treatment=0;$scope.treatment_blank_on_form=0;        
        $scope.samples_received_data={'plasma':{},'dbs':{}};
        $scope.suppressed_by_duration={};
        $scope.valid_res_by_duration={};
        $scope.rejected_by_duration={'sample_quality':{},'eligibility':{},'incomplete_form':{}};
        $scope.facility_numbers={};
        $scope.district_numbers={};

        for(var i in results_json){
            var that = results_json[i];
            if(evaluator(that)){
                setKeyIndicators(that); //set the values for the key indicators
                setOtherIndicators(that); //set the values for other indicators
                setDataByDuration(that); //set data by duration to be displayed in graphs    
                setDataByFacility(that); //set data by facility to be displayed in tables
                setDistrictData(that); //set data by district to displayed in the table
            }         
        }
        $scope.displaySamplesRecieved();
        $scope.displaySupressionRate();
        $scope.displayRejectionRate();
    };


    $scope.displaySamplesRecieved=function(){       //$scope.samples_received=100000;
        var srd=$scope.samples_received_data;        
        var data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];

        for(var i in srd.dbs){
            data[0].values.push({"x":i,"y":srd.dbs[i]});
            data[1].values.push({"x":i,"y":srd.plasma[i]});            
        }

        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().color(["#00786A","#526CFD"]).reduceXTicks(false);
            d3.select('#samples_received svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };


    $scope.displaySupressionRate=function(){
        var data=[{"key":"SUPRESSION RATE","color": "#6D6D6D","values":[] },
                  {"key":"VALID RESULTS","bar":true,"color": "#00786A","values":[]}];

        for(var i in $scope.valid_res_by_duration){
            var sprsd=$scope.suppressed_by_duration[i]||0;
            var vld=$scope.valid_res_by_duration[i]||0;
            var s_rate=(sprsd/vld)*100;
            s_rate.toPrecision(3);
            data[0].values.push([i,s_rate]);
            data[1].values.push([i,vld]);
        } 
        nv.addGraph(function() {
            var chart = nv.models.linePlusBarChart()
                        .margin({right: 60,})
                        .x(function(d,i) { return i })
                        .y(function(d,i) {return d[1] }).focusEnable(false);

            chart.xAxis.tickFormat(function(d) {
                return data[0].values[d] && data[0].values[d][0] || " ";
            });
            //chart.reduceXTicks(false);
            chart.bars.forceY([0]);
            chart.lines.forceY([0,100]);
            d3.select('#supression_rate svg').datum(data).transition().duration(9500).call(chart);
            return chart;
        });
    }

    $scope.displayRejectionRate=function(){
        var rbd=$scope.rejected_by_duration;
        var data=[{"key":"SAMPLE QUALITY","values":[]},
                  {"key":"INCOMPLETE FORM","values":[] },
                  {"key":"ELIGIBILITY","values":[] }];

        for(var i in rbd.sample_quality){
            var ttl=rbd.sample_quality[i]+rbd.incomplete_form[i]+rbd.eligibility[i];
            var sq_rate=(rbd.sample_quality[i]/ttl)*100;
            var inc_rate=(rbd.incomplete_form[i]/ttl)*100;
            var el_rate=(rbd.eligibility[i]/ttl)*100;
            data[0].values.push({"x":i,"y":sq_rate });
            data[1].values.push({"x":i,"y":inc_rate});
            data[2].values.push({"x":i,"y":el_rate});
        }
        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().reduceXTicks(false).stacked(true).color(["#526CFD","#B1DEDA","#009688"]);
            d3.select('#rejection_rate svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };

    $scope.compare = function(prop,comparator, val){
        return function(item){
            if(comparator=='eq'){
                return item[prop] == val;
            }else if (comparator=='ne'){
               return item[prop] != val;
            }else if (comparator=='gt'){
               return item[prop] > val;
            }else if (comparator=='lt'){
               return item[prop] < val;
            }else if (comparator=='ge'){
               return (item[prop] > val)||(item[prop] == val);
            }else if (comparator=='le'){
               return (item[prop] < val)||(item[prop] == val);
            }else{
                return false;
            }
        }
    };

    $scope.removeTag=function(mode,nr){
        switch(mode){
            case "district": delete $scope.filter_districts[nr];break;
            case "hub": delete $scope.filter_hubs[nr];break;
            case "age_group": delete $scope.filter_age_group[nr];break;
        }
        $scope.filter(mode);
    };

    $scope.empty=function(prop,status){
        return function(item){
            switch(item[prop]) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                case typeof this == "undefined":
                if(status=='no'){ return false; } else { return true; };
                    default :  if(status=='no'){ return true; } else { return false; };
                }
        }
           
    };

    $scope.nana=function(){
        if($scope.show_fclties==true){
            $("#d_shw").attr("class","active");
            $("#f_shw").attr("class","");
            $scope.show_fclties=false;
        }else{
            $("#f_shw").attr("class","active");
            $("#d_shw").attr("class","");
            $scope.show_fclties=true;
        }
    }

    var inArray=function(val,arr){
        var ret=false;
        for(var i in arr){
            if(val==arr[i]) ret=true;
        }
        return ret;
    }

};

app.controller(ctrllers);



/*
-Hide empty filters --- don't show the (~)
-Xes on the filter labels  and make the same background color
-Open sense for the font style
-bug on the graphs when o =n hover for av. positivity --- try with wakiso --HCIV, Hospital, HC III



*/
</script>
</html>
