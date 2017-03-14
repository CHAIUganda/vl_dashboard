@extends('layout')

@section('content')  

<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">Facilities :: {!! \Auth::user()->hub_name !!}</h3> </div>
    <div class="panel-body">

     <?php
     $to_date=date("Ym");
     $fro_date=MyHTML::dateNMonthsBack();

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
        <br>
         <span ng-model="loading" ng-init="loading=true"></span>
        <div ng-show="loading" style="text-align: center;padding:10px;"> <img src="{{ asset('/images/loading.gif') }}" height="20" width="20"> processing</div>


          
             <ul class="nav nav-tabs" role="tablist">
              <li id="li-suppression-trend" class="active"><a href="#suppression-trend" role="tab" data-toggle="tab">Suppression Trend</a></li>
              <li id="li-action-pane"><a href="#action-pane" role="tab" data-toggle="tab">Action Pane</a></li>
              <li id="li-retest-ns"><a href="#retest-ns" role="tab" data-toggle="tab">Retest-NotSuppressing</a></li>
              <li id="li-retest-s"><a href="#retest-s" role="tab" data-toggle="tab">Retest-Suppressing</a></li>
              <li id="li-rejections"><a href="#rejections" role="tab" data-toggle="tab">Rejections</a></li>
              <li id="li-v-patients"><a href="#v-patients" role="tab" data-toggle="tab">Valid Patients</a></li>
              <li id="li-a-patients"><a href="#a-patients" role="tab" data-toggle="tab">All Patients</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="suppression-trend">
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
                  <span class="rm_item">Previously Not Suppressing, Not Suppressing Recently</span>
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
                  <span class="rm_item">Previously Suppressing, Not Suppressing Recently</span>
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
              </div><!--End suppression-trends tab -->
              <div class="tab-pane" id="action-pane"> @include('suppression_trends._action_pane')</div>
              <div class="tab-pane" id="retest-ns"> @include('suppression_trends._retest_ns')</div>
              <div class="tab-pane" id="retest-s"> @include('suppression_trends._retest_suppressing')</div>
              <div class="tab-pane" id="rejections"> @include('suppression_trends._rejections')</div>

              <div class="tab-pane" id="v-patients">
                       <div class="facilties-sect">
                        <table id="v_patients_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th >Patient ID</th>
                                    <th >Facility</th>
                                    <th >ART Number</th>
                                    <th >Date of Arrival at CPHL</th>
                                    <th >Results </th>
                                    
                                </tr>
                            </thead>
                            <tbody>                                
                                      
                              <tr ng-repeat="validPatientResults_object in validPatientResults" >
                              
                                  <td class="ng-cloak"><% validPatientResults_object.patientID %></td>
                                  <td class="ng-cloak"><% validPatientResults_object.facility %></td>
                                  <td class="ng-cloak"><% validPatientResults_object.artNumber %></td>
                                  <td class="ng-cloak"><% validPatientResults_object.datesArrivedAtCPHL %></td>
                                  <td class="ng-cloak"><% validPatientResults_object.results %></td>
                              </tr>                 
                             </tbody>
                        </table>
                      </div>
               </div><!--end v-patients tab-pane-->
               <div class="tab-pane" id="a-patients">
                    <div class="facilties-sect">
                        <table id="a_patients_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th >Patient ID</th>
                                    <th >Facility</th>
                                    <th >ART Number</th>
                                    <th >Date of Arrival at CPHL</th>
                                    <th >Results </th>
                                    
                                </tr>
                            </thead>
                            <tbody>                                
                              <tr ng-repeat="allPatientsResults_object in allPatientsResults" >
                              
                                  <td class="ng-cloak"><% allPatientsResults_object.patientID %></td>
                                  <td class="ng-cloak"><% allPatientsResults_object.facility %></td>
                                  <td class="ng-cloak"><% allPatientsResults_object.artNumber %></td>
                                  <td class="ng-cloak"><% allPatientsResults_object.datesArrivedAtCPHL %></td>
                                  <td class="ng-cloak"><% allPatientsResults_object.results %></td>
                              
                          </tr>           
                             </tbody>
                        </table>
                      </div>
               </div><!--end a-patients tab-pane-->
            </div>   

      
 </div>
</div>

<script type="text/javascript" src=" {{ asset('js/live_for_hubs.js') }} "></script>

@endsection()