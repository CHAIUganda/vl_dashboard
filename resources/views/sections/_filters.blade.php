<span ng-model="month_labels" ng-init='month_labels={!! json_encode(MyHTML::months()) !!}'></span>
<span ng-model="filtered" ng-init='filtered=false'></span>
<span class="hdr hdr-grey" style="float:right;font-size:11px"><% data_date %></span><br>

<div class='row'>
    <div class='col-md-1' style="padding-top:17px; font-size:bolder">
        <span class='hdr hdr-grey'>FILTERS:</span>
    </div>
     
    <div class="filter-section col-md-9">        
        <span ng-model='filter_duration' ng-init='filter_duration={!! json_encode($init_duration) !!};init_duration={!! json_encode($init_duration) !!};'>
          <span class="filter-val ng-cloak">
            <% filter_duration[0] |d_format %> - <% filter_duration[filter_duration.length-1] | d_format %> 
        </span>
        </span>
        &nbsp;

        <span ng-model='filter_districts' ng-init='filter_districts={}'>
            <span ng-repeat="(d_nr,d_name) in filter_districts"> 
                <span class="filter-val ng-cloak"> <% d_name %> (d) <x ng-click='removeTag("district",d_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_hubs' ng-init='filter_hubs={}'>
            <span ng-repeat="(h_nr,h_name) in filter_hubs">
                <span class="filter-val ng-cloak"> <% h_name %> (h) <x ng-click='removeTag("hub",h_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_age_group' ng-init='filter_age_group={}'>
            <span ng-repeat="(ag_nr,ag_name) in filter_age_group">
                <span class="filter-val ng-cloak"> <% ag_name %> (a) <x ng-click='removeTag("age_group",ag_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-show="filtered" class="filter_clear" ng-click="clearAllFilters()">reset all</span>

    </div>
</div>

<table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
    <tr>
        <td width='20%' >
            <span ng-model='fro_date_slct' ng-init='fro_date_slct={!! json_encode($months_by_years) !!}'></span>
            <select ng-model="fro_date" ng-init="fro_date='all'" ng-change="dateFilter('fro')">
                <option value='all'>FROM DATE</option>
                <optgroup class="ng-cloak" ng-repeat="(yr,mths) in fro_date_slct | orderBy:'-yr'" label="<% yr %>">
                    <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %>-<% mth %>"> 
                        <% month_labels[mth] %> '<% yr|slice:-2 %>
                    </option>
                </optgroup>
            </select>
        </td>
        <td width='20%' >
            <span ng-model='to_date_slct' ng-init='to_date_slct={!! json_encode($months_by_years) !!}'></span>
            <select ng-model="to_date" ng-init="to_date='all'" ng-change="dateFilter('to');getData()">
                <option value='all'>TO DATE</option>
                <optgroup class="ng-cloak" ng-repeat="(yr,mths) in to_date_slct" label="<% yr %>">
                    <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %>-<% mth %>"> 
                        <% month_labels[mth] %> '<% yr|slice:-2 %>
                    </option>
                </optgroup>
            </select>
        </td>
        <td width='20%' id='dist_elmt'>
            <select ng-model="district" ng-init="district='all'" ng-change="filter('district')">
                <option value='all'>DISTRICTS</option>
                <option class="ng-cloak" ng-repeat="dist in districts2 | orderBy:'name'" value="<% dist.id %>">
                    <% dist.name %>
                </option>
            </select>
        </td>
        <td width='20%' id='dist_elmt'>
            <select ng-model="hub" ng-init="hub='all'" ng-change="filter('hub')">
                <option value='all'>HUBS</option>
                <option class="ng-cloak" ng-repeat="hb in hubs2|orderBy:'name'" value="<% hb.id %>">
                    <% hb.name %>
                </option>
            </select>
        </td>
        <td width='20%' id='dist_elmt'>
            <select ng-model="age_group" ng-init="age_group='all'" ng-change="filter('age_group')">
                <option value='all'>AGE GROUP</option>
                <option class="ng-cloak" ng-repeat="(ag_nr,ag) in age_group_slct" value="<% ag_nr %>">
                    <% ag %>
                </option>
            </select>
        </td>

         
    </tr>
</table>
