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
    <script src="{{ asset('/js/jquery-ui.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/twitter-bootstrap-3.3/js/bootstrap.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/angular.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular-route.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/angular-datatables.min.js') }}" type="text/javascript"></script>


    <link href="{{ asset('/css/nv.d3.min.css') }}" rel="stylesheet" type="text/css">
    <script src="{{ asset('/js/d3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('/js/nv.d3.min.js') }}"></script>
    <script src="{{ asset('/js/stream_layers.js') }}"></script>

    
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
     $months_by_years=yearByMonths(2014,1); 
     //krsort($months_by_years);
     ?>
     <span ng-model="month_labels" ng-init='month_labels={!! json_encode(MyHTML::months()) !!}'></span>
     <span ng-model="filtered" ng-init='filtered=false'></span>

     <div class='row'><div class='col-md-1' style="padding-top:17px; font-size:bolder"><span class='hdr hdr-grey'>FILTERS:</span> </div>
         
     <div class="filter-section col-md-9">        
        <span ng-model='filter_duration' ng-init='filter_duration={!! json_encode($init_duration) !!};init_duration={!! json_encode($init_duration) !!};'>
          <span class="filter-val ng-cloak">
            <% filter_duration[0] |d_format %> - <% filter_duration[filter_duration.length-1] | d_format %> 
        </span>
        </span>
        &nbsp;

        <span ng-model='filter_districts' ng-init='filter_districts={}'>
            <span ng-repeat="(d_nr,d_name) in filter_districts"> 
                <span class="filter-val ng-cloak"> <% d_name %> (d) <x ng-click='removeTag("district",d_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_hubs' ng-init='filter_hubs={}'>
            <span ng-repeat="(h_nr,h_name) in filter_hubs">
                <span class="filter-val ng-cloak"> <% h_name %> (h) <x ng-click='removeTag("hub",h_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_age_group' ng-init='filter_age_group={}'>
            <span ng-repeat="(ag_nr,ag_name) in filter_age_group">
                <span class="filter-val ng-cloak"> <% ag_name %> (a) <x ng-click='removeTag("age_group",ag_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-show="filtered" class="filter_clear" ng-click="clearAllFilters()">reset all</span>

     </div></div>

     <table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
        <tr>
            <td width='20%' >
                <span ng-model='fro_date_slct' ng-init='fro_date_slct={!! json_encode($months_by_years) !!}'></span>
                <select ng-model="fro_date" ng-init="fro_date='all'" ng-change="dateFilter('fro')">
                    <option value='all'>FROM DATE</option>
                    <optgroup class="ng-cloak" ng-repeat="(yr,mths) in fro_date_slct | orderBy:'-yr'" label="<% yr %>">
                        <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %>-<% mth %>"> 
                            <% month_labels[mth] %> '<% yr|slice:-2 %>
                        </option>
                    </optgroup>
                </select>
            </td>
            <td width='20%' >
                <span ng-model='to_date_slct' ng-init='to_date_slct={!! json_encode($months_by_years) !!}'></span>
                <select ng-model="to_date" ng-init="to_date='all'" ng-change="dateFilter('to')">
                    <option value='all'>TO DATE</option>
                    <optgroup class="ng-cloak" ng-repeat="(yr,mths) in to_date_slct" label="<% yr %>">
                        <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %>-<% mth %>"> 
                            <% month_labels[mth] %> '<% yr|slice:-2 %>
                        </option>
                    </optgroup>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="district" ng-init="district='all'" ng-change="filter('district')">
                    <option value='all'>DISTRICTS</option>
                    <option class="ng-cloak" ng-repeat="dist in districts2 | orderBy:'name'" value="<% dist.id %>">
                        <% dist.name %>
                    </option>
                </select>
            </td>
            <td width='20%' id='dist_elmt'>
                <select ng-model="hub" ng-init="hub='all'" ng-change="filter('hub')">
                    <option value='all'>HUBS</option>
                    <option class="ng-cloak" ng-repeat="hb in hubs2|orderBy:'name'" value="<% hb.id %>">
                        <% hb.name %>
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
                                    <th width='60%'>District</th>
                                    <th width='10%'>Samples Received</th>
                                    <th width='20%'>DBS (%)</th>
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
        <div class='col-sm-2'>
            <font class='addition-metrics figure ng-cloak' ng-init="tb_infection=0" ng-model="tb_infection">
                <% ((tb_infection/samples_received)*100)|number:1 %>%
            </font><br>
            <font class='addition-metrics desc'>TB INFECTION</font>            
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

<script type="text/javascript" src=" {{ asset('js/vl.js') }} "></script>
</html>
