<div class="facilties-sect">
      <table id="rejections_table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
        <thead>
          <tr>
                                        <th >Patient ID</th>
                                        <th >Sample ID</th>
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
                                  <tr ng-repeat="patientsWithRejections_object in patientsWithRejections" >
                                      <td class="ng-cloak"><% patientsWithRejections_object.patient_unique_id %></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.vl_sample_id %></td>
                                      <td class="ng-cloak"><% labels.facilities_details[patientsWithRejections_object.facility_id].dhis2_name%></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.art_number %></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.date_collected %></td>

                                      <td class="ng-cloak"><% patientsWithRejections_object.date_received %></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.rejection_category %></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.rejection_reason %></td>
                                      <td class="ng-cloak"><% patientsWithRejections_object.phone %></td>

                                      <td></td>
                                      <td></td>
                                  
                              </tr>           
                                 </tbody>
                            </table>
</div>