<div class="facilties-sect">
      <table id="a_patients_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
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
                                  
                                      <td class="ng-cloak"><% retestNSPatients_object.patientUniqueID %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.facility %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.artNumber %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.collectionDate %></td>

                                      <td class="ng-cloak"><% retestNSPatients_object.receiptDate %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.result %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.recommendedRetestDate %></td>
                                      <td class="ng-cloak"><% retestNSPatients_object.phone %></td>

                                      <td><a>Click Here</a></td>
                                      <td></td>
                                      <td></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>