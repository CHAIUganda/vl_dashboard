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

        <span ng-model='filtered_age_range' ng-init='filtered_age_range=[]'>
            <span ng-repeat="filtered_age_range_instance in filtered_age_range" ng-init="age_range_index = ageRangesCount()">
                <span class="filter-val ng-cloak"> <% filtered_age_range_instance.from_age %> 
                    - <% filtered_age_range_instance.to_age %>
                    (yrs) <x ng-click='filtered_age_range.splice($index, 1)'>&#120;</x>
                </span> 
            </span>
        </span>

         <span ng-model='filter_gender' ng-init='filter_gender={}'>
            <span ng-repeat="(g_nr,g_name) in filter_gender">
                <span class="filter-val ng-cloak"> <% g_name %> (s) <x ng-click='removeTag("gender",g_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_regimen' ng-init='filter_regimen={}'>
            <span ng-repeat="(r_nr,r_name) in filter_regimen">
                <span class="filter-val ng-cloak"> <% r_name %> (r) <x ng-click='removeTag("regimen",r_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_line' ng-init='filter_line={}'>
            <span ng-repeat="(l_nr,l_name) in filter_line">
                <span class="filter-val ng-cloak"> <% l_name %> (l) <x ng-click='removeTag("line",l_nr)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_indication' ng-init='filter_indication={}'>
            <span ng-repeat="(i_nr,i_name) in filter_indication">
                <span class="filter-val ng-cloak"> <% i_name %> (p) <x ng-click='removeTag("indication",i_nr)'>&#120;</x></span> 
            </span>
        </span>
        
        <span ng-model='filter_emtct' ng-init='filter_emtct={}'>
            <span ng-repeat="(emtct_key,emtct_value) in filter_emtct">
                <span class="filter-val ng-cloak"> <% emtct_value %> (e) <x ng-click='removeTag("emtct",emtct_key)'>&#120;</x></span> 
            </span>
        </span>

        <span ng-model='filter_tb_status' ng-init='filter_tb_status={}'>
            <span ng-repeat="(tb_status_key,tb_status_value) in filter_tb_status">
                <span class="filter-val ng-cloak"> <% tb_status_value %> (t) <x ng-click='removeTag("tb_status",tb_status_key)'>&#120;</x></span> 
            </span>
        </span>
        <span ng-show="filtered" class="filter_clear" ng-click="clearAllFilters()">reset all</span>

    </div>
</div>

<table border='1' cellpadding='0' cellspacing='0' class='filter-tb'>
    <tr>
        <td width='9%' >
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
        <td width='9%' >
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
        <td width='9%' id='dist_elmt'>
            <select ng-model="district" ng-init="district='all'" ng-change="filter('district')">
                <option value='all'>DISTRICTS</option>
                <option class="ng-cloak" ng-repeat="dist in districts2 | orderBy:'name'" value="<% dist.id %>">
                    <% dist.name %>
                </option>
            </select>
        </td>
        <td width='9%' id='dist_elmt'>
            <select ng-model="hub" ng-init="hub='all'" ng-change="filter('hub')">
                <option value='all'>HUBS</option>
                <option class="ng-cloak" ng-repeat="hb in hubs2|orderBy:'name'" value="<% hb.id %>">
                    <% hb.name %>
                </option>
            </select>
        </td>
        <td width='9%' id='dist_elmt'>
            <select ng-model="from_age" ng-init="from_age='all'">
                <option value='all'>From Age</option>
                <option class="ng-cloak" ng-repeat="fro_age in labels.from_age " value="<% fro_age %>">
                    <% fro_age %>
                </option>
            </select>

        </td>
        <td width='9%' id='dist_elmt'>
            <select ng-model="to_age" ng-init="to_age='all'" ng-change="filter('age_range')">
                <option value='all'>To Age</option>
                <option class="ng-cloak" ng-repeat="to_age in labels.to_age " value="<% to_age %>">
                    <% to_age %>
                </option>
            </select>

        </td>
        <td width='9%' id='dist_elmt'>
            <select ng-model="gender" ng-init="gender='all'" ng-change="filter('gender')">
                <option value='all'>SEX</option>
                <option class="ng-cloak" ng-repeat="(g_nr,gnd) in labels.genders" value="<% g_nr %>">
                    <% gnd %>
                </option>
            </select>
        </td>
        <td width='9%' id='dist_elmt'>
            <select ng-model="regimen" ng-init="regimen='all'" ng-change="filter('regimen')">
                <option value='all'>REGIMEN</option>
                <option class="ng-cloak" ng-repeat="reg in labels.regimens" value="<% reg.id %>">
                    <% reg.name %>
                </option>
            </select>
        </td>   

        <td width='9%' id='dist_elmt'>
            <select ng-model="line" ng-init="line='all'" ng-change="filter('line')">
                <option value='all'>LINE</option>
                <option class="ng-cloak" ng-repeat="(l_nr,l) in labels.lines" value="<% l_nr %>">
                    <% l %>
                </option>
            </select>
        </td>
  
        <td width='9%'>
            <select ng-model="emtct" ng-init="emtct='all'" ng-change="filter('emtct')">
                <option value='all'>eMTCT</option>
                <option class="ng-cloak" ng-repeat="(emtct_value,emtct_key) in labels.emtct" value="<% emtct_value %>">
                    <% emtct_key %>
                </option>
            </select>
        </td> 
      
       <td width='9%'>
            <select ng-model="tb_status" ng-init="tb_status='all'" ng-change="filter('tb_status')">
                <option value='all'>TB STATUS</option>
                <option class="ng-cloak" ng-repeat="(tb_status_value,tb_status_key) in labels.tb_status" value="<% tb_status_value %>">
                    <% tb_status_key %>
                </option>
            </select>
        </td>
    </tr>
</table>