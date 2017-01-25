<span ng-model="filtered" ng-init='filtered=false'></span>
<span class="hdr hdr-grey" style="float:right;font-size:11px"><% data_date %></span><br>

<div class='row'>
    <div class='col-md-1' style="padding-top:17px; font-size:bolder">
        <span class='hdr hdr-grey'>FILTERS:</span>
    </div>
     
    <div class="filter-section col-md-9">        
        <span ng-model='filter_duration' ng-init='filter_duration={!! json_encode($init_duration) !!};init_duration={!! json_encode($init_duration) !!};'>
          <span ng-init="fro_date_label='{!! $fro_date !!}';to_date_label='{!! $to_date !!}'" class="filter-val ng-cloak">
            <% fro_date_label |d_format %> - <% to_date_label |d_format %>

         </span>
        </span>
        &nbsp;

        <span ng-show="filtered" class="filter_clear" ng-click="clearAllFilters()">reset all</span>

    </div>
</div>

<table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
    <tr>
        <td width='10%' >
            <span ng-model='fro_date_slct' ng-init='fro_date_slct={!! json_encode($months_by_years) !!}'></span>
            <select ng-model="fro_date" ng-init="fro_date='all'">
                <option value='all'>FROM DATE</option>
                <optgroup class="ng-cloak" ng-repeat="(yr,mths) in fro_date_slct | orderBy:'-yr'" label="<% yr %>">
                    <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %><% mth %>"> 
                        <% month_labels[mth] %> '<% yr|slice:-2 %>
                    </option>
                </optgroup>
            </select>
        </td>
        <td width='10%' >
            <span ng-model='to_date_slct' ng-init='to_date_slct={!! json_encode($months_by_years) !!}'></span>
            <select ng-model="to_date" ng-init="to_date='all'" ng-change="dateFilter(   )">
                <option value='all'>TO DATE</option>
                <optgroup class="ng-cloak" ng-repeat="(yr,mths) in to_date_slct" label="<% yr %>">
                    <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %><% mth %>"> 
                        <% month_labels[mth] %> '<% yr|slice:-2 %>
                    </option>
                </optgroup>
            </select>
        </td>
                
    </tr>
</table>