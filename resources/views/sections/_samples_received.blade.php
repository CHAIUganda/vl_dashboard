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
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th width='60%'>District</th>
                        <th width='10%'>Samples Received</th>
                        <th width='20%'>DBS (%)</th>
                        <th width='10%'>Samples Tested</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="d in district_numbers | orderBy:'-samples_received'" >
                        <td class="ng-cloak"><% d.name %></td>
                        <td class="ng-cloak"><% d.samples_received|number %></td>
                        <td class="ng-cloak"><% ((d.dbs_samples/d.samples_received)*100 )| number:1 %> %</td>
                        <td class="ng-cloak"><% d.total_results|number %></td>
                    </tr>                        
                 </tbody>
             </table>
         </div>
         <div ng-show="show_fclties1">
             <table datatable="ng" ng-hide="checked" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th width='60%'>Facility</th>
                        <th width='10%'>Samples Received</th>
                        <th width='20%'>DBS (%)</th>
                        <th width='10%'>Samples Tested</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="f in facility_numbers" >
                        <td class="ng-cloak"><% f.name %></td>
                        <td class="ng-cloak"><% f.samples_received|number %></td>
                        <td class="ng-cloak"><% ((f.dbs_samples/f.samples_received)*100 )| number:1 %> %</td>
                        <td class="ng-cloak"><% f.total_results|number %></td>
                    </tr>                        
                 </tbody>
             </table>
         </div>

    </div>
</div>