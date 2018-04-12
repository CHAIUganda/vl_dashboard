
//angular stuff

/*
Authors
Name                        @       Period      Role       
Logan Smith                 CHAI    2015(v1)    Interface Design, Q/A
Lena Derisavifard           CHAI    2015(v1)    Req Specification, Q/A, UAT
Kitutu Paul                 CHAI    2015(v1)    System development
Sam Otim                    CHAI    2015(v1)    System development
Simon Peter Muwanga         METS/MUSPH 2016(v2) System development
Credit to CHAI Uganda,METS/MUSPH, CPHL and stakholders
*/
var $injector = angular.injector();

var app=angular.module('dashboard', ['datatables','ngSanitize', 'ngCsv'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    });

app.filter('ssplit', function() {
        return function(input, splitChar,splitIndex) {
            // do some bounds checking here to ensure it has that index
            var arr=input.split(splitChar);
            return arr[splitIndex];
        }
    });

app.filter('slice', function() {
        return function(input, length) {
            return input.slice(length);
        }
    });

app.filter('d_format', function() {
        return function(y_m) {
            var y_m=isNaN(y_m)?y_m:y_m.toString();
            var month_labels={'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sept','10':'Oct','11':'Nov','12':'Dec'};
            return month_labels[y_m.slice(-2)]+" '"+y_m.slice(2,4);
        }
    });


var ctrllers={};

ctrllers.DashController = function($scope,$http){
   /* $scope.dtOptions = DTOptionsBuilder.newOptions()
            .withPaginationType('full_numbers')
            .withBootstrap()
            .withButtons([
                'csv',  'excel']);*/

    $scope.identity = angular.identity;
    $scope.params = {
        'districts':[],'hubs':[],'age_ranges':[],'genders':[],
        'regimens':[],'lines':[], 'indications': [],'emtct':[],'tb_status':[]};

    var hubs_json = {};
    var age_group_json = {1:"0<2",2:"2-<5",3:"5-<10",4:"10-<15",5:"15-<20",6:"20-<25",7:"25+"};  

    var generateFromAge = function(){
        var json_array={};
        for(var i=0; i<100; i++){
            var key=i;
            if(i < 10){
                key="0"+i;
            }
            
            json_array[key]=i;
        }

        return json_array;
    };
    var from_age_json =generateFromAge();

    var generateToAge = function(){
        var json_array={};
        for(var i=1; i<100; i++){
            var key=i;
            if(i < 10){
                key="0"+i;
            }
            
            json_array[key]=i;
        }

        return json_array;
    };
    var to_age_json =generateToAge();
    var regimen_groups_json = {1: 'AZT based', 2: 'ABC based', 3: 'TDF based', 4: 'Other'};
    var regimen_times_json = {0:'No Date Given',1:'6-12 months',2:'1-2 years',3:'2-3 years',4:'3-5 years',5:'5+ years'};    
    var results_json = {}; //to hold a big map will all processed data to later on be used in the generalFilter
    var genders_json = {'m':'Male','f':'Female','x':'Unknown'};
    var lines_json = {1:'1st Line',2:'2nd Line',4:'Left Blank',5:'Other'};
    var t_indication_json = {1: "PMTCT/OPTION B+", 4:"TB INFECTION"};

    //var emtct_json = {1:"PREGNANT",2:"BREAST FEEDING",3: "PMTCT/OPTION B+"};
    //var tb_status_json = {1:"Active on TB",2:"Not Active on TB",3:"Left Blank"};
    var emtct_json = {1:"Pregnant",2:"Breastfeeding",3: "Initiated ART because of PMTCT"};
    var tb_status_json = {1:"Active TB",2:"No TB",3:"Left Blank"};
    

    $scope.month_labels = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sept','10':'Oct','11':'Nov','12':'Dec'};

    $scope.labels = {};
    $scope.labels.reg_grps = regimen_groups_json;
    $scope.labels.reg_times = regimen_times_json;
    $scope.labels.age_grps = age_group_json;
    $scope.labels.genders = genders_json;
    $scope.labels.lines = lines_json;
    $scope.labels.districts = [];

    $scope.labels.facilities = [];
    $scope.labels.facilities_details = [];

    $scope.labels.regimens = [];
    $scope.labels.regimens2 = [];

    $scope.labels.indications = t_indication_json;
    $scope.labels.emtct = emtct_json;
    $scope.labels.tb_status=tb_status_json;

    $scope.labels.from_age = from_age_json;
    $scope.labels.to_age = to_age_json;
    $scope.filter_from_age = from_age_json;
    $scope.filter_to_age = to_age_json;
    $scope.filtered_age_range = [];
    $scope.to_age = null;
    $scope.from_age = null;
    $scope.ageRangesCounter=0;
    

    var vvvrrr = 0;

    $scope.districts2 = [];
    $scope.hubs2 = [];
    $scope.hubs = [];
    $scope.age_group_slct = age_group_json;


   /* $scope.orderByCurrentRegimen = function(regimen){
        if($scope.labels.reg_grps[regimen._id] == 'ABC')
            return 1;
        else if($scope.labels.reg_grps[regimen._id] == 'AZT')
            return 2;
        else if($scope.labels.reg_grps[regimen._id] == 'TDF/XTC/ATV/r')
            return 3;
        else if($scope.labels.reg_grps[regimen._id] == 'TDF/XTC/EFV')
            return 4;
         else if($scope.labels.reg_grps[regimen._id] == 'TDF/XTC/LPV/r')
            return 5;
         else if($scope.labels.reg_grps[regimen._id] == 'TDF/XTC/NVP')
            return 6;
        else if($scope.labels.reg_grps[regimen._id] == 'Other')
            return 7;

    };*/

   /* $scope.orderByCurrentRegimen = function(regimen){
        if($scope.labels.reg_grps[regimen._id] == 'ABC based')
            return 1;
        else if($scope.labels.reg_grps[regimen._id] == 'AZT based')
            return 2;
        else if($scope.labels.reg_grps[regimen._id] == 'TDF based')
            return 3;
        else if($scope.labels.reg_grps[regimen._id] == 'Other')
            return 4;
    };*/
    

    $http.get("/other_data/").success(function(data){
        //console.log("Ehealth at chai rocks 1 "+JSON.stringify(data.facilities));
        for(var i in data.districts){
            var obj = data.districts[i];
            $scope.labels.districts[obj.id] = obj.name||"no district";

            $scope.districts2.push({"id":obj.id,"name":obj.name});
        }

        for(var i in data.hubs){
            var obj = data.hubs[i];
            hubs_json[obj.id] = obj.name;
            $scope.hubs2.push({"id":obj.id,"name":obj.name});
        }

        for(var i in data.new_hubs){
            var obj = data.new_hubs[i];
            
            $scope.hubs.push({"id":obj.id,"name":obj.hub});
        }

        for(var i in data.facilities){
            var obj = data.facilities[i];
            //facilities_json[f.id]={'name':f.name,'district_id':f.district_id,'hub_id':f.hub_id};
            $scope.labels.facilities[obj.id] = obj.name||"no facility";
            var facility_object = {
                id:obj.id,
                cphl_name:obj.name,
                dhis2_name:obj.dhis2_name,
                hub_id:obj.hub_id,
                ip_id:obj.ip_id,
                district_id:obj.district_id,
                dhis2_uid:obj.dhis2_uid,
                district_uid:obj.district_uid
            };
            $scope.labels.facilities_details[obj.id] = facility_object||"no facility";
            
        }

        for(var i in data.regimens){
            var obj = data.regimens[i];
            $scope.labels.regimens.push({"id":obj.id,"name":obj.name});
            $scope.labels.regimens2[obj.id] = obj.name;
        }
    });
    
    var convertAgeRangesToAgeIds=function(scopeAgeRangesParam){
        var age_ranges_array = scopeAgeRangesParam;
        var age_ids_array=[];
        for (var i = 0; i<age_ranges_array.length ; i++) {
            var from_age_value = parseInt(age_ranges_array[i].from_age);
            var to_age_value = parseInt(age_ranges_array[i].to_age);
            var age_range = to_age_value - from_age_value;
            
            
            
            if(age_range == 1){//to_age becomes the id. This is what we put in the mongoDB.
                
                age_ids_array.push(from_age_value);
                age_ids_array.push(to_age_value);

            }else if(age_range == 0){
                age_ids_array.push(to_age_value);
            }
            else if(age_range > 1){
                var age_range_id = from_age_value;

                for(var age_index=0; age_index <= age_range; age_index++){
                   
                    age_ids_array.push(age_range_id);
                    age_range_id++;
                }//end inner loop
            }
        }//end outer loop

        return age_ids_array;
    };

    var getTreatmentIndicationsFromEmtctParam=function(emtctScopeParam){//skipped because TWG needed a union, not an intersection
        var emtct_ids_array = emtctScopeParam;
        var treatment_indication_mongo_keys_array=[];
        for (var i = 0; i < emtct_ids_array.length; i++) {
            if(emtct_ids_array[i] == 3){//
               // treatment_indication_mongo_keys_array.push(1);//PMTCT/Option B+
            }
        };

        return treatment_indication_mongo_keys_array;
    };
    var convertEmtctIdsToMongoKeys=function(emtctScopeParam){
        
        var emtct_ids_array = emtctScopeParam;
        var emtct_mongo_keys_array=[];
        for (var i = 0; i < emtct_ids_array.length; i++) {
            if(emtct_ids_array[i] == 1){
                emtct_mongo_keys_array.push("pregnancy_status");
            }else if (emtct_ids_array[i] == 2) {
                emtct_mongo_keys_array.push("breast_feeding_status");
            }else if(emtct_ids_array[i] == 3){
                emtct_mongo_keys_array.push("initiated_art_because_pmtct");
            }
        };

        return emtct_mongo_keys_array;
    };

    var convertTbStatusIdsToMongoKeys=function(tbStatusScopeParam){
        var tb_status_ids_array = tbStatusScopeParam;
        var tb_status_mongo_keys_array=[];
        for(var i=0; i < tb_status_ids_array.length; i++){
            var dummy_value=parseInt(tb_status_ids_array[i]);
            if(dummy_value == parseInt("1")){
                tb_status_mongo_keys_array.push("y");
            }else if(dummy_value == parseInt("2")){
                tb_status_mongo_keys_array.push("n");
            }else if(dummy_value == parseInt("3")){
                tb_status_mongo_keys_array.push("x");
            }
        }

        return tb_status_mongo_keys_array;
    };

    var getData=function(){
            $scope.loading = true;
            var prms = {};
            prms.districts = JSON.stringify($scope.params.districts);
            prms.hubs = JSON.stringify($scope.params.hubs);
            prms.age_ids = JSON.stringify(convertAgeRangesToAgeIds($scope.params.age_ranges)); 
            prms.genders = JSON.stringify($scope.params.genders);
            prms.regimens = JSON.stringify($scope.params.regimens);
            prms.lines = JSON.stringify($scope.params.lines);
            //prms.indications = JSON.stringify($scope.params.indications);
            prms.indications = JSON.stringify(getTreatmentIndicationsFromEmtctParam($scope.params.emtct));
            prms.emtct = JSON.stringify(convertEmtctIdsToMongoKeys($scope.params.emtct));
            prms.tb_status = JSON.stringify(convertTbStatusIdsToMongoKeys($scope.params.tb_status));
            prms.fro_date = $scope.fro_date;
            prms.to_date = $scope.to_date;
           
            $http({method:'GET',url:"/live/",params:prms}).success(function(data) {
                
                //console.log("we rrrr"+JSON.stringify($scope.params));

                $scope.samples_received = data.whole_numbers.samples_received||0;
                $scope.suppressed = data.whole_numbers.suppressed||0;
                $scope.valid_results = data.whole_numbers.valid_results||0;

                $scope.rejected_samples = data.whole_numbers.rejected_samples||0;  

                $scope.t_indications = data.t_indication; 

                $scope.duration_numbers = data.drn_numbers||{};
                $scope.facility_numbers = data.f_numbers||{};
                $scope.export_facility_numbers = exportFacilityNumbers($scope);
                $scope.district_numbers = data.dist_numbers||{};

                $scope.export_district_numbers = exportDistrictNumbers($scope);
                $scope.export_district_suppression_numbers = exportDistrictSuppressionNumbers($scope);
                $scope.export_facility_suppression_numbers = exportFacilitySuppressionNumbers($scope);
                $scope.export_district_rejection_numbers = exportDistrictRejectionNumbers($scope);
                $scope.export_facility_rejection_numbers = exportFacilityRejectionNumbers($scope);
                
                $scope.current_timestamp = getCurrentTimeStamp();

                //$scope.regimen_group_numbers=data.reg_groups||{};
                $scope.regimen_numbers = data.regimen_numbers||{};
                $scope.export_regimen_numbers = exportRegimenNumbers($scope);

                //console.log("reg"+JSON.stringify($scope.regimen_numbers))
                $scope.regimen_time_numbers = data.reg_times||{};
                $scope.export_duration_on_art = exportDurationOnArt($scope);

                $scope.line_numbers = data.line_numbers||{};
                $scope.regimenLineOfTreatment=generateRegimenLineOfTreatment(data);
                $scope.firstLineRegimens = generateFirstLineRegimens(data);
                $scope.secondLineRegimens = generateSecondLineRegimens(data);
                $scope.otherRegimens=generateOtherRegimens(data);

               //console.log("lajejdieorer: "+JSON.stringify($scope.regimen_group_numbers));

                $scope.displaySamplesRecieved(); //to display the samples graph - for the first time
                $scope.displaySupressionRate();
                $scope.displayRejectionRate();
                $scope.displayRegimenGroups();
                $scope.displayRegimenTime();

                $scope.filtered = count($scope.filter_districts)>0||count($scope.filter_hubs)>0||count($scope.filtered_age_range)>0||$scope.date_filtered;    
                $scope.loading = false;
                

                //transposeDurationNumbers();
                //console.log("lalallalal:: samples_received:: "+data.samples_received+" suppressed:: "+data.suppressed+" "+data.valid_results);
            });
    };

    getData();    

    var generateRegimenLineOfTreatment = function(data){
        var regimenLineOfTreatmentObject = null;
        var regimenLineOfTreatmentArray =[]; 
        regimenLineOfTreatmentArray = data.regimen_by_line_of_treatment;
        
        var firstLineNotSuppressed=0;
        var firstLineSuppressed=0;
        var secondLineNotSuppressed=0;
        var secondLineSuppressed=0;
        var otherLineNotSuppressed=0;
        var otherLineSuppressed=0;

        var firstLine=0;
        var secondLine=0;
        var otherLine=0;

        var notSuppressed=0;
        var suppressed=0;

        var total=0;
        //regimenLineOfTreatmentObject = {firstLineNS:};
        for(var index=0; index< regimenLineOfTreatmentArray.length; index++){
           var dummy_egimenLineOfTreatment = regimenLineOfTreatmentArray[index];
           if(dummy_egimenLineOfTreatment._id = 1){
                firstLineSuppressed = dummy_egimenLineOfTreatment.suppressed;
                firstLineNotSuppressed = dummy_egimenLineOfTreatment.valid_results - firstLineSuppressed;
           }else if(dummy_egimenLineOfTreatment._id = 2){
                secondLineSuppressed = dummy_egimenLineOfTreatment.suppressed;
                secondLineNotSuppressed = dummy_egimenLineOfTreatment.valid_results - secondLineSuppressed;
           }else if(dummy_egimenLineOfTreatment._id = 5){
                otherLineSuppressed = dummy_egimenLineOfTreatment.suppressed;
                otherLineNotSuppressed = dummy_egimenLineOfTreatment.valid_results - otherLineSuppressed;
           }
        }

        firstLine = firstLineSuppressed+firstLineNotSuppressed;
        secondLine = secondLineSuppressed+secondLineNotSuppressed;
        otherLine = otherLineSuppressed + otherLineNotSuppressed;

        notSuppressed = firstLineNotSuppressed+secondLineNotSuppressed+otherLineNotSuppressed;
        suppressed = firstLineSuppressed+secondLineSuppressed+otherLineSuppressed;

        total=notSuppressed+suppressed;

        regimenLineOfTreatmentObject = {"firstLineSuppressed":firstLineSuppressed,"firstLineNotSuppressed":firstLineNotSuppressed,
                                        "secondLineSuppressed":secondLineSuppressed,"secondLineNotSuppressed":secondLineNotSuppressed,
                                        "otherLineSuppressed":otherLineSuppressed,"otherLineNotSuppressed":otherLineNotSuppressed,
                                        "firstLine":firstLine,"secondLine":secondLine,"otherLine":otherLine,
                                        "notSuppressed":notSuppressed,"suppressed":suppressed,"total":total};
        return regimenLineOfTreatmentObject;
    };
    var generateFirstLineRegimens = function(data){
        //pick first line regimens
        var regimenNamesArray = data.regimen_names;
        var regimenNumbersArray = data.regimen_numbers;
        //generate object with firstline regimens only
        var one_c_AZT_3TC_NVP=1;
        var one_d_AZT_3TC_EFV=2;
        var one_e_TDF_3TC_NVP=3;
        var one_f_TDF_3TC_EFV=4;
        var one_g_TDF_FTC_NVP=5;
        var one_h_TDF_FTC_EFV=6;
        var one_i_ABC_3TC_EFV=7;
        var one_j_ABC_3TC_NVP=8;
        var four_a_d4T_3TC_NVP=19, four_b_d4T_3TC_EFV=20, four_c_AZT_3TC_NVP=21, four_d_AZT_3TC_EFV=22;
        var four_e_ABC_3TC_NVP=23, four_f_ABC_3TC_EFV=24;

        var regimens ={"one_c":getRegimen(data,one_c_AZT_3TC_NVP),"one_d":getRegimen(data,one_d_AZT_3TC_EFV),
                        "one_e":getRegimen(data,one_e_TDF_3TC_NVP),"one_f":getRegimen(data,one_f_TDF_3TC_EFV),
                        "one_g":getRegimen(data,one_g_TDF_FTC_NVP),"one_h":getRegimen(data,one_h_TDF_FTC_EFV),
                        "one_i":getRegimen(data,one_i_ABC_3TC_EFV),"one_j":getRegimen(data,one_j_ABC_3TC_NVP),
                        "four_a":getRegimen(data,four_a_d4T_3TC_NVP),"four_b":getRegimen(data,four_b_d4T_3TC_EFV),
                        "four_c":getRegimen(data,four_c_AZT_3TC_NVP),"four_d":getRegimen(data,four_d_AZT_3TC_EFV),
                        "four_e": getRegimen(data,four_e_ABC_3TC_NVP), "four_f":getRegimen(data,four_f_ABC_3TC_EFV)};
        return regimens;

    };

    var generateSecondLineRegimens = function(data){
        
        var two_b_TDF_3TC_LPV_r=11;
        var two_c_TDF_FTC_LPV_r=12;
        var two_e_AZT_FTC_NVP=13;
        var two_f_TDF_FTC_ATV_r=14;

        var two_g_TDF_3TC_ATV_r=15;
        var two_h_AZT_3TC_ATV_r=16;
        var two_i_ABC_3TC_LPV_r=17;
        var two_j_ABC_3TC_ATV_r=18;

        var five_d_TDF_3TC_LPV_r=25;
        var five_e_TDF_FTC_LPV_r=26;
        var five_g_AZT_ABC_LPV_r=27;
        var five_i_AZT_3TC_ATV_r=28;
        var five_j_ABC_3TC_LPV_r=29;
        var five_k_ABC_3TC_ATV_r=30;

        var regimens ={"two_b":getRegimen(data,two_b_TDF_3TC_LPV_r),"two_c":getRegimen(data,two_c_TDF_FTC_LPV_r),
                        "two_e": getRegimen(data,two_e_AZT_FTC_NVP),"two_f":getRegimen(data,two_f_TDF_FTC_ATV_r),
                      "two_g":getRegimen(data,two_g_TDF_3TC_ATV_r),"two_h":getRegimen(data,two_h_AZT_3TC_ATV_r),
                      "two_i":getRegimen(data,two_i_ABC_3TC_LPV_r), "two_j":getRegimen(data,two_j_ABC_3TC_ATV_r),
                      "five_d":getRegimen(data,five_d_TDF_3TC_LPV_r),"five_e":getRegimen(data,five_e_TDF_FTC_LPV_r),
                      "five_g":getRegimen(data,five_g_AZT_ABC_LPV_r),"five_i":getRegimen(data,five_i_AZT_3TC_ATV_r),
                      "five_j":getRegimen(data,five_j_ABC_3TC_LPV_r),"five_k":getRegimen(data,five_k_ABC_3TC_ATV_r)};
        return regimens;

    };

    var generateOtherRegimens = function(data){

        var left_blank=31;
        var other_regimen=71;
        var regimens ={"left_blank":getRegimen(data,left_blank),"other_regimen":getRegimen(data,other_regimen)};
        return regimens;
    };
  
    var getRegimen=function(data,regimenIdentifier){//treatmentStatusID:it is 
        var regimenNumbersArray = data.regimen_numbers;
        var regimenID = regimenIdentifier;//database id for both mongo and mysql
        var regimenObject ={};
        var suppressed=0;
        var notSuppressed=0;
        var suppressedPercentage=0;
        var notSuppressedPercentage=0;

        for(var index=0; index < regimenNumbersArray.length; index++){
            var dummyRegimenNumber = regimenNumbersArray[index];
            if(dummyRegimenNumber._id == regimenID){
                suppressed = dummyRegimenNumber.suppressed;
                suppressedPercentage = (suppressed/dummyRegimenNumber.valid_results)*100 ;

                notSuppressed = dummyRegimenNumber.valid_results - suppressed;
                notSuppressedPercentage = 100 - suppressedPercentage;
                break;
            }
        }

        regimenObject = {"suppressed":suppressed, "suppressedPercentage":suppressedPercentage,
                        "notSuppressed":notSuppressed,"notSuppressedPercentage":notSuppressedPercentage};
        return regimenObject;
    };
   
    
    $scope.testClick = function(){
        $scope.getArray = [{a: 1, b:2}, {a:3, b:4}];
    }

    $scope.dateFilter=function(){
        
        if($scope.fro_date!="all" && $scope.to_date!="all"){
            var fro_nr=Number($scope.fro_date);//numberise the fro date
            var to_nr=Number($scope.to_date);//numberise the to date

            if(fro_nr>to_nr){
                alert("Please make sure that the fro date is earlier than the to date");
            }else{
                $scope.date_filtered=true;
                $scope.fro_date_label=$scope.fro_date;
                $scope.to_date_label=$scope.to_date;
                getData();
            }
        }
    }
    


    $scope.filter=function(mode){
        switch(mode){
            case "district":
            $scope.filter_districts[$scope.district]=$scope.labels.districts[$scope.district];
            $scope.params.districts.push(Number($scope.district));
            $scope.district='all';            
            break;

            case "hub":
            $scope.filter_hubs[$scope.hub]=hubs_json[$scope.hub];
            $scope.params.hubs.push(Number($scope.hub));
            $scope.hub='all';
            break;

            case "age_range":
            //--validate

            //push
            var age_range = {"from_age":$scope.from_age,"to_age":$scope.to_age};
            if(isAgeRageValid(age_range)){
                $scope.filtered_age_range.push(age_range);
                $scope.params.age_ranges.push(age_range);
            }else{
                alert("Please make sure your range selection is realistic");
            }
            
            $scope.from_age="all";
            $scope.to_age="all";
            break;

            case "gender":
            $scope.filter_gender[$scope.gender]=genders_json[$scope.gender];
            $scope.params.genders.push($scope.gender);
            $scope.gender='all';
            break;

            case "regimen":
            $scope.filter_regimen[$scope.regimen]=regimen_groups_json[$scope.regimen];
            $scope.params.regimens.push(Number($scope.regimen));
            $scope.regimen='all';
            break;

            case "line":
            $scope.filter_line[$scope.line]=lines_json[$scope.line];
            $scope.params.lines.push(Number($scope.line));
            $scope.line='all';
            break;

            case "indication":
            $scope.filter_indication[$scope.indication] = t_indication_json[$scope.indication];
            $scope.params.indications.push(Number($scope.indication));
            $scope.indication='all';
            break;

            case "emtct":
            $scope.filter_emtct[$scope.emtct] = emtct_json[$scope.emtct];
            $scope.params.emtct.push(Number($scope.emtct));
            $scope.emtct='all';
            break;

            case "tb_status":
            $scope.filter_tb_status[$scope.tb_status] = tb_status_json[$scope.tb_status];
            $scope.params.tb_status.push(Number($scope.tb_status));
            $scope.tb_status='all';
            break;
        }

        delete $scope.filter_districts["all"];
        delete $scope.filter_hubs["all"];
        delete $scope.filtered_age_range["all"];
        delete $scope.filter_gender["all"];
        delete $scope.filter_regimen["all"];
        delete $scope.filter_line["all"];
        delete $scope.filter_indication["all"];
        delete $scope.filter_emtct["all"];
        delete $scope.filter_tb_status["all"];

        getData();

        //generalFilter(); //filter the results for each required event
    }

    var isAgeRageValid=function(age_range_to_validate){
        var validated=true;
        var validate_from_age=parseInt(age_range_to_validate.from_age);
        var validate_to_age=parseInt(age_range_to_validate.to_age);

        if(validate_from_age > validate_to_age){
             validated=false;
             return validated;
        }

        for (var index = 0; index < $scope.filtered_age_range.length; index++) {
            var dummy_from_age = $scope.filtered_age_range[index].from_age;
            var dummy_to_age = $scope.filtered_age_range[index].to_age;
            //remove repeating age-ranges
            if(validate_from_age == dummy_from_age && validate_to_age == dummy_to_age){
                validated=false;
                return validated;
            }

            if(validate_from_age == dummy_from_age){
                validated=false;
                return validated;
            }

            if(validate_from_age > dummy_from_age && validate_from_age < dummy_to_age){
                validated=false;
                return validated;
            }

            if(validate_to_age > dummy_from_age && validate_to_age < dummy_to_age){
                validated=false;
                return validated;
            }
            if(validate_to_age == dummy_to_age){
                validated=false;
                return validated;
            }
        };
        return validated;
    };

    $scope.ageRangesCount = function() {
        return $scope.ageRangesCounter++;
    }
    $scope.removeTag=function(mode,nr){
        switch(mode){
            case "district": 
            delete $scope.filter_districts[nr];
            $scope.params.districts=rmveFrmArr(nr,$scope.params.districts);
            break;

            case "hub": 
            delete $scope.filter_hubs[nr];
            $scope.params.hubs=rmveFrmArr(nr,$scope.params.hubs);
            break;

            case "age_range": 
            delete $scope.filtered_age_range[nr];
            $scope.params.age_ranges=removeAgeGroup(nr,$scope.params.age_ranges);
            break;

            case "gender": 
            delete $scope.filter_gender[nr];
            $scope.params.genders=rmveFrmArr(nr,$scope.params.genders);
            break;

            case "regimen": 
            delete $scope.filter_regimen[nr];
            $scope.params.regimens=rmveFrmArr(nr,$scope.params.regimens);
            break;

            case "line": 
            delete $scope.filter_line[nr];
            $scope.params.lines=rmveFrmArr(nr,$scope.params.lines);
            break;

            case "indication": 
            delete $scope.filter_indication[nr];
            $scope.params.indications=rmveFrmArr(nr,$scope.params.indications);
            break;

            case "emtct": 
            delete $scope.filter_emtct[nr];
            $scope.params.indications=rmveFrmArr(nr,$scope.params.emtct);
            break;

            case "tb_status": 
            delete $scope.filter_tb_status[nr];
            $scope.params.indications=rmveFrmArr(nr,$scope.params.tb_status);
            break;
        }
        //$scope.filter(mode);
        getData();

    };

    $scope.clearAllFilters=function(){
        $scope.filter_districts={};
        $scope.filter_hubs={};
        $scope.filtered_age_range=[];
        $scope.filter_gender={};
        $scope.filter_regimen={};
        $scope.filter_line={};
        $scope.filter_indication={};
        $scope.filter_emtct={};
        $scope.filter_tb_status={};
        $scope.filter_duration=$scope.init_duration;
        $scope.filtered=false;
        $scope.date_filtered=false;
        $scope.fro_date="all";
        $scope.to_date="all";
        $scope.params = {
                'districts':[],'hubs':[],'age_ranges':[],'genders':[],
                'regimens':[],'lines':[],'indications':[],'emtct':[],'tb_status':[]
            };
        getData();
        //generalFilter();
    };
   
    $scope.getHubName=function(hub_id){
        var hub_list = $scope.hubs;
        var hub_name = null;
        for (var i = 0; i<hub_list.length; i++) {
            if(hub_id == hub_list[i].id){
                hub_name = hub_list[i].name;
                break;
            }
        };
        return hub_name;
    }
    $scope.displaySamplesRecieved=function(){       //$scope.samples_received=100000;  
        //console.log("districts -- "+JSON.stringify($scope.labels.districts));
        //console.log("facilities -- "+JSON.stringify($scope.labels.facilities));     
        var data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];

        for(var i in $scope.duration_numbers){
            var obj=$scope.duration_numbers[i];
            var plasma_samples=obj.samples_received-obj.dbs_samples;
            data[0].values.push({"x":dateFormat(obj._id),"y":Math.round(obj.dbs_samples||0)});
            data[1].values.push({"x":dateFormat(obj._id),"y":Math.round(plasma_samples||0)});            
        }

        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().color(["#F44336","#607D8B"]);
            if(count($scope.duration_numbers)<=8) { chart.reduceXTicks(false); }

            chart.yAxis.tickFormat(d3.format(',.0d'));
            $('#samples_received svg').html(" ");
            d3.select('#samples_received svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };

    
    $scope.displaySupressionRate=function(){
        var data=[{"key":"SUPRESSION RATE","color": "#607D8B","values":[] },
                  {"key":"VALID RESULTS","bar":true,"color": "#F44336","values":[]}];

        for(var i in $scope.duration_numbers){
            var obj=$scope.duration_numbers[i];
            var sprsd=obj.suppressed||0;
            var vld=obj.valid_results||0;
            var s_rate=((sprsd/vld)||0)*100;
            //s_rate.toPrecision(3);
            data[0].values.push([dateFormat(obj._id),Math.round(s_rate)]);
            data[1].values.push([dateFormat(obj._id),vld]);
        } 
        nv.addGraph( function() {
            var chart = nv.models.linePlusBarChart()
                        .margin({right: 60,})
                        .x(function(d,i) { return i })
                        .y(function(d,i) {return d[1] }).focusEnable(false);

            chart.xAxis.tickFormat(function(d) {
                return data[0].values[d] && data[0].values[d][0] || " ";
            });
            //chart.reduceXTicks(false);
            //chart.bars.forceY([0]);
            chart.lines.forceY([0,100]);
            chart.legendRightAxisHint(" (R)").legendLeftAxisHint(" (L)");

            $('#supression_rate svg').html(" ");
            d3.select('#supression_rate svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    }

    $scope.displayRejectionRatexxxx=function(){
            //this will hold of our main data consists of multiple chart data
        var data = [];
        
        //variables to hold monthly month
        var monthList = [];
       
        var sampleQualityRejectionRateList = [];
        var incompleteFormRejectionRateList = [];
        var eligibilityRejectionRateList = [];
        var rejectionRateList = [];
        var samplesRejectedList = [];

           
        for(var i in $scope.duration_numbers){
            var duration_numbers_instance = $scope.duration_numbers[i];
           
            
            monthList.push(dateFormat(duration_numbers_instance._id));

            var totalRegections = duration_numbers_instance.sample_quality_rejections+duration_numbers_instance.incomplete_form_rejections
            +duration_numbers_instance.eligibility_rejections;
            
            var sampleQualityRejectionRate = Math.round(((duration_numbers_instance.sample_quality_rejections/totalRegections)||0)*100);
            sampleQualityRejectionRateList.push(sampleQualityRejectionRate);


            var incompleteFormRejectionRate = Math.round(((duration_numbers_instance.incomplete_form_rejections/totalRegections)||0)*100);
            incompleteFormRejectionRateList.push(incompleteFormRejectionRate);

            var eligibilityRejectionRate =100-(sampleQualityRejectionRate+incompleteFormRejectionRate);
            eligibilityRejectionRateList.push(eligibilityRejectionRate);

            var rejected = totalRegections;
            var received = duration_numbers_instance.samples_received;
            rejectionRate = Math.round(((rejected/received)||0)*100);
            rejectionRateList.push(rejectionRate);
            samplesRejectedList.push(rejected);

        }


        //Array to hold each individual coordinate x and y values in json format
        var sampleQualityRejectionRateValues = [];
        var incompleteFormRejectionRateValues = [];
        var eligibilityRejectionRateValues = [];
        var rejectionRateValues = [];
        var samplesRejectedValues = [];
        
        
        //Looping the data and fetch into array
        for(var i = 0; i < monthList.length; i++){
          
            
            var xySampleQualityRejectionRate = {x:i,y:sampleQualityRejectionRateList[i]};
            sampleQualityRejectionRateValues.push(xySampleQualityRejectionRate);

            var xynIcompleteFormRejectionRate = {x:i, y:incompleteFormRejectionRateList[i]};
            incompleteFormRejectionRateValues.push(xynIcompleteFormRejectionRate);

            var xyEligibilityRejectionRate = {x:i,y:eligibilityRejectionRateList[i]};
            eligibilityRejectionRateValues.push(xyEligibilityRejectionRate);

            var xyRejectionRate = {x:i, y:rejectionRateList[i]};
            rejectionRateValues.push(xyRejectionRate);

            var xySamplesRejected = {x:i, y:samplesRejectedList[i]};
            samplesRejectedValues.push(xySamplesRejected);

        }
        
        //These will be for the bar charts 
        var sampleQualityRejection = {key: "SAMPLE QUALITY", values: sampleQualityRejectionRateValues, type: "bar", yAxis: 1, color: '#F44336'};
        var incompleteFormRejection = {key: "INCOMPLETE FORM", values: incompleteFormRejectionRateValues, type: "bar", yAxis: 1, color: '#607D8B'};
        var eligibilityRejection = {key: "ELIGIBILITY", values: incompleteFormRejectionRateValues, type: "bar", yAxis: 1, color: '#FFCDD2'};
        var samplesRejected = {key: "SAMPLES REJECTED", values: samplesRejectedValues, type: "bar", yAxis: 1, color: '#C62828'};

        //These will be for line charts
        var rejectionRate = { key: "Rejection Rate", values: rejectionRateValues, type: "line", yAxis: 2, color: '#D32F2F' }
        
        //Insert the values array into data variable
        data.push(sampleQualityRejection);
        data.push(incompleteFormRejection);
        data.push(eligibilityRejection);
        data.push(samplesRejected);
        
        data.push(rejectionRate);
        
        //build the graph
        nv.addGraph(function () {
            //build as multichart graphs and set the margin right and left to 100px.
            var chart = nv.models.multiChart()
                        .margin({left: 100, right: 100});
                        
            chart.bars1.stacked(true);

            //customize the tool tip
            /**
            chart.tooltip.contentGenerator(function (key, x, y, e, graph) {
                return "<div class='tooltip'><span>Month:</span> " + monthList[key.index] + "</div>" + "<div class='tooltip'><span>Value:</span> " + key.series[0].value + "</div><div class='tooltip'><span>Legend:</span> <div style='background:" + key.series[0].color + ";display:inline-block;height:15px;width:15px;'>&#160;</div></div>";
            });
             */
            //Overwrite the x axis label and replace it with the month name
            chart.xAxis.tickFormat(function (d) { return monthList[d] });
            
            //get the chart svg object and fecth the data to build the chart
            //$('#rejection_rate svg').html(" ");
            d3.select('#rejection_rate svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };

    $scope.displayRejectionRate=function(){
        var data=[{"key":"SAMPLE QUALITY","values":[], "bar": true},
                  {"key":"INCOMPLETE FORM","values":[], "bar": false },
                  {"key":"ELIGIBILITY","values":[] }];

        for(var i in $scope.duration_numbers){
            var obj=$scope.duration_numbers[i];

            
            var ttl=obj.sample_quality_rejections+obj.incomplete_form_rejections+obj.eligibility_rejections;
            var sq_rate=Math.round(((obj.sample_quality_rejections/ttl)||0)*100);
            var inc_rate=Math.round(((obj.incomplete_form_rejections/ttl)||0)*100);
            //var el_rate=((obj.eligibility_rejections/ttl)||0)*100;
            var el_rate=100-(sq_rate+inc_rate);
            data[0].values.push({"x":dateFormat(obj._id),"y": sq_rate });
            data[1].values.push({"x":dateFormat(obj._id),"y": inc_rate });
            data[2].values.push({"x":dateFormat(obj._id),"y": el_rate });
        }
        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().stacked(true).color(["#607D8B","#FFCDD2","#F44336"]);
            if(count($scope.duration_numbers)<=8) { chart.reduceXTicks(false); }
            chart.yAxis.tickFormat(d3.format(',.0d'));
            $('#rejection_rate svg').html(" ");
            d3.select('#rejection_rate svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };
     

     $scope.displayRegimenGroups=function(){

        var data=[{"key":"SUPRESSION RATE","color": "#607D8B","values":[] },
                  {"key":"VALID RESULTS","bar":true,"color": "#F44336","values":[]}];

        for(var i in $scope.regimen_numbers){
            var obj=$scope.regimen_numbers[i];
            var sprsd=obj.suppressed||0;
            var vld=obj.valid_results||0;
            var s_rate=((sprsd/vld)||0)*100;
            //s_rate.toPrecision(3);
            var label=$scope.labels.regimens2[obj._id];
            data[0].values.push([label,Math.round(s_rate)]);
            data[1].values.push([label,vld]);
        } 
        nv.addGraph( function() {
            var chart = nv.models.linePlusBarChart()
                        .margin({right: 60,})
                        .x(function(d,i) { return i })
                        .y(function(d,i) {return d[1] }).focusEnable(false);

            chart.xAxis.tickFormat(function(d) {
                return data[0].values[d] && data[0].values[d][0] || " ";
            });
            chart.xAxis.rotateLabels(90);
            //chart.reduceXTicks(false);

            chart.lines.forceY([0,100]);
            chart.legendRightAxisHint(" (R)").legendLeftAxisHint(" (L)");

            $('#regimen_groups svg').html(" ");
            d3.select('#regimen_groups svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });

        
    };


    $scope.displayRegimenTime=function(){
        var data=[{"key":"SUPRESSION RATE","color": "#607D8B","values":[] },
                  {"key":"SAMPLES RECEIVED","bar":true,"color": "#F44336","values":[]},
                  {"key":"NON SUPRESSION RATE","color": "#FF851B","values":[]}];

        for(var i in $scope.regimen_time_numbers){
            var obj=$scope.regimen_time_numbers[i];
            var sprsd=obj.suppressed||0;
            var vld=obj.valid_results||0;
            var s_rate=((sprsd/vld)||0)*100;
            var non_suppression_rates = 100 - s_rate;
            //s_rate.toPrecision(3);
            var label=regimen_times_json[obj._id];//non_suppression

            data[0].values.push([label,Math.round(s_rate)]);
            data[1].values.push([label,obj.samples_received]);

            data[2].values.push([label,Math.round(non_suppression_rates)]);//non_suppression
        } 
        nv.addGraph( function() {
            var chart = nv.models.linePlusBarChart()
                        .margin({right: 60,})
                        .x(function(d,i) { return i })
                        .y(function(d,i) {return d[1] }).focusEnable(false);

            chart.xAxis.tickFormat(function(d) {
                return data[0].values[d] && data[0].values[d][0] || " ";
            });
            //chart.reduceXTicks(false);
            //chart.bars.forceY([0]);
            chart.lines.forceY([0,100]);
            chart.legendRightAxisHint(" (R)").legendLeftAxisHint(" (L)");

            $('#regimen_time svg').html(" ");
            d3.select('#regimen_time svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };

    $scope.compare = function(prop,comparator, val){
        return function(item){
            if(comparator=='eq'){
                return item[prop] == val;
            }else if (comparator=='ne'){
               return item[prop] != val;
            }else if (comparator=='gt'){
               return item[prop] > val;
            }else if (comparator=='lt'){
               return item[prop] < val;
            }else if (comparator=='ge'){
               return (item[prop] > val)||(item[prop] == val);
            }else if (comparator=='le'){
               return (item[prop] < val)||(item[prop] == val);
            }else{
                return false;
            }
        }
    };

    $scope.empty=function(prop,status){
        return function(item){
            switch(item[prop]) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                case typeof this == "undefined":
                if(status=='no'){ return false; } else { return true; };
                    default :  if(status=='no'){ return true; } else { return false; };
                }
        }
           
    };

    $scope.showF=function(i){
        var show_f=false;
        switch(i){
            case 1:
            show_f=$scope.show_fclties1;
            $scope.show_fclties1=show_f==false?true:false;        
            break;

            case 2:
            show_f=$scope.show_fclties2;
            $scope.show_fclties2=show_f==false?true:false;
            break;

            case 3:
            show_f=$scope.show_fclties3;
            $scope.show_fclties3=show_f==false?true:false;
            break;
        }
        if(show_f==true){
            $("#d_shw"+i).attr("class","active");
            $("#f_shw"+i).attr("class","");
        }else{
            $("#f_shw"+i).attr("class","active");
            $("#d_shw"+i).attr("class","");
        }
    };

    $scope.showReg=function(){
        $scope.show_reg=!$scope.show_reg;
        if($scope.show_reg==true){
            $scope.displayRegimenGroups();
            $("#reg_shw").attr("class","active");
            $("#dur_shw").attr("class","");
        }else{
            $scope.displayRegimenTime();
            $("#reg_shw").attr("class","");
            $("#dur_shw").attr("class","active");
        }
    }

    var inArray=function(val,arr){
        var ret=false;
        for(var i in arr){
            if(val==arr[i]) ret=true;
        }
        return ret;
    };

    /*var dateFormat=function(y_m){
        var arr=y_m.split('-');
        var yr=arr[0];
        var mth=arr[1];
        return $scope.month_labels[mth]+" '"+yr.slice(-2);
    }*/

    var dateFormat=function(x){
        var ym=isNaN(x)?x:x.toString();
        return $scope.month_labels[ym.slice(-2)]+" '"+ym.slice(2,4);
    };

    var count=function(json_obj){
        return Object.keys(json_obj).length;
    };

    var rmveFrmArr=function(val,arr){
        for(var i in arr){
            if(val==arr[i]){
                arr.splice(i,1); 
                return arr;
            } 
        }
        return arr;
    };
    var removeAgeGroup=function(index,age_range_array){
        var age_range_array_cleaned = [];
        for(var i =0; i< age_range_array.length;i++){
            if(index != i)
            {

               age_range_array_cleaned.push(age_range_array[i]); 
            }
        }

        return age_range_array_cleaned;
    };
    //rounding off numbers to the nearest decimal place
    var round = Math.round;
    Math.round = function (value, decimals) {
        decimals = decimals || 0;
        return Number(round(value + 'e' + decimals) + 'e-' + decimals);
    }
    function exportDistrictNumbers(scopeInstance){
       
        var export_district_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        var district_numbers_from_scope = scopeInstance.district_numbers;

        for( var index = 0; index < district_numbers_from_scope.length; index++){
            var districtRecord = district_numbers_from_scope[index];

            var district_instance = {
                district_name : district_labels[districtRecord._id],
                samples_received : districtRecord.samples_received,
                patients_received : districtRecord.patients_received,
                samples_tested : districtRecord.total_results,
                samples_pending : (districtRecord.samples_received - districtRecord.total_results),
                rejected_samples : districtRecord.rejected_samples,
                dbs : Math.round(((districtRecord.dbs_samples/districtRecord.samples_received)*100),1),
                plasma : Math.round((((districtRecord.samples_received-districtRecord.dbs_samples)/districtRecord.samples_received)*100 ),1)
            }

            export_district_numbers.push(district_instance);
        }

        return export_district_numbers;
    }

    function exportFacilityNumbers(scopeInstance){
       
        var export_facility_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        var facility_labels = scopeInstance.labels.facilities;
        var facility_details_labels = scopeInstance.labels.facilities_details;

        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var hub_name_value = null;
            try{
                hub_name_value =scopeInstance.getHubName(facilityRecord.hub_id);
            }catch(err){

            }
            var facility_details_lables_object = facility_details_labels[facilityRecord.facility_id];
            if(facility_details_lables_object === null){
                console.log(facilityRecord.facility_id);
                continue;
            }
            var facility_instance = {
                district_name : district_labels[facilityRecord.district_id],
                hub_name: hub_name_value,
                facility_name : facility_details_lables_object.cphl_name,
                dhis2_facility_name : facility_details_labels[facilityRecord.facility_id].dhis2_name,
                dhis2_facility_uid : facility_details_labels[facilityRecord.facility_id].dhis2_uid,
                samples_received : facilityRecord.samples_received,
                patients_received : facilityRecord.patients_received,
                samples_tested : facilityRecord.total_results,
                samples_pending : (facilityRecord.samples_received - facilityRecord.total_results),
                rejected_samples : facilityRecord.rejected_samples,
                dbs : Math.round(((facilityRecord.dbs_samples/facilityRecord.samples_received)*100),1),
                plasma : Math.round((((facilityRecord.samples_received-facilityRecord.dbs_samples)/facilityRecord.samples_received)*100 ),1)
            }

            export_facility_numbers.push(facility_instance);
        }

        return export_facility_numbers;
    }

    function exportDistrictSuppressionNumbers(scopeInstance){
        var export_district_suppression_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        var district_numbers_from_scope = scopeInstance.district_numbers;

        for( var index = 0; index < district_numbers_from_scope.length; index++){
            var districtRecord = district_numbers_from_scope[index];

            var district_instance = {
                district_name : district_labels[districtRecord._id],
                valid_results : districtRecord.valid_results,
                suppressed_results : districtRecord.suppressed,
                non_suppressed_results : (Number(districtRecord.valid_results) - Number(districtRecord.suppressed)),
                
                suppression_rate : Math.round(((districtRecord.suppressed/districtRecord.valid_results)*100),1)
            }

            export_district_suppression_numbers.push(district_instance);
        }

        return export_district_suppression_numbers;
    }

    function exportFacilitySuppressionNumbers(scopeInstance){
        var export_facility_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        var facility_labels = scopeInstance.labels.facilities;
        var facility_details_labels = scopeInstance.labels.facilities_details;

        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var hub_name_value = null;
            try{
                hub_name_value = scopeInstance.getHubName(facilityRecord.hub_id);
            }catch(err){

            }

            var facility_instance = {
                district_name : district_labels[facilityRecord.district_id],
                hub_name: hub_name_value,
                facility_name : facility_details_labels[facilityRecord.facility_id].cphl_name,
                dhis2_facility_name : facility_details_labels[facilityRecord.facility_id].dhis2_name,
                dhis2_facility_uid : facility_details_labels[facilityRecord.facility_id].dhis2_uid,
                valid_results : facilityRecord.valid_results,
                suppressed_results : facilityRecord.suppressed,
                non_suppressed_results : (Number(facilityRecord.valid_results) - Number(facilityRecord.suppressed)),
                suppression_rate : Math.round(((facilityRecord.suppressed/facilityRecord.valid_results)*100),1)
            }

            export_facility_numbers.push(facility_instance);
        }

        return export_facility_numbers;
    }

    function exportDistrictRejectionNumbers(scopeInstance){
        var export_district_rejection_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        var district_numbers_from_scope = scopeInstance.district_numbers;

        for( var index = 0; index < district_numbers_from_scope.length; index++){
            var districtRecord = district_numbers_from_scope[index];

            var district_instance = {
                district_name : district_labels[districtRecord._id],
                samples_received : districtRecord.samples_received,
                rejected_samples : districtRecord.rejected_samples,
                
                rejection_rate : Math.round(((districtRecord.rejected_samples/districtRecord.samples_received)*100),1)
            }

            export_district_rejection_numbers.push(district_instance);
        }

        return export_district_rejection_numbers;
    }

    function exportFacilityRejectionNumbers(scopeInstance){
        var export_facility_rejection_numbers = [];
        var district_labels = scopeInstance.labels.districts;
        
        var facility_labels = scopeInstance.labels.facilities;
        var facility_details_labels = scopeInstance.labels.facilities_details;

        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var hub_name_value = null;
            try{
                hub_name_value = scopeInstance.getHubName(facilityRecord.hub_id);
            }catch(err){

            }

            var facility_instance = {
                district_name : district_labels[facilityRecord.district_id],
                hub_name: hub_name_value,
                facility_name : facility_details_labels[facilityRecord.facility_id].cphl_name,
                dhis2_facility_name : facility_details_labels[facilityRecord.facility_id].dhis2_name,
                dhis2_facility_uid : facility_details_labels[facilityRecord.facility_id].dhis2_uid,
                samples_received : facilityRecord.samples_received,
                rejected_samples:facilityRecord.rejected_samples,
                rejection_rate : Math.round(((facilityRecord.rejected_samples/facilityRecord.samples_received)*100),1)
            }

            export_facility_rejection_numbers.push(facility_instance);
        }

        return export_facility_rejection_numbers;
    }

    function exportRegimenNumbers(scopeInstance){
        //
        var export_regimen_numbers = [];
        var regimen_labels = scopeInstance.labels.regimens2;
        var regimen_numbers_from_scope = scopeInstance.regimen_numbers;
        var samples_received = scopeInstance.samples_received;

        for( var index = 0; index < regimen_numbers_from_scope.length; index++){
            var regimenRecord = regimen_numbers_from_scope[index];

            var regimen_instance = {
                regimen : regimen_labels[regimenRecord._id],
                samples_received : regimenRecord.samples_received,
                total_results:regimenRecord.total_results,
                valid_results : regimenRecord.valid_results,
                suppressed:regimenRecord.suppressed,
                suppression_rate : Math.round(((regimenRecord.suppressed/regimenRecord.valid_results)*100),1),
                percentage_of_samples : Math.round(((regimenRecord.samples_received/samples_received)*100),1),
            }

            export_regimen_numbers.push(regimen_instance);
        }

        return export_regimen_numbers;
    }

    function exportDurationOnArt(scopeInstance){
        //
        var export_duration_on_art= [];
        var regimen_time_labels = scopeInstance.labels.reg_times;
        var regimen_time_numbers_from_scope = scopeInstance.regimen_time_numbers;
        var samples_received = scopeInstance.samples_received;

        for( var index = 0; index < regimen_time_numbers_from_scope.length; index++){
            var regimenTimeRecord = regimen_time_numbers_from_scope[index];

            var regimen_time_instance = {
                time_on_treatment: regimen_time_labels[regimenTimeRecord._id],
                samples_received : regimenTimeRecord.samples_received,

                
                percentage_of_samples : Math.round(((regimenTimeRecord.samples_received/samples_received)*100),1),
                samples_tested : regimenTimeRecord.total_results,
                suppressed : regimenTimeRecord.suppressed
                             }

            export_duration_on_art.push(regimen_time_instance);
        }

        return export_duration_on_art;
    }
    function getCurrentTimeStamp(){
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        var hr = today.getHours();
        var min = today.getMinutes();


        if(dd<10) {
            dd='0'+dd
        } 

        if(mm<10) {
            mm='0'+mm
        } 

        if (min < 10) {
            min = "0" + min;
        }

        today = yyyy+''+mm+''+dd+''+hr+''+min;
        return today;
    }
};

app.controller(ctrllers);