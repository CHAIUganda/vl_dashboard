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
                    <li id='l1' class='active'>{!! link_to("/","DASH BOARD",['class'=>'hdr']) !!}</li>            
            </ul>
        </div>
    </div>
</div> 

<div class='container'>
    <br>
    <?php //if(!isset($filter_val)) $filter_val="National Metrics, ".$time." thus far" ?>
      
     <?php 
     $year=date('Y');
     $init_duration=[];
     $m=1;
     while($m<=12){
        $init_duration[]="$year-$m";
        $m++;
     }

     ?>


     <div style="overflow: auto;padding:5px;white-space: nowrap;">
        <label class='hdr hdr-grey'> FILTERS:</label>
        <span ng-model='filter_duration' ng-init='filter_duration={!! json_encode($init_duration) !!}'>
            <span class="filter-val ng-cloak">FROM: <% filter_duration[0] %></span>
            <span class="filter-val ng-cloak">TO: <% filter_duration[filter_duration.length-1] %></span>
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

     
     <!-- <label class='hdr val-grey'>
        <label class='filter-val ng-cloak' ng-model='from_date_label' ng-init="from_date_label='~'">
            <% from_date_label %>
        </label> 
        <label class='filter-val ng-cloak' ng-model='to_date_label' ng-init="to_date_label='~'"><% to_date_label %></label>
        <label class='filter-val ng-cloak' ng-model='district_label' ng-init="district_label='~'"><% district_label %></label> 
        <label class='filter-val ng-cloak' ng-model='hub_label' ng-init="hub_label='~'"><% hub_label %></label> 
        <label class='filter-val ng-cloak' ng-model='age_group_label' ng-init="age_group_label='~'"><% age_group_label %></label> 
    </label> --><br>

     <table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
        <tr>
            <td width='20%' sytle="display:inline">
                {!! MyHTML::selectYearMonth(2013,date('Y'),"from_date","",["id"=>"from_date","class"=>"selectpicker"],"FROM DATE") !!}
            </td>
            <td width='20%'>
               {!! MyHTML::selectYearMonth(2013,date('Y'),"to_date","",["id"=>"to_date","class"=>"selectpicker"],"TO DATE") !!}   
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="district" ng-init="district=''" ng-change="filter('district')">
                    <option value="">DISTRICTS</option>
                    <option class="ng-cloak" ng-repeat="(d_nr,dist) in districts_slct" value="<% d_nr %>">
                        <% dist %>
                    </option>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="hub" ng-init="hub=''" ng-change="filter('hub')">
                    <option value="">HUBS</option>
                    <option class="ng-cloak" ng-repeat="(h_nr,hub) in hubs_slct" value="<% h_nr %>">
                        <% hub %>
                    </option>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="age_group" ng-init="age_group=''" ng-change="filter('age_group')">
                    <option value="">AGE GROUP</option>
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
                        <span class="num ng-cloak" ng-model="samples_received" ng-init="samples_received={!! 300000 !!}" >
                            <% samples_received|number %>
                        </span>
                        <span class="desc">samples received</span>
                    </a>
                </li>
                <li id='tb_hd2'>
                    <a href="#tab2" id='tb_lnk2'  ng-click="displaySupressionRate()">
                        <span class="num ng-cloak" ng-model="supression_rate" ng-init="supression_rate={!! 26 !!}">
                            <% supression_rate|number %>%
                        </span>
                        <span class="desc">supression rate</span>
                    </a>
                </li>
                <li id='tb_hd3'>
                    <a href="#tab3" id='tb_lnk3' ng-click="displayRejectionRate()">
                        <span class="num ng-cloak" ng-model="rejection_rate" ng-init="rejection_rate={!! 30 !!}">
                            <% rejection_rate|number %>%
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
                        <p ng-if="facility_numbers.length < 0"> loading facilities</p>
                        <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>Facility</th>
                                    <th>Samples Received</th>
                                    <th>DBS %</th>
                                    <th>Samples Tested</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr ng-repeat="f in facility_numbers | filter:empty('name','no') " >
                                    <td class="ng-cloak" width='80%'><% f.name %></td>
                                    <td class="ng-cloak" width='10%'><% f.initiation_rate %></td>
                                    <td class="ng-cloak" width='10%'><% f.initiation_rate %></td>
                                    <td class="ng-cloak" width='10%'><% f.total_results %></td>

                                </tr>                        
                             </tbody>
                         </table>

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
                                    <th width='8%'>Suppression Rate </th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="f in facility_numbers | filter:compare('facility_name','ne',null)">
                                    <td class="ng-cloak"><% f.facility_name %></td>
                                    <td class="ng-cloak"><% f.total_results %></td>
                                    <td class="ng-cloak"><% f.initiation_rate %></td>
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
                                <tr ng-repeat="f in facility_numbers | filter:compare('initiation_rate','gt',0)">
                                    <td class="ng-cloak"><% f.facility_name %></td>                                    
                                    <td class="ng-cloak"><% f.abs_positives %></td>
                                    <td class="ng-cloak"><% f.initiation_rate %></td>
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
            <font class='addition-metrics figure ng-cloak' ng-init="low_cd4={!! 30 !!}" ng-model='low_cd4'><% low_cd4|number %>%</font><br>
            <font class='addition-metrics desc'>CD4 < 500</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="optionb_plus={!! 47 !!}" ng-model='optionb_plus'><% optionb_plus|number %>%</font><br>
            <font class='addition-metrics desc'>PMTCT/OPTION B+</font>            
        </div>       
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="children_under15={!! 15 !!}" ng-model="children_under15">
                <% children_under15|number %>%
            </font><br>
            <font class='addition-metrics desc'>CHILDREN UNDER 15</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="other_treatment={!! 40 !!}" ng-model="other_treatment">
                <% other_treatment|number %>%
            </font><br>
            <font class='addition-metrics desc'>OTHER</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="blank_on_paper={!! 7 !!}" ng-model="blank_on_paper">
                <% blank_on_paper|number %>%
            </font><br>
            <font class='addition-metrics desc'>BLANK ON PAPER</font>            
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

var samples_received_data=[
     {"key":"DBS","values":[{"x":"Jan","y":1200},{"x":"Feb","y":1290},{"x":"Mar","y":1300},{"x":"Apr","y":998},{"x":"May","y":1118},{"x":"Jun","y":1187},{"x":"Jul","y":1900},{"x":"Aug","y":1200},{"x":"Sep","y":1740},{"x":"Oct","y":1800},{"x":"Nov","y":1700},{"x":"Dec","y":1200}] },
     {"key":"PLASMA","values":[{"x":"Jan","y":500},{"x":"Feb","y":499},{"x":"Mar","y":688},{"x":"Apr","y":490},{"x":"May","y":318},{"x":"Jun","y":347},{"x":"Jul","y":600},{"x":"Aug","y":700},{"x":"Sep","y":830},{"x":"Oct","y":480},{"x":"Nov","y":570},{"x":"Dec","y":600}] }
     ];

var supression_rate_data=[
    {"key":"SUPRESSION RATE","color": "#6D6D6D","values":[["Jan",80],["Feb",78],["Mar",90],["Apr",89],["May",100],["Jun",89],["Jul",97],["Aug",86],["Sep",91],["Oct",98],["Nov",91],["Dec",81]] },
    {"key":"VALID RESULTS","bar":true,"color": "#00786A","values":[["Jan",20],["Feb",18],["Mar",20],["Apr",19],["May",30],["Jun",19],["Jul",27],["Aug",16],["Sep",31],["Oct",18],["Nov",21],["Dec",31]] },
    ];

var rejection_rate_data=[
{"key":"SAMPLE QUALITY","values":[{"x":"Jan","y":20},{"x":"Feb","y":10},{"x":"Mar","y":10},{"x":"Apr","y":0},{"x":"May","y":30},{"x":"Jun","y":20},{"x":"Jul","y":50},{"x":"Aug","y":20},{"x":"Sep","y":40},{"x":"Oct","y":30},{"x":"Nov","y":20},{"x":"Dec","y":30}] },
     {"key":"INCOMPLETE FORM","values":[{"x":"Jan","y":40},{"x":"Feb","y":45},{"x":"Mar","y":60},{"x":"Apr","y":65},{"x":"May","y":50},{"x":"Jun","y":55},{"x":"Jul","y":40},{"x":"Aug","y":30},{"x":"Sep","y":30},{"x":"Oct","y":35},{"x":"Nov","y":40},{"x":"Dec","y":35}] },
     {"key":"ELIGIBILITY","values":[{"x":"Jan","y":40},{"x":"Feb","y":45},{"x":"Mar","y":30},{"x":"Apr","y":35},{"x":"May","y":20},{"x":"Jun","y":25},{"x":"Jul","y":10},{"x":"Aug","y":50},{"x":"Sep","y":30},{"x":"Oct","y":35},{"x":"Nov","y":40},{"x":"Dec","y":35}] }
     
     ];
$(document).ready( function(){
   nv.addGraph( function(){
    var chart = nv.models.multiBarChart().reduceXTicks(false).color(["#00786A","#526CFD"]);
     d3.select('#samples_received svg').datum(samples_received_data).transition().duration(250).call(chart);
    return chart;
   });
})


var months=["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug","Sept","Oct","Nov","Dec"];
var months_init={"1":0, "2":0, "3":0, "4":0, "5":0, "6":0, "7":0,"8":0,"9":0,"10":0,"11":0,"12":0};


/*$(document).ready( function(){
    var ctx = $("#hiv_postive_infants").get(0).getContext("2d");
   // This will get the first returned node in the jQuery collection. 
   var data = {
        labels: months,
        datasets: [count_positives_json] 
    };
    var myLineChart = new Chart(ctx).Line(data);
});
*/
/*$(document).ready(function() {
    setTimeout($('#tab_id').DataTable(),3000);
    
  });*/

$("#time_fltr").change(function(){
    return window.location.assign("/"+this.value);
});



//angular stuff
var app=angular.module('dashboard', ['datatables'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    });
var ctrllers={};

ctrllers.DashController=function($scope,$timeout,$http){

    var districts_json=[];
    var hubs_json={};
    var age_group_json={};
    var facility_json={};
    var results_json={};


    //fetch the data from the json file
    $http.get("{{ asset('/json/data.json') }}").success(function(data) {
        districts_json=data['districts']||{};
        hubs_json=data['hubs']||{};
        age_group_json=data['age_group']||{};
        facility_json=data['facilities']||{};
        results_json=data['results']||{};

        $scope.districts_slct=districts_json;
        $scope.hubs_slct=hubs_json;
        $scope.age_group_slct=age_group_json;
        //$scope.facility_numbers=facility_json;
    });

    $scope.filter=function(mode){
        switch(mode){
            case "district":
            $scope.filter_districts[$scope.district]=districts_json[$scope.district];
            $scope.district="";
            break;

            case "hub":
            $scope.filter_hubs[$scope.hub]=hubs_json[$scope.hub];
            $scope.hub="";
            break;

            case "age_group":
            $scope.filter_age_group[$scope.age_group]=age_group_json[$scope.age_group];
            $scope.age_group="";
            break;
        }

    }

    
   

    //data for filterin

    //$scope.filter_districts=[];

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
                if(status=='no'){ return false; } else { return true };
                    default :  if(status=='no'){ return true; } else { return false };
                }
        }
           
    };

    $scope.displayRejectionRate=function(){
        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().reduceXTicks(false).stacked(true).color(["#526CFD","#B1DEDA","#009688"]);
            d3.select('#rejection_rate svg').datum(rejection_rate_data).transition().duration(500).call(chart);
            return chart;
        });
    };

    $scope.displaySupressionRate=function(){
        nv.addGraph(function() {
            var chart = nv.models.linePlusBarChart()
                        .margin({right: 60,})
                        .x(function(d,i) { return i })
                        .y(function(d,i) {return d[1] }).focusEnable(false);

            chart.xAxis.tickFormat(function(d) {
                return supression_rate_data[0].values[d] && supression_rate_data[0].values[d][0] || " ";
            });
            //chart.reduceXTicks(false);

            chart.bars.forceY([0,40]);
            chart.lines.forceY([0,100]);
            d3.select('#supression_rate svg').datum(supression_rate_data).transition().duration(9500).call(chart);
            return chart;
        });
    }

    $scope.facility_filter=function(){
        if($scope.district!="all"){
            if($scope.care_level!="all"){
                $scope.facility_numbers=$scope.filteredfcltys({"district_id":$scope.district,"level_id":$scope.care_level});
            }else{
                $scope.facility_numbers=$scope.filteredfcltys({"district_id":$scope.district});
            }
        }else if ($scope.region!="all"){
            if($scope.care_level!="all"){
                $scope.facility_numbers=$scope.filteredfcltys({"region_id":$scope.region,"level_id":$scope.care_level});
            }else{
                $scope.facility_numbers=$scope.filteredfcltys({"region_id":$scope.region});
            }
        }else if($scope.care_level!="all"){
            $scope.facility_numbers=$scope.filteredfcltys({"level_id":$scope.care_level});
        }else{
            $scope.facility_numbers=$scope.facility_numbers_init;    
        }       
    };
/*
    $scope.filteredfcltys=function(options){
        var ret={};
        for (var i in $scope.facility_numbers_init){
            var arr=$scope.facility_numbers_init[i];
            var no_match=0;
            for(var j in options){
                if((options[j] != arr[j])){
                    no_match=1;
                }
            }            
            if(no_match==0){
                ret[i]=arr;
            }
        };
        return ret;
    };

    $scope.filter=function(filterer){
        if(filterer=='region'){
            $scope.district="all";
            if($scope.region=="all"){
                $scope.districts_slct=districts_json;
            }else{
               $scope.districts_slct=reg_districts[$scope.region]; 
           }            
        }
        
    };
    */





};

app.controller(ctrllers);
</script>
</html>
