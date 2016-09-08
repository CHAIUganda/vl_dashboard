<div class="row">
    <div class="col-lg-6">                        
        <div id="regimen_groups" class="db-charts">
            <svg></svg>
        </div>                        
    </div>

    <div class="col-lg-6 facilties-sect " >
        <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
            <thead>
                <tr>
                    <th>Regimen</th>
                    <th>Samples Received</th>
                    <th>Samples&nbsp;Tested</th>
                    <th>Number Suppressed</th>
                    <th>Percentage of Samples&nbsp;(%)</th>
                </tr>
            </thead>
            <tbody>                                
                <tr ng-repeat="r in regimen_numbers | orderBy:'-samples_received'" >
                    <td class="ng-cloak"><% 1 %></td>
                    <td class="ng-cloak"><% r.samples_received|number %></td>
                    <td class="ng-cloak"><% r.total_results|number %></td>
                    <td class="ng-cloak"><% r.suppressed|number %></td>
                    <td class="ng-cloak"><% ((r.samples_received/samples_received)*100 )| number:1 %> %</td>
                </tr>                        
             </tbody>
        </table>
    </div>
</div>