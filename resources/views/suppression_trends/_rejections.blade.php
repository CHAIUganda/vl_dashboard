<div class="facilties-sect">
      <table id="rejections_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
        <thead>
          <tr>
                                        <th >Patient ID</th>
                                        <th >Facility</th>
                                        <th >ART Number</th>
                                        <th>Date of Collection</th>

                                        <th >Date of Arrival at CPHL</th>
                                        <th >Rejection Category </th>
                                        <th> Rejection Reason</th>
                                        <th>Contact</th>

                                        <th>Action</th>
                                        <th>Comments</th>
                                        
          </tr>
        </thead>
                                <tbody>                                
                                  <tr ng-repeat="patientsWithInvalidResults_object in patientsWithInvalidResults" >
                                  
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.patientUniqueID %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.facility %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.artNumber %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.collectionDate %></td>

                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.receiptDate %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.status %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.result %></td>
                                      <td class="ng-cloak"><% patientsWithInvalidResults_object.phone %></td>

                                      <td></td>
                                      <td></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>