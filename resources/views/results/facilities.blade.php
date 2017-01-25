@extends('layout')

@section('content')  

<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">Facilities :: {!! \Auth::user()->hub_name !!}</h3> </div>
    <div class="panel-body">
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
        <!-- end filters-->
        @include('sections._hub_filters')
         <span ng-model="loading" ng-init="loading=true"></span>
        <div ng-show="loading" style="text-align: center;padding:10px;"> <img src="{{ asset('/images/loading.gif') }}" height="20" width="20"> processing</div>
  
            <ul class="nav nav-tabs" role="tablist">
              <li class="active"><a href="#facilities" role="tab" data-toggle="tab">Facilities</a></li>
              <li><a href="#suppression-trend" role="tab" data-toggle="tab">Suppression Trend</a></li>
              <!--li><a href="#messages" role="tab" data-toggle="tab">Messages</a></li>
              <li><a href="#settings" role="tab" data-toggle="tab">Settings</a></li-->
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="facilities">
                        <table id="results-table" class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>Facility</th>               
                              <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <!-- <th># Pending printing</th>
                                <th># Printed</th> -->
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facilities AS $facility)
                             <tr>
                                <td><a href='/results_list?f={{$facility->id}}'>{{ $facility->facility }}</a></td>               
                                <td>{{ $facility->contactPerson }}</td>
                                <td>{{ $facility->phone }}</td>
                                <td>{{ $facility->email }}</td>
                                
                                <td>
                                    <?= "<a class='btn btn-danger btn-xs' href='/results_list?f=$facility->id'>view pending</a>" ?>
                                    <?= "<a class='btn btn-danger btn-xs' href='/results_list?f=$facility->id&printed=YES'>printed/downloaded</a>" ?>
                                </td>
                            </tr>
                            @endforeach
        
                        </tbody>
                    </table>
              </div>
              <div class="tab-pane" id="suppression-trend">
                <div class="facilties-sect" style="width:50%">
                   <table id="suppression-table" class="table table-condensed table-bordered">
                      <tr>
                        <th>Previous Test</th>
                        <th>Most Recent Test</th>
                        <th>#</th>
                        <th>%</th>
                      </tr>
                      <tr>
                        <td rowspan="2"><div>Not Suppressed</div></td>
                        <td><span class="rm_item">Not Suppressed</span></td>
                        <td><% previouslyNonSuppressingCurrentlyNotSuppressing %></td>
                        <td><%((previouslyNonSuppressingCurrentlyNotSuppressing/previouslyNotSuppressing)*100) | number: 1%></td>
                      </tr>
                      <tr>
                        <td>Suppressed</td>
                        <td><%previouslyNonSuppressingCurrentlySuppressing%></td>
                        <td><%((previouslyNonSuppressingCurrentlySuppressing/previouslyNotSuppressing)*100) | number: 1%></td>
                        
                      </tr>
                      <tr>
                        <td rowspan="2"><div>Suppressed</div></td>
                        <td>Suppressed</td>
                        <td><%previouslySuppressingCurrentlySuppressing%></td>
                        <td><%((previouslySuppressingCurrentlySuppressing/previouslySuppressing)*100) | number: 1%></td>
                      </tr>
                      <tr>
                        <td><span class="rm_item">Not Suppressed</span></td>
                        <td><%previouslySuppressingCurrentlyNotSuppressing%></td>
                        <td><%((previouslySuppressingCurrentlyNotSuppressing/previouslySuppressing)*100) | number: 1%></td>
                        
                      </tr>
                    </table>
                </div>
                <div class="facilties-sect">
                  <table id="previously_not_suppressing_ns_table" datatable="ng"  class="row-border hover table table-bordered table-condensed table-striped">
                      <thead>
                          <tr>
                              <th >Patient ID</th>
                              <th >Facility</th>
                              <th >ART Number</th>
                              <th >Previous Date of Collection</th>
                              <th >Previous Date of Arrival at CPHL</th>
                              <th >Previous Results</th>
                              <th>Recent Date of Collection</th>
                              <th>Recent Date of Arrival at CPHL</th>
                              <th >Recent Results</th>
                              <th >Contact</th>
                          </tr>
                      </thead>
                      <tbody>                                
                          <tr ng-repeat="previouslyNScurrentlyNS_object in previouslyNScurrentlyNS" >
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.patientID %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.facility %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.artNumber %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.previousCollectionDate %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.prevoiusReceiptDate %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.previousResults %></td>

                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.currentCollectionDate %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.currentReceiptDate %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.currentResults %></td>
                              <td class="ng-cloak"><% previouslyNScurrentlyNS_object.phone %></td>
                          </tr>                        
                       </tbody>
                  </table>
                </div>
                <br><br><br>

                <div class="facilties-sect">
                  <table id="previously_suppressing_ns_table" datatable="ng"  class="row-border hover table table-bordered table-condensed table-striped">
                      <thead>
                          <tr>
                              <th >Patient ID</th>
                              <th >Facility</th>
                              <th >ART Number</th>
                              <th >Previous Date of Collection</th>
                              <th >Previous Date of Arrival at CPHL</th>
                              <th >Previous Results</th>
                              <th>Recent Date of Collection</th>
                              <th>Recent Date of Arrival at CPHL</th>
                              <th >Recent Results</th>
                              <th >Contact</th>
                          </tr>
                      </thead>
                      <tbody>                                
                          <tr ng-repeat="previouslyScurrentlyNS_object in previouslyScurrentlyNS" >
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.patientID %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.facility %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.artNumber %></td>

                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.previousCollectionDate %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.prevoiusReceiptDate %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.previousResults %></td>

                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.currentCollectionDate %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.currentReceiptDate %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.currentResults %></td>
                              <td class="ng-cloak"><% previouslyScurrentlyNS_object.phone %></td>
                          </tr>                        
                       </tbody>
                  </table>
                </div>
              </div>
              <!--div class="tab-pane" id="messages">..mrss.</div>
              <div class="tab-pane" id="settings">..sett.</div -->
            </div>
        
        

 </div>
</div>

<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable();
});
</script>
<script type="text/javascript" src=" {{ asset('js/live_for_hubs.js') }} "></script>
@endsection()