<div class="facilties-sect">
      <table id="suppression_trend_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
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
                                  <tr ng-repeat="retestSuppressingPatients_object in retestSuppressingPatients" >
                                  
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.patientUniqueID %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.facility %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.artNumber %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.collectionDate %></td>

                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.receiptDate %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.result %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.recommendedRetestDate %></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"><% retestSuppressingPatients_object.phone %></td>

                                      <td ng-class="[retestSuppressingPatients_object.class]"><a>Click Here</a></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"></td>
                                      <td ng-class="[retestSuppressingPatients_object.class]"></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>