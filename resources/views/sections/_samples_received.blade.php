<div class="row">
    <div class="col-lg-6">                        
        <div id="samples_received" class="db-charts">
            <svg></svg>
        </div>                        
    </div>

    <div class="col-lg-6 facilties-sect " >
        <span class='dist_faclty_toggle' ng-model="show_fclties1" ng-init="show_fclties1=false" ng-click="showF(1)">
            <span class='active' id='d_shw1'>&nbsp;&nbsp;DISTRICTS&nbsp;&nbsp;</span>
            <span id='f_shw1'>&nbsp;&nbsp;FACILITIES &nbsp;&nbsp;</span>
        </span>
        <div ng-hide="show_fclties1">           
            <table id="samples_received_table" datatable="ng"  class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th >District</th>
                        <th >Samples Received</th>
                        <th >Patients&nbsp;for samples&nbsp;received</th>
                        <th >Samples Tested</th>
                        <th >Samples Pending</th>
                        <th >Samples Rejected</th>
                        <th >DBS&nbsp;(%)</th>
                        <th >Plasma</th>

                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="d in district_numbers" >
                        <td class="ng-cloak"><% labels.districts[d._id] %></td>
                        <td class="ng-cloak"><% d.samples_received|number %></td>
                        <td class="ng-cloak"><% d.patients_received|number %></td>
                        <td class="ng-cloak"><% d.total_results|number %></td>
                        <td class="ng-cloak"><% (d.samples_received - d.total_results) |number %></td>
                        <td class="ng-cloak"><% d.rejected_samples|number %></td>
                        <td class="ng-cloak"><% ((d.dbs_samples/d.samples_received)*100 )| number:1 %> %</td>
                        <td class="ng-cloak"><% (((d.samples_received-d.dbs_samples)/d.samples_received)*100 )| number:1 %> %</td>
                    </tr>                        
                 </tbody>
                 
            </table>
            
         </div>

         <div ng-show="show_fclties1">

            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th >District&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th >Hub&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th >Facility&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th >Samples Received</th>
                        <th >Patients&nbsp;for samples&nbsp;received</th>
                        <th >Samples Tested</th>
                        <th >Samples Pending</th>
                        <th >Samples Rejected</th>
                        <th >DBS&nbsp;(%)</th>
                        <th >Plasma</th>

                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="f in facility_numbers | orderBy:'-samples_received'" >
                        
                        <td class="ng-cloak"><% labels.districts[f._id.district_id] %></td>
                        <td class="ng-cloak"><% getHubName(f._id.hub_id) %></td>
                        <td class="ng-cloak"><% labels.facilities[f._id.facility_id] %></td>
                        <td class="ng-cloak"><% f.samples_received|number %></td>
                        <td class="ng-cloak"><% f.patients_received|number %></td>
                        <td class="ng-cloak"><% f.total_results|number %></td>
                        <td class="ng-cloak"><% (f.samples_received - f.total_results) |number %></td>
                        <td class="ng-cloak"><% f.rejected_samples|number %></td>
                        <td class="ng-cloak"><% ((f.dbs_samples/f.samples_received)*100 )| number:1 %> %</td>
                        <td class="ng-cloak"><% (((f.samples_received-f.dbs_samples)/f.samples_received)*100 )| number:1 %> %</td>
                    </tr>                        
                 </tbody>
                 
            </table>

         </div>
            
            <br>
            <br>
            <button ng-hide="show_fclties1" id="exportDistricts" type="button" ng-csv="export_district_numbers"  class="btn btn-success" filename="samples_<%current_timestamp%>.csv" csv-header="['District', 'Samples Received', 'Patients for Samples Received','Samples Tested','Samples Pending','Samples Rejected','DBS %','Plasma %']">Download CSV</button>

            <br>
            <br>
            <button ng-show="show_fclties1" id="exportFacilities" type="button" ng-csv="export_facility_numbers" filename="facilities_<%current_timestamp%>.csv" class="btn btn-success" csv-header="['District','Hub','Facility', 'Samples Received', 'Patients for Samples Received','Samples Tested','Samples Pending','Samples Rejected','DBS %','Plasma %']">Download CSV</button>


    </div>
</div>

