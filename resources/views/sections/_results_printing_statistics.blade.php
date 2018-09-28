 <div class="row">
    

    <div class="col-lg-12 facilties-sect " >
        
 <table  id="results-stats-table" datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th>Facility</th>  
                        <th>Hub</th>                   
                        <th>Implementing Partner</th>
                        <th># Pending Results</th>

                        
                        <th>Last Printed on</th>
                        <th>Oldest Result Pending Printing</th>

                    </tr>
                </thead>
                   <tbody>                                
                        <tr ng-repeat="f in facilities_array" >
                            <td class="ng-cloak"><% f.facility %></td>
                            <td class="ng-cloak"><% f.hub %></td>
                            <td class="ng-cloak"><% f.ip %></td>
                            <td class="ng-cloak"><%  f.num_pending_dispatch%></td>

                            
                            <td class="ng-cloak"><% f.last_dispatched_at %></td>
                            <td class="ng-cloak"><%  %></td>
                        </tr>                        
                     </tbody>
                 
            </table>

            

    </div>
</div>





