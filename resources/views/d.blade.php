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
     $year=date('Y');
     $init_duration=[];
     $m=1;
     while($m<=12){
        $init_duration[]="$year-$m";
        $m++;
     }

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
                {!! MyHTML::selectYearMonth(2013,date('Y'),"from_date","",["id"=>"from_date","class"=>"selectpicker"],"FROM DATE") !!}
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
                        <span class="num ng-cloak" ng-model="suppression_rate" ng-init="suppression_rate=0">
                            <% suppression_rate|number:1 %>%
                        </span>
                        <span class="desc">supression rate</span>
                    </a>
                </li>
                <li id='tb_hd3'>
                    <a href="#tab3" id='tb_lnk3' ng-click="displayRejectionRate()">
                        <span class="num ng-cloak" ng-model="rejection_rate" ng-init="rejection_rate=0">
                            <% rejection_rate|number:1 %>%
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
                                    <td class="ng-cloak"><% +d.name %></td>
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
                                    <td class="ng-cloak"><% +f.name %></td>
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
            <font class='addition-metrics figure ng-cloak' ng-init="cd4_less_than_500=0" ng-model='cd4_less_than_500'><% cd4_less_than_500|number:1 %>%</font><br>
            <font class='addition-metrics desc'>CD4 < 500</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="pmtct_option_b_plus=0" ng-model='pmtct_option_b_plus'><% pmtct_option_b_plus|number:1 %>%</font><br>
            <font class='addition-metrics desc'>PMTCT/OPTION B+</font>            
        </div>       
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="children_under_15=0" ng-model="children_under_15">
                <% children_under_15|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>CHILDREN UNDER 15</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="other_treatment=0" ng-model="other_treatment">
                <% other_treatment|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>OTHER</font>            
        </div>
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="treatment_blank_on_form=0" ng-model="treatment_blank_on_form">
                <% treatment_blank_on_form|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>BLANK ON PAPER</font>            
        </div>
       <!--  
       <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="dbs_samples=0" ng-model="dbs_samples">
                <% dbs_samples|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>dbs_samples</font>  
            <font class='addition-metrics figure ng-cloak' ng-init="plasma=0" ng-model="plasma">
                <% plasma|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>plasma</font>           
        </div> -->
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

$(".xxx").mouseover(function() {
    console.log(" mouser over");
    
    }).mouseout(function() { 
        console.log(" mouser out");
     })


//angular stuff
var app=angular.module('dashboard', ['datatables'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    });
var ctrllers={};

ctrllers.DashController=function($scope,$timeout,$http){

    var districts_json={};
    var district_data_json={};
    var hubs_json={};
    var age_group_json={};
    var facilities_json={};
    var facility_data_json={};
   
    var results_json={};

    var samples_received=0;
    var valid_results=0;
    var suppressed=0;
    var rejected_samples=0;

    var cd4_less_than_500=0;
    var pmtct_option_b_plus=0;
    var children_under_15=0;
    var other_treatment=0;
    var treatment_blank_on_form=0;

    //var samples_received_data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];
    //var samples_received_data=$scope.filter_duration;

    //fetch the data from the json file
    $http.get("{{ asset('/json/data.json') }}").success(function(data) {
        $scope.rights=0;
        $scope.wrongs=0;
        districts_json=data['districts']||{};
        hubs_json=data['hubs']||{};
        age_group_json=data['age_group']||{};
        facilities_json=data['facilities']||{};
        facility_data_json=facilities_json;
        //district_data_json=districts_json;        

        $scope.districts_slct=districts_json;
        $scope.hubs_slct=hubs_json;
        $scope.age_group_slct=age_group_json;

        var res=data['results']||{};
        $scope.samples_received=0
        for(var i in res){
           var that=res[i];
           var facility_id=that.facility_id;
           var facility_details=facilities_json[facility_id];
           var dist_id=facility_details.district_id;
           results_json[i]={
                'year_month':that.year+"-"+that.month,
                'facility_id':facility_id,
                'facility_name':facility_details.name,
                'hub_id':facility_details.hub_id,
                'district_id':dist_id,
                'district_name':districts_json[dist_id],


                'age_group':that.age_group,
                'samples_received':that.samples_received,
                'valid_results':that.valid_results,
                'rejected_samples':that.rejected_samples,
                'suppressed':that.suppressed,
                'dbs_samples':that.dbs_samples,

                'cd4_less_than_500':that.cd4_less_than_500,
                'pmtct_option_b_plus':that.pmtct_option_b_plus,
                'children_under_15':that.children_under_15,
                'other_treatment':that.other_treatment,
                'treatment_blank_on_form':that.treatment_blank_on_form
                };

            var f_obj=facility_data_json[facility_id]||{};

            var f_smpls_rvd=f_obj.samples_received||0;
            var f_vls_rsts=f_obj.valid_results||0;
            var f_rjctd_smpls=f_obj.rejected_samples||0;
            var f_sprrsd=f_obj.suppressed||0;
            var f_dbs_smpls=f_obj.dbs_samples||0;
            var f_ttl_results=f_obj.total_results||0;

            f_obj.samples_received=f_smpls_rvd+that.samples_received;
            f_obj.valid_results=f_vls_rsts+that.valid_results;
            f_obj.rejected_samples=f_rjctd_smpls+that.rejected_samples;
            f_obj.suppressed=f_sprrsd+that.suppressed;
            f_obj.dbs_samples=f_dbs_smpls+that.dbs_samples;
            f_obj.total_results=f_ttl_results+that.total_results;

            if(dist_id!=0){
                 district_data_json[dist_id]=district_data_json[dist_id]||{};
                 var d_smpls_rvd=district_data_json[dist_id].samples_received||0;
                 var d_dbs_smpls=district_data_json[dist_id].dbs_samples||0;
                 var d_ttl_results=district_data_json[dist_id].total_results||0;

                 district_data_json[dist_id].samples_received=d_smpls_rvd+that.samples_received;
                 district_data_json[dist_id].dbs_samples=d_dbs_smpls+that.dbs_samples;
                 district_data_json[dist_id].total_results=d_ttl_results+that.total_results;
                 district_data_json[dist_id].name=districts_json[dist_id];
                 district_data_json[dist_id].id=dist_id;
             }

             samples_received+=that.samples_received;
             suppressed+=that.suppressed;
             valid_results+=that.valid_results;
             rejected_samples+=that.rejected_samples;
             
             cd4_less_than_500+=that.cd4_less_than_500;
             pmtct_option_b_plus+=that.pmtct_option_b_plus;
             children_under_15+=that.children_under_15;
             other_treatment+=that.other_treatment;
             treatment_blank_on_form+=that.treatment_blank_on_form;



             //samples_received_data[0].values.=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];


           
        }





        var samples_received_data=[
     {"key":"DBS","values":[{"x":"Jan","y":1200},{"x":"Feb","y":1290},{"x":"Mar","y":1300},{"x":"Apr","y":998},{"x":"May","y":1118},{"x":"Jun","y":1187},{"x":"Jul","y":1900},{"x":"Aug","y":1200},{"x":"Sep","y":1740},{"x":"Oct","y":1800},{"x":"Nov","y":1700},{"x":"Dec","y":1200}] },
     {"key":"PLASMA","values":[{"x":"Jan","y":500},{"x":"Feb","y":499},{"x":"Mar","y":688},{"x":"Apr","y":490},{"x":"May","y":318},{"x":"Jun","y":347},{"x":"Jul","y":600},{"x":"Aug","y":700},{"x":"Sep","y":830},{"x":"Oct","y":480},{"x":"Nov","y":570},{"x":"Dec","y":600}] }
     ];

        $scope.results_json=results_json;

        $scope.facility_numbers=facility_data_json;
        $scope.district_numbers=district_data_json;

        $scope.samples_received=samples_received;
        $scope.suppression_rate=(suppressed/valid_results)*100;
        $scope.rejection_rate=(rejected_samples/samples_received)*100;

        $scope.cd4_less_than_500=(cd4_less_than_500/samples_received)*100;
        $scope.pmtct_option_b_plus=(pmtct_option_b_plus/samples_received)*100;
        $scope.children_under_15=(children_under_15/samples_received)*100;
        $scope.other_treatment=(other_treatment/samples_received)*100;
        $scope.treatment_blank_on_form=(treatment_blank_on_form/samples_received)*100;

        //console.log("distrcts data:: "+JSON.stringify($scope.district_numbers));
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

        $scope.generalFilter();
    }



    $scope.evaluator=function(that){  
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

    $scope.initMonths=function(){
        var ret={};
        for(var i in $scope.filter_duration){
            ret[$scope.filter_duration[i]]=0; 
        }
        return ret;
    }



    $scope.generalFilter=function(){
        var samples_received=0,suppressed=0,valid_results=0,rejected_samples=0;
        var cd4_less_than_500=0,pmtct_option_b_plus=0,children_under_15=0,other_treatment=0,treatment_blank_on_form=0;
        var inited_ms=$scope.initMonths();
        var samples_received_data={'plasma':inited_ms,'dbs':inited_ms};
        /*var dbs_samples=0;
        var plasma=0;*/
        for(var i in results_json){
            var that = results_json[i];


            if($scope.evaluator(that)){
                //key indicators
                samples_received+=that.samples_received;
                suppressed+=that.suppressed;
                valid_results+=that.valid_results;
                rejected_samples+=that.rejected_samples;

                //other filters
                cd4_less_than_500+=that.cd4_less_than_500;
                pmtct_option_b_plus+=that.pmtct_option_b_plus;
                children_under_15+=that.children_under_15;
                other_treatment+=that.other_treatment;
                treatment_blank_on_form+=that.treatment_blank_on_form;

                samples_received_data.plasma[that.year_month]+=2;
                samples_received_data.dbs[that.year_month]+=1;


               /* p_val=samples_received_data.plasma[that.year_month];
                d_val=samples_received_data.dbs[that.year_month];

                samples_received_data.plasma[that.year_month]=((that.samples_received-that.dbs_samples)+p_val)||0;
                samples_received_data.dbs[that.year_month]=(that.dbs_samples+d_val)||0;*/
                /*dbs_samples+=that.dbs_samples;
                plasma+=(that.samples_received-that.dbs_samples);*/

            }         
        }

        //key indicators
        // $scope.dbs_samples=dbs_samples;
        // $scope.plasma=plasma;


        $scope.samples_received=samples_received;
        $scope.suppression_rate=(suppressed/valid_results)*100;
        $scope.rejection_rate=(rejected_samples/samples_received)*100;

        //final setting for other indicators
        $scope.cd4_less_than_500=((cd4_less_than_500/samples_received)*100)||0;
        $scope.pmtct_option_b_plus=((pmtct_option_b_plus/samples_received))*100||0;
        $scope.children_under_15=((children_under_15/samples_received))*100||0;
        $scope.other_treatment=((other_treatment/samples_received)*100)||0;
        $scope.treatment_blank_on_form=((treatment_blank_on_form/samples_received)*100)||0;

        $scope.displaySamplesRecieved(samples_received_data);

       /* 
        $scope.cd4_less_than_500=cd4_less_than_500;
        $scope.pmtct_option_b_plus=pmtct_option_b_plus;
        $scope.children_under_15=children_under_15;
        $scope.other_treatment=other_treatment;
        $scope.treatment_blank_on_form=treatment_blank_on_form;*/

    };


    $scope.displaySamplesRecieved=function(srd){       //$scope.samples_received=100000;
        console.log("graph stuff :"+JSON.stringify(srd));
        var data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];
        for(var i in $scope.filter_duration){
            var mth=$scope.filter_duration[i];
            data[0].values.push({"x":mth,"y":srd.plasma[mth]});
            data[1].values.push({"x":mth,"y":srd.dbs[mth]});
        }

        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().color(["#00786A","#526CFD"]);
            d3.select('#samples_received svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
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

   function inArray(val,arr){
    var ret=false;
    for(var i in arr){
        if(val==arr[i]) ret=true;
    }
    return ret;
}
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



/*
-Hide empty filters --- don't show the (~)
-Xes on the filter labels  and make the same background color
-Open sense for the font style
-bug on the graphs when o =n hover for av. positivity --- try with wakiso --HCIV, Hospital, HC III



*/
</script>
</html>
