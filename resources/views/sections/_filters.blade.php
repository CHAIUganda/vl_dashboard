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

         <span ng-model='filter_gender' ng-init='filter_gender={}'>
            <span ng-repeat="(g_nr,g_name) in filter_gender">
                <span class="filter-val ng-cloak"> <% g_name %> (g) <x ng-click='removeTag("gender",g_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_regimen' ng-init='filter_regimen={}'>
            <span ng-repeat="(r_nr,r_name) in filter_regimen">
                <span class="filter-val ng-cloak"> <% r_name %> (g) <x ng-click='removeTag("regimen",r_nr)'>&#120;</x></span> 
            </span>
        </span>

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
            <select ng-model="to_date" ng-init="to_date='all'" ng-change="dateFilter()">
                <option value='all'>TO DATE</option>
                <optgroup class="ng-cloak" ng-repeat="(yr,mths) in to_date_slct" label="<% yr %>">
                    <option class="ng-cloak" ng-repeat="mth in mths" value="<% yr %><% mth %>"> 
                        <% month_labels[mth] %> '<% yr|slice:-2 %>
                    </option>
                </optgroup>
            </select>
        </td>
        <td width='10%' id='dist_elmt'>
            <select ng-model="district" ng-init="district='all'" ng-change="filter('district')">
                <option value='all'>DISTRICTS</option>
                <option class="ng-cloak" ng-repeat="dist in districts2 | orderBy:'name'" value="<% dist.id %>">
                    <% dist.name %>
                </option>
            </select>
        </td>
        <td width='10%' id='dist_elmt'>
            <select ng-model="hub" ng-init="hub='all'" ng-change="filter('hub')">
                <option value='all'>HUBS</option>
                <option class="ng-cloak" ng-repeat="hb in hubs2|orderBy:'name'" value="<% hb.id %>">
                    <% hb.name %>
                </option>
            </select>
        </td>
        <td width='10%' id='dist_elmt'>
            <select ng-model="age_group" ng-init="age_group='all'" ng-change="filter('age_group')">
                <option value='all'>AGE GROUP</option>
                <option class="ng-cloak" ng-repeat="(ag_nr,ag) in labels.age_grps" value="<% ag_nr %>">
                    <% ag %>
                </option>
            </select>
        </td>
        <td width='10%' id='dist_elmt'>
            <select ng-model="gender" ng-init="gender='all'" ng-change="filter('gender')">
                <option value='all'>GENDER</option>
                <option class="ng-cloak" ng-repeat="(g_nr,gnd) in labels.genders" value="<% g_nr %>">
                    <% gnd %>
                </option>
            </select>
        </td>
        <td width='10%' id='dist_elmt'>
            <select ng-model="regimen" ng-init="regimen='all'" ng-change="filter('regimen')">
                <option value='all'>REGIMEN</option>
                <option class="ng-cloak" ng-repeat="(r_nr,rg) in labels.reg_grps" value="<% r_nr %>">
                    <% rg %>
                </option>
            </select>
        </td>        
    </tr>
</table>
