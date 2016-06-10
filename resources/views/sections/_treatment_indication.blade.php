<div class='row' ng-model="t_indications">
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[3]/samples_received)*100)|number:1 %>%
        </font><br>

        <font class='addition-metrics desc'>CD4 < 500</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[1]/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>PMTCT/OPTION B+</font>            
    </div>       
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[2]/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>CHILDREN UNDER 15</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[5]/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>OTHER</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[0]/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>BLANK ON FORM</font>            
    </div>
    <div class='col-sm-2'>
        <font class='addition-metrics figure ng-cloak'>
            <% ((t_indications[4]/samples_received)*100)|number:1 %>%
        </font><br>
        <font class='addition-metrics desc'>TB INFECTION</font>            
    </div>

</div>