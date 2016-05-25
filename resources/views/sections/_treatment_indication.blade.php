<div class='row'>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="cd4_less_than_500=0" ng-model='cd4_less_than_500'>
            <% ((cd4_less_than_500/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>CD4 < 500</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="pmtct_option_b_plus=0" ng-model='pmtct_option_b_plus'>
            <% ((pmtct_option_b_plus/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>PMTCT/OPTION B+</font>            
    </div>       
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="children_under_15=0" ng-model="children_under_15">
            <% ((children_under_15/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>CHILDREN UNDER 15</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="other_treatment=0" ng-model="other_treatment">
            <% ((other_treatment/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>OTHER</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="treatment_blank_on_form=0" ng-model="treatment_blank_on_form">
            <% ((treatment_blank_on_form/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>BLANK ON FORM</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak' ng-init="tb_infection=0" ng-model="tb_infection">
            <% ((tb_infection/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>TB INFECTION</font>            
    </div>

</div>