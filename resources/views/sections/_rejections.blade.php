<div class="row">
    <div class="col-lg-6">
        <div id="rejection_rate" class="db-charts">
            <svg></svg>
        </div>
    </div>
   
    <div class="col-lg-6 facilties-sect" >

         <span class='dist_faclty_toggle' ng-init="show_fclties3=false" ng-click="showF(3)">
            <span class='active' id='d_shw3'>&nbsp;&nbsp;DISTRICTS&nbsp;&nbsp;</span>
            <span id='f_shw3'>&nbsp;&nbsp;FACILITIES &nbsp;&nbsp;</span>
        </span>
        <div ng-hide="show_fclties3">
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th width='70%'>District</th>
                        <th width='10%'>Samples Received</th>
                        <th width='10%'>Samples Rejected</th>
                        <th width='10%'>Rejection Rate (%)</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="d in district_numbers" >
                        <td class="ng-cloak"><% labels.districts[d._id] %></td>
                        <td class="ng-cloak"><% d.samples_received|number %></td>
                        <td class="ng-cloak"><% d.rejected_samples|number %></td>
                        <td class="ng-cloak"><% ((d.rejected_samples/d.samples_received)*100)|number:1 %> %</td>
                    </tr>                        
                 </tbody>
             </table>

         </div>
         <div ng-show="show_fclties3">
             <table datatable="ng" ng-hide="checked" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        
                        <th width='20%'>District</th>
                        <th width='20%'>Hub</th>
                        <th width='60%'>Facility</th>
                        <th width='5%'>Samples Received</th>
                        <th width='5%'>Rejection rate (%)</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="f in facility_numbers">

                        <td class="ng-cloak"><% labels.districts[f._id.district_id] %></td>
                        <td class="ng-cloak"><% hubs2[f._id.hub_id].name %></td>
                        <td class="ng-cloak"><% labels.facilities[f._id.facility_id] %></td>
                        <td class="ng-cloak"><% f.samples_received %></td>
                        <td class="ng-cloak"><% ((f.rejected_samples/f.samples_received)*100)|number:1 %> %</td>
                    </tr>                        
                 </tbody>
            </table>
         </div>

         <br>
         <br>
         <button ng-hide="show_fclties3" id="exportDistrictsRejectionRate" type="button" ng-csv="export_district_rejection_numbers"  class="btn btn-success" filename="district_rejection_<%current_timestamp%>.csv" csv-header="['District', 'Received Samples','Rejected Samples','Rejection Rate (%)']">Download CSV</button>

         <br>
         <br>
         <button ng-show="show_fclties3" id="exportFacilitiesRejectionRate" type="button" ng-csv="export_facility_rejection_numbers" filename="facilities_rejection_<%current_timestamp%>.csv" class="btn btn-success" csv-header="['District','Hub','Facility', 'Received Samples','Rejected Samples', 'Rejection Rate (%)']">Download CSV</button>

    </div>
</div>                