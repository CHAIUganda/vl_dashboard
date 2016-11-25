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
            <br>
            <br>
            <button id="exportButton" class="btn btn-success">Download CSV</button> <br>
            <button type="button" ng-csv="district_numbers" filename="test.csv">Export</button>

         </div>
         
         <div ng-show="show_fclties1">
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
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
                        <td class="ng-cloak"><% labels.facilities[f._id] %></td>
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
            <br>
            <br>
            <button type="button" ng-csv="facility_numbers" filename="facilities.csv" class="btn btn-success">Export facilities</button>
         </div>

    </div>
</div>

