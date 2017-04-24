<div class="facilties-sect">
      <table id="retest_ns_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
        <thead>
          <tr>
                                        <th >Patient ID</th>
                                        <th >Facility</th>
                                        <th >ART Number</th>
                                        <th>Date of Collection</th>

                                        <th >Date Arrived at CPHL</th>
                                        <th >Results</th>
                                        <th> Recommended Retest Date</th>
                                        <th>Contact</th>
  
                                        <th>More Info</th>
                                        <th>Action</th>
                                        <th>Comments</th>
                                        
          </tr>
        </thead>
                                <tbody>                                
                                  <tr ng-repeat="retestNSPatients_object in retestNSPatients" >
                                  
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.patientUniqueID %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.facility %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.artNumber %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.collectionDate %></td>

                                      <td ng-class="ng-cloak"><% retestNSPatients_object.receiptDate %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.result %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.recommendedRetestDate %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.phone %></td>

                                      <td ng-class="ng-cloak">
                                        <div class="cursor" ng-click="loadProgressMap(retestNSPatients_object.patientID,retestNSPatients_object.patientUniqueID,'retestNonSuppressedMap')">
                                          Click Here for details
                                        </div>
                                              
                                        </td>
                                      <td ng-class="ng-cloak"></td>
                                      <td ng-class="ng-cloak"></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>

<div id="progressmap_restest_not_suppressed_id">
    <h3>Progress Map for <% patientUniqueID %></h3>
    <svg></svg>
    <table class="table table-bordered table-condensed table-striped">
      <tr>
        <th class="ng-cloak" colspan="2">Collection Date</th> 
      </tr>
      <tr>
        <td ng-repeat="progressmaplabel in progressmaplabels" class="figure ng-cloak">
           <% progressmaplabel.x %>
        </td>
      </tr>
      <tr>
        <td ng-repeat="progressmapresult in progressmapresults" class="figure ng-cloak">
           <% progressmapresult.y %>
        </td>
      </tr>
    </table>
</div>
