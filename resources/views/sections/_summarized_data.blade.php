<div class="row summary-sect" >
    <select ng-model="summary_facility" ng-init="summary_facility=null">
        <option value=''>select facility</option>
        <option class="ng-cloak" ng-repeat="f in facilities2" value="<% f.id %>">
            <% f.name %>
        </option>
    </select> <br>
    <div class="col-lg-6"> 
                       
        <span class='hdr'>Suppression Rate</span><br>
        <table class="row-border hover table table-bordered table-condensed table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th colspan="3"><% summary_facility!=null? labels.facilities[summary_facility] : 'Facility' %></th>
                    <th colspan="3">Uganda</th>
                    
                </tr>
            </thead>
            <thead>
                <tr>
                    <th >group</th>
                    <th >All</th>
                    <th >F</th>
                    <th >M</th>

                    <th >All</th>
                    <th >F</th>
                    <th >M</th>
                </tr>
            </thead>
            <tbody>                                
                <tr ng-repeat="(i,a) in age_grp_numbers | orderBy:'_id'" >
                    <td class="ng-cloak"><% labels.age_grps[i] %></td>

                    <td class="ng-cloak"><% (((tt[summary_facility][i].suppressed_f+tt[summary_facility][i].suppressed_m)/(tt[summary_facility][i].valid_results_f+tt[summary_facility][i].valid_results_m))*100) | number:1 %>%</td>
                    <td class="ng-cloak"><% ((tt[summary_facility][i].suppressed_f/tt[summary_facility][i].valid_results_f)*100) | number:1 %>%</td>
                    <td class="ng-cloak"><% ((tt[summary_facility][i].suppressed_m/tt[summary_facility][i].valid_results_m)*100) | number:1 %>%</td>
        
                    <td class="ng-cloak"><% (((a.suppressed_f+a.suppressed_m)/(a.valid_results_f+a.valid_results_m))*100) | number:1 %>%</td>
                    <td class="ng-cloak"><% ((a.suppressed_f/a.valid_results_f)*100) | number:1 %>%</td>
                    <td class="ng-cloak"><% ((a.suppressed_m/a.valid_results_m)*100) | number:1 %>%</td>
                </tr>                        
             </tbody>
         </table>                  
    </div>

    <div class="col-lg-6">                        
        xxxx                       
    </div>
</div>