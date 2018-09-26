<div class="facilties-sect">
      <table id="retest_ns_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
        <thead>
          <tr>
                                        <th >Patient ID</th>
                                        <th >Facility</th>
                                        <th >ART Number</th>
                                        <th>Sample ID</th>
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
                                  
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.patient_unique_id %></td>
                                      <td ng-class="ng-cloak"><% labels.facilities_details[retestNSPatients_object.facility_id].dhis2_name %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.art_number %></td>

                                      <td ng-class="ng-cloak"><% retestNSPatients_object.vl_sample_id %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.date_collected %></td>

                                      <td ng-class="ng-cloak"><% retestNSPatients_object.date_received %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.alpha_numeric_result %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.recommended_retest_date %></td>
                                      <td ng-class="ng-cloak"><% retestNSPatients_object.phone_number %></td>

                                      <td ng-class="ng-cloak">
                                        
                                              
                                        </td>
                                      <td ng-class="ng-cloak"></td>
                                      <td ng-class="ng-cloak"></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>

