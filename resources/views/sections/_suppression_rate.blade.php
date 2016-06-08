<div class="row">

    <div class="col-lg-6">
       <div id="supression_rate" class="db-charts">
            <svg></svg>
        </div>
    </div>
   
    <div class="col-lg-6 facilties-sect" >

         <span class='dist_faclty_toggle' ng-init="show_fclties2=false" ng-click="showF(2)">
            <span class='active' id='d_shw2'>&nbsp;&nbsp;DISTRICTS&nbsp;&nbsp;</span>
            <span id='f_shw2'>&nbsp;&nbsp;FACILITIES &nbsp;&nbsp;</span>
        </span>
        <div ng-hide="show_fclties2">
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th width='80%'>District</th>
                        <th width='10%'>Valid Results</th>
                        <th width='10%'>Suppression Rate (%)</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="d in district_numbers" >
                        <td class="ng-cloak"><% labels.districts[d._id] %></td>
                        <td class="ng-cloak"><% d.valid_results|number %></td>
                        <td class="ng-cloak"><% ((d.suppressed/d.valid_results)*100)|number:1 %> %</td>
                    </tr>                        
                 </tbody>
             </table>
         </div>
         <div ng-show="show_fclties2">
             <table datatable="ng" ng-hide="checked" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th width='90%'>Facility</th>
                        <th width='5%'>Valid Results</th>
                        <th width='5%'>Suppression rate (%)</th>
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="f in facility_numbers" >
                        <td class="ng-cloak"><% labels.facilities[f._id].name %></td>
                        <td class="ng-cloak"><% f.valid_results|number %></td>
                        <td class="ng-cloak"><% ((f.suppressed/f.valid_results)*100)|number:1 %> %</td>
                    </tr>                        
                 </tbody>
             </table>
         </div>
    </div>
</div> 