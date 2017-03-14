
//angular stuff

/*
Authors
Name                        @       Period      Role       
Logan Smith                 CHAI    2015(v1)    Interface Design, Q/A
Lena Derisavifard           CHAI    2015(v1)    Req Specification, Q/A, UAT
Kitutu Paul                 CHAI    2015(v1)    System development
Sam Otim                    CHAI    2015(v1)    System development

Credit to CHAI Uganda, CPHL and stakholders
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
        'districts':[],'hubs':[],'age_ids':[],'genders':[],
        'regimens':[],'lines':[], 'indications': []};

    var hubs_json = {};
    var age_group_json = {1:"0<2",2:"2-<5",3:"5-<10",4:"10-<15",5:"15-<20",6:"20-<25",7:"25+"};  
    var regimen_groups_json = {1: 'AZT based', 2: 'ABC based', 3: 'TDF based', 4: 'Other'};
    var regimen_times_json = {0:'No Date Given',1:'6-12 months',2:'1-2 years',3:'2-3 years',4:'3-5 years',5:'5+ years'};    
    var results_json = {}; //to hold a big map will all processed data to later on be used in the generalFilter
    var genders_json = {'m':'Male','f':'Female','x':'Unknown'};
    var lines_json = {1:'1st Line',2:'2nd Line',4:'Left Blank',5:'Other'};
    var t_indication_json = {1: "PMTCT/OPTION B+", 4:"TB INFECTION"};

    $scope.month_labels = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sept','10':'Oct','11':'Nov','12':'Dec'};

    $scope.labels = {};
    $scope.labels.reg_grps = regimen_groups_json;
    $scope.labels.reg_times = regimen_times_json;
    $scope.labels.age_grps = age_group_json;
    $scope.labels.genders = genders_json;
    $scope.labels.lines = lines_json;
    $scope.labels.districts = [];
    $scope.labels.facilities = [];
    $scope.labels.regimens = [];
    $scope.labels.regimens2 = [];
    $scope.labels.indications = t_indication_json;

    var vvvrrr = 0;

    $scope.districts2 = [];
    $scope.hubs2 = [];
    $scope.age_group_slct = age_group_json;
    
    var removeDuplicates = function(patient_results){
        var clean_results =[];
        for (var i = 0; i < patient_results.length; i++) {
            var patient_results_object = patient_results[i];
            var clean_results_object = null;
            if (i== 0) {
                clean_results_object = {
                "patientID":patient_results_object.patientID,
                "vlSampleID":patient_results_object.vlSampleID,
                "created":patient_results_object.created,
                "patientUniqueID":patient_results_object.patientUniqueID,
                "result":patient_results_object.result,
                "hub":patient_results_object.hub,
                "facility":patient_results_object.facility,
                "collectionDate":patient_results_object.collectionDate,
                "receiptDate":patient_results_object.receiptDate,
                "artNumber":patient_results_object.artNumber,
                "phone":patient_results_object.phone};

                clean_results.push(clean_results_object);
            }else if(i > 0){
                var previous_index = i - 1;
                var previous_patient_results_object = patient_results[previous_index];
                if( 
                    previous_patient_results_object.vlSampleID != patient_results_object.vlSampleID){
                        clean_results_object = {
                            "patientID":patient_results_object.patientID,
                            "vlSampleID":patient_results_object.vlSampleID,
                            "created":patient_results_object.created,
                            "patientUniqueID":patient_results_object.patientUniqueID,
                            "result":patient_results_object.result,
                            "hub":patient_results_object.hub,
                            "facility":patient_results_object.facility,
                            "collectionDate":patient_results_object.collectionDate,
                            "receiptDate":patient_results_object.receiptDate,
                            "artNumber":patient_results_object.artNumber,
                            "phone":patient_results_object.phone
                        };

                        clean_results.push(clean_results_object);

                }
            }//end else if
            
            
            
        };//end for loop
        return clean_results;
    };

    var getUniformResults = function(result){
        //var returnString = null;
        var returnString = "rejected";
        var str="";
        str = result;
        if(str == null){
            return returnString;
         }
        //remove in bits
        if(str == 'Not detected')
        {
            returnString = "suppressed";
        }else if(str == 'detected'){
            returnString = "notSuppressed";
        }else if(str.startsWith("Not detected")){
                returnString = "suppressed";
            }else if(str.startsWith("-1")){
                returnString = "rejected";
            }else if(str.startsWith("4442")){
                returnString = "rejected";
            }else if(str.startsWith("3109")){
                returnString = "rejected";
            }else if(str.startsWith("3110")){
                returnString = "rejected";
            }else if(str.startsWith("3118")){
                returnString = "rejected";
            }else if(str.startsWith("3119")){
                returnString = "rejected";
            }else if(str.startsWith("3153")){
                returnString = "rejected";
            }else if(str.startsWith("4408")){
                returnString = "rejected";
            }else if(str.startsWith("3130")){
                returnString = "rejected";
            }else if(str.startsWith("Target not detected")){
                returnString = "suppressed";
            }else if(str.startsWith("< 1.60 Log")){//40 Copies m/L
                returnString = "suppressed";
            }else if(str.startsWith("< 75 Copies")){
                returnString = "suppressed";
            }else if(str.startsWith("<75 copies")){
                returnString = "suppressed";
            }else if(str.startsWith("< 550 Copies")){
                returnString = "suppressed";
            }else if(str.startsWith("> 10,000,000 Copies")){
                returnString = "notSuppressed";
            }else if(str.startsWith("< 150 Copies")){
                returnString = "suppressed";
            } else if(str.startsWith("1") || str.startsWith("2") || str.startsWith("3") || str.startsWith("4") ||
                str.startsWith("5") || str.startsWith("6") || str.startsWith("7") || str.startsWith("8") ||
                str.startsWith("9") ){

                
                while(str.includes(",")){
                    str = str.replace(',','');
                }
                str = str.match(/[0-9]+/g);
                str = parseInt(str);
                
                
                if(str < 1000){
                    returnString = "suppressed";
                }else if(str >= 1000){
                    returnString = "notSuppressed";
                }
                else if(str == 3118 || str == 3109){
                    returnString = "rejected";
                }
            }else{
                returnString = "rejected";
            }

            if (returnString == null){
                 returnString = "rejected";
            }
        return returnString;
    };
    var removeFirstTimeVLTesters = function(clean_results){
        var patients_with_more_results =[];
        for (var i = 1; i < clean_results.length; i++) {

            var clean_results_object = clean_results[i];
            var previous_index = i - 1;
            var next_index = i + 1;
            var previous_clean_results_object = clean_results[previous_index];
            var next_clean_results_object = clean_results[next_index];

            var patients_with_more_results_object = [];

            if (i == 1 && previous_clean_results_object.patientID == clean_results_object.patientID)  {
                
                patients_with_more_results_previous_object = {
                            "patientID":clean_results_object.patientID,
                            "vlSampleID":clean_results_object.vlSampleID,
                            "created":clean_results_object.created,
                            "patientUniqueID":clean_results_object.patientUniqueID,
                            "result":clean_results_object.result,
                            "status":getUniformResults(clean_results_object.result),
                            "hub":clean_results_object.hub,
                            "facility":clean_results_object.facility,
                            "collectionDate":clean_results_object.collectionDate,
                            "receiptDate":clean_results_object.receiptDate,
                            "artNumber":clean_results_object.artNumber,
                            "phone":clean_results_object.phone
                        };
                patients_with_more_results.push(patients_with_more_results_previous_object);

                patients_with_more_results_object = {
                            "patientID":clean_results_object.patientID,
                            "vlSampleID":clean_results_object.vlSampleID,
                            "created":clean_results_object.created,
                            "patientUniqueID":clean_results_object.patientUniqueID,
                            "result":clean_results_object.result,
                            "status":getUniformResults(clean_results_object.result),
                            "hub":clean_results_object.hub,
                            "facility":clean_results_object.facility,
                            "collectionDate":clean_results_object.collectionDate,
                            "receiptDate":clean_results_object.receiptDate,
                            "artNumber":clean_results_object.artNumber,
                            "phone":clean_results_object.phone
                        };
                    patients_with_more_results.push(patients_with_more_results_object);
            }//end of if
            else if(previous_clean_results_object.patientID == clean_results_object.patientID){
                patients_with_more_results_object = {
                            "patientID":clean_results_object.patientID,
                            "vlSampleID":clean_results_object.vlSampleID,
                            "created":clean_results_object.created,
                            "patientUniqueID":clean_results_object.patientUniqueID,
                            "result":clean_results_object.result,
                            "status":getUniformResults(clean_results_object.result),
                            "hub":clean_results_object.hub,
                            "facility":clean_results_object.facility,
                            "collectionDate":clean_results_object.collectionDate,
                            "receiptDate":clean_results_object.receiptDate,
                            "artNumber":clean_results_object.artNumber,
                            "phone":clean_results_object.phone
                        };
                    patients_with_more_results.push(patients_with_more_results_object);
            }
            else if( next_clean_results_object != null){
                if(clean_results_object.patientID == next_clean_results_object.patientID){
                    patients_with_more_results_object = {
                            "patientID":clean_results_object.patientID,
                            "vlSampleID":clean_results_object.vlSampleID,
                            "created":clean_results_object.created,
                            "patientUniqueID":clean_results_object.patientUniqueID,
                            "result":clean_results_object.result,
                            "status":getUniformResults(clean_results_object.result),
                            "hub":clean_results_object.hub,
                            "facility":clean_results_object.facility,
                            "collectionDate":clean_results_object.collectionDate,
                            "receiptDate":clean_results_object.receiptDate,
                            "artNumber":clean_results_object.artNumber,
                            "phone":clean_results_object.phone
                        };
                    patients_with_more_results.push(patients_with_more_results_object); 
                }//end inner if
                    
            }
        }//end for loop

        return patients_with_more_results;
    };

    var sortPatientRecords = function(patients_with_more_results){
        var sorted_patient_records =[];
         for (var i = 1; i < patients_with_more_results.length; i++) {
             var current_record = patients_with_more_results[i];
             var previous_index = i - 1;
             var previous_record = patients_with_more_results[previous_index];
             
             //eliminate third VL result
             var second_previous_index = i - 2;
             var second_previous_record = null;
             if(i > 1){
                second_previous_record = patients_with_more_results[second_previous_index];
                if(second_previous_record.patientID == current_record.patientID){
                    continue;
                }
             }

             if(previous_record.patientID == current_record.patientID){

                var sorted_patient_records_object = {
                    "patientID":current_record.patientID,
                    "patientUniqueID":current_record.patientUniqueID,
                    "hub":current_record.hub,
                    "facility":current_record.facility,
                    "artNumber":current_record.artNumber,
                    
                    "previousCollectionDate":previous_record.collectionDate,
                    "prevoiusReceiptDate":previous_record.receiptDate,

                    "currentCollectionDate":current_record.collectionDate,
                    "currentReceiptDate":current_record.receiptDate,

                    "previousResults": previous_record.result,
                    "previousStatus": previous_record.status,
                    "currentResults": current_record.result,
                    "currentStatus": current_record.status,
                    "phone":current_record.phone
                };

                sorted_patient_records.push(sorted_patient_records_object);
             }
         };//end for loop
         return sorted_patient_records;
    };

    var getSuppressionTrend = function(sorted_patient_records){
        var suppression_trend = null;
        var previouslyNotSuppressing = 0;
        var previouslyNotSuppressingCurrentlyNotSuppressing = 0;

        var previouslySuppressing = 0;
        var previouslySuppressingCurrentlyNotSuppressing = 0;

        var previouslyNScurrentlyNS = [];
        var previouslyScurrentlyNS= [];

        for (var i = 0; i < sorted_patient_records.length; i++) {
            var sorted_patient_records_object = sorted_patient_records[i];
            if(sorted_patient_records_object.previousStatus == "notSuppressed"){
                previouslyNotSuppressing ++;
                if(sorted_patient_records_object.currentStatus == "notSuppressed"){
                    previouslyNotSuppressingCurrentlyNotSuppressing ++;
                    previouslyNScurrentlyNS.push(sorted_patient_records_object);
                }
            }else if(sorted_patient_records_object.previousStatus == "suppressed"){
                previouslySuppressing++;
                if(sorted_patient_records_object.currentStatus == "notSuppressed"){
                    previouslySuppressingCurrentlyNotSuppressing ++;

                    previouslyScurrentlyNS.push(sorted_patient_records_object);
                }
            }
        };
        suppression_trend = {
            "previouslyNotSuppressing":previouslyNotSuppressing,
            "previouslyNotSuppressingCurrentlyNotSuppressing": previouslyNotSuppressingCurrentlyNotSuppressing,
            "previouslySuppressing":previouslySuppressing,
            "previouslySuppressingCurrentlyNotSuppressing":previouslySuppressingCurrentlyNotSuppressing,
            "previouslyNScurrentlyNS": previouslyNScurrentlyNS,
            "previouslyScurrentlyNS": previouslyScurrentlyNS
        };
        return suppression_trend;
    };

    var getAllPatientsResults = function(patients_with_more_results){
        var patient_records =[];
        var sorted_patient_records_object = null;
        var dummy_receipt_dates = "";
        var dummy_results = "";
        var test ="";
        var array_size = patients_with_more_results.length;
        for (var i = 0; i< array_size; i++) {
            var current_record = patients_with_more_results[i];

            var next_index = i + 1;
            var next_record = null;

            var second_next_index = i + 2;
            var second_next_record = null;

            var third_next_index = i + 3;
            var third_next_record = null;

            var fourth_next_index = i + 4; 
            var fourth_next_record = null;
            
            //check last four records
            if(fourth_next_index < array_size){
                fourth_next_record = patients_with_more_results[fourth_next_index];
                third_next_record =  patients_with_more_results[third_next_index];
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(fourth_next_record.patientID == current_record.patientID){
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+third_next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+fourth_next_record.receiptDate;

                    
                    dummy_results = dummy_results +'\&'+current_record.status;
                    dummy_results = dummy_results +'\&'+next_record.status;
                    dummy_results = dummy_results +'\&'+second_next_record.status;
                    dummy_results = dummy_results +'\&'+third_next_record.status;
                    dummy_results = dummy_results +'\&'+fourth_next_record.status;

                    
                    test = test +'\&'+current_record.result;
                    test = test +'\&'+next_record.result;
                    test = test +'\&'+second_next_record.result;
                    test = test +'\&'+third_next_record.result;
                    test = test +'\&'+fourth_next_record.result;

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "test":test,
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    patient_records.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+4;
                    continue;
                }

            }
            //check last three records
            if (third_next_index  < array_size) {
                third_next_record = patients_with_more_results[third_next_index];
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(third_next_record.patientID == current_record.patientID){
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+third_next_record.receiptDate;

                    dummy_results = dummy_results +'\&'+current_record.status;
                    dummy_results = dummy_results +'\&'+next_record.status;
                    dummy_results = dummy_results +'\&'+second_next_record.status;
                    dummy_results = dummy_results +'\&'+third_next_record.status;

                    test = test +'\&'+current_record.result;
                    test = test +'\&'+next_record.result;
                    test = test +'\&'+second_next_record.result;
                    test = test +'\&'+third_next_record.result;
                    

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "test":test,
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    patient_records.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+3;
                    continue;
                }
            } 
            //check last two records
            if(second_next_index < array_size){
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(second_next_record.patientID == current_record.patientID){
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;

                    dummy_results = dummy_results +'\&'+current_record.status;
                    dummy_results = dummy_results +'\&'+next_record.status;
                    dummy_results = dummy_results +'\&'+second_next_record.status;

                    test = test +'\&'+current_record.result;
                    test = test +'\&'+next_record.result;
                    test = test +'\&'+second_next_record.result;
                    

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "test":test,
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    patient_records.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    patient_records_object = null;
                    i=i+2;
                    continue;
                }
            }
            //check last record
             if(next_index < array_size){
                next_record = patients_with_more_results[next_index];
                if(next_record.patient_id == current_record.patient_id){
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;

                    dummy_results = dummy_results +'\&'+current_record.status;
                    dummy_results = dummy_results +'\&'+next_record.status;

                    test = test +'\&'+current_record.result;
                    test = test +'\&'+next_record.result;
                    
                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "test":test,
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    patient_records.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+1;

                    continue;
                }
            }
            
        };
         return patient_records;
    };
    var removeRejections = function(patients_with_more_results){
        var patients_with_more_results_and_no_rejections=[];
        var array_size = patients_with_more_results.length;

        for (var i = 0; i< array_size; i++) {
            var current_record = patients_with_more_results[i];
            if(current_record.status == "rejected"){
                continue;
            }else{
                patients_with_more_results_and_no_rejections.push(current_record);
            }
        }
        return patients_with_more_results_and_no_rejections;
    };
    var getValidPatientResults = function(patients_with_more_results){
        var valid_patient_results =[];
        var sorted_patient_records_object = null;
        var dummy_receipt_dates = "";
        var dummy_results = "";
        var test ="";
        var array_size = patients_with_more_results.length;
        for (var i = 0; i< array_size; i++) {
            var current_record = patients_with_more_results[i];

            var next_index = i + 1;
            var next_record = null;

            var second_next_index = i + 2;
            var second_next_record = null;

            var third_next_index = i + 3;
            var third_next_record = null;

            var fourth_next_index = i + 4; 
            var fourth_next_record = null;
            
            //check last four records
            if(fourth_next_index < array_size){
                fourth_next_record = patients_with_more_results[fourth_next_index];
                third_next_record =  patients_with_more_results[third_next_index];
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(fourth_next_record.patientID == current_record.patientID){

                    if(current_record.status == "suppressed" || current_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+current_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    }
                    if(current_record.status == "suppressed" || next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    }
                    
                    if(second_next_record.status == "suppressed" || second_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+second_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;
                    }
                    if(third_next_record.status == "suppressed" || third_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+third_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+third_next_record.receiptDate;
                    }
                    
                    if(third_next_record.status == "suppressed" || third_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+fourth_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+fourth_next_record.receiptDate;
                    }
                    

                    
                    

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    valid_patient_results.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+4;
                    continue;
                }

            }
            //check last three records
            if (third_next_index  < array_size) {
                third_next_record = patients_with_more_results[third_next_index];
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(third_next_record.patientID == current_record.patientID){
                    
                    if(current_record.status == "suppressed" || current_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+current_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    }
                    if(current_record.status == "suppressed" || next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    }
                    
                    if(second_next_record.status == "suppressed" || second_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+second_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;
                    }
                    if(third_next_record.status == "suppressed" || third_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+third_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+third_next_record.receiptDate;
                    }

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    valid_patient_results.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+3;
                    continue;
                }
            } 
            //check last two records
            if(second_next_index < array_size){
                second_next_record = patients_with_more_results[second_next_index];
                next_record = patients_with_more_results[next_index];

                if(second_next_record.patientID == current_record.patientID){
                    if(current_record.status == "suppressed" || current_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+current_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    }
                    if(current_record.status == "suppressed" || next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    }
                    
                    if(second_next_record.status == "suppressed" || second_next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+second_next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+second_next_record.receiptDate;
                    }
                    

                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    valid_patient_results.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    patient_records_object = null;
                    i=i+2;
                    continue;
                }
            }
            //check last record
             if(next_index < array_size){
                next_record = patients_with_more_results[next_index];
                if(next_record.patient_id == current_record.patient_id){
                    if(current_record.status == "suppressed" || current_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+current_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+current_record.receiptDate;
                    }
                    if(current_record.status == "suppressed" || next_record.status == "notSuppressed"){
                        dummy_results = dummy_results +'\&'+next_record.status;
                        dummy_receipt_dates = dummy_receipt_dates +'\&'+next_record.receiptDate;
                    }
                    
                    //construct object to push
                    var patient_id = current_record.patientUniqueID;
                    sorted_patient_records_object = {
                        "patientID":patient_id,
                        "patientUniqueID":current_record.patientUniqueID,
                        "hub":current_record.hub,
                        "facility":current_record.facility,
                        "artNumber":current_record.artNumber,
                        "datesArrivedAtCPHL":dummy_receipt_dates.substr(1),
                        "results": dummy_results.substr(1)
                    };
                     //push object to array
                    valid_patient_results.push(sorted_patient_records_object);
                    dummy_receipt_dates = "";
                    dummy_results = "";
                    sorted_patient_records_object = null;
                    i=i+1;

                    continue;
                }
            }
            
        };
         return valid_patient_results;
    };

    var getPatientsWithInvalidResults = function(clean_results){
        var patients_with_invalid_results = [];
        

        var array_size = clean_results.length;
        for (var i = 0; i< array_size; i++) {
            var clean_results_object = clean_results[i];
            var uniformResult = getUniformResults(clean_results_object.result);
            var invalid_patient_record = null;
            if(clean_results_object.result != null && uniformResult == 'rejected'){
                invalid_patient_record = {
                    "patientID":clean_results_object.patientID,
                    "vlSampleID":clean_results_object.vlSampleID,
                    "created":clean_results_object.created,
                    "patientUniqueID":clean_results_object.patientUniqueID,
                    "result":clean_results_object.result,
                    "status":getUniformResults(clean_results_object.result),
                    "hub":clean_results_object.hub,
                    "facility":clean_results_object.facility,
                    "collectionDate":clean_results_object.collectionDate,
                    "receiptDate":clean_results_object.receiptDate,
                    "artNumber":clean_results_object.artNumber,
                    "phone":clean_results_object.phone
                };
                patients_with_invalid_results.push(invalid_patient_record);
            }//end for loop
        }

        return patients_with_invalid_results;
    };
    
    //=====Date Customizations====================
    Date.isLeapYear = function (year) { 
        return (((year % 4=== 0)&&(year % 100 !== 0)) || (year % 400 === 0)); 
    };

    Date.getDaysInMonth = function (year, month) {
        return [31, (Date.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
    };

    Date.prototype.isLeapYear = function () { 
        return Date.isLeapYear(this.getFullYear()); 
    };

    Date.prototype.getDaysInMonth = function () { 
        return Date.getDaysInMonth(this.getFullYear(), this.getMonth());
    };

    Date.prototype.addMonths = function (value) {
        var n = this.getDate();
        this.setDate(1);
        this.setMonth(this.getMonth() + value);
        this.setDate(Math.min(n, this.getDaysInMonth()));
    return this;
    };
    //=====End Date customization====================
    var getRecommendedRetestDate =function(collectionDate,suppressionRetestType){
        //parse string to Date
        var collection_date_object = new Date(collectionDate);

        var recommended_retest_date = null;
        //add 6 months if retest_not_suppressing
        if(suppressionRetestType == "retest_not_suppressing"){
            recommended_retest_date = collection_date_object.addMonths(6);
        }else if(suppressionRetestType == "retest_suppressing"){//add 12 months if retest_suppressing
            recommended_retest_date = collection_date_object.addMonths(12);
        }
            
        var recommended_retest_month = recommended_retest_date.getMonth()+1;//Months= 0 - 11
        if(recommended_retest_month < 10){
            recommended_retest_month = "0"+recommended_retest_month;
        }
        var recommended_retest_day = recommended_retest_date.getDate();
        if(recommended_retest_day < 10){
            recommended_retest_day = "0"+recommended_retest_day;
        }
        var recommended_retest_date_string = recommended_retest_date.getFullYear()+"-"+recommended_retest_month+"-"+recommended_retest_day;

        return recommended_retest_date_string;
    };

    var getRetestedDate = function(patientUniqueID,recommended_retest_date,patient_retested_dates){
        var recommended_retest_date_object = new Date(recommended_retest_date);
        var patient_retested_date = null;
        for(var index=0; index < patient_retested_dates.length; index++){
            patient_retested_date = null;
            var next_index = index + 1;

            //date format: Y-m-d e.g. 2016-04-01
            if(patientUniqueID == patient_retested_dates[index].patientUniqueID){
                patient_retested_date = patient_retested_dates[index].collectionDate;
                var dummy_date_object = new Date(patient_retested_date);
                if(recommended_retest_date_object.getTime() <= dummy_date_object.getTime()){

                    return patient_retested_date;
                }else if(patientUniqueID != patient_retested_dates[next_index].patientUniqueID){
                    patient_retested_date = null;
                    break;//stops looping over irrelevant data since all info about this patient has been exhausted
                }
            }
            
        }//end loop
        return patient_retested_date;
    };
    var getNumberOfMonthsMissed = function(recommended_retest_date,retested_date){
            



            var months = 0;
            var recommended_retest_date_object = new Date(recommended_retest_date);
            if(retested_date == null){
                months = 2;
                return months;
            }
                
            var retested_date_object = new Date(retested_date);

            

            var today = new Date();
            if(recommended_retest_date_object.getTime() >= today.getTime()){
                return -1;
            }
            
            
            while((recommended_retest_date_object.getMonth()+''+recommended_retest_date_object.getFullYear()) != 
                    (retested_date_object.getMonth()+''+retested_date_object.getFullYear())) {
                months++;
                recommended_retest_date_object.setMonth(recommended_retest_date_object.getMonth()+1);
            }
            return months;
            
            
        
    };
    var getColour = function(patientUniqueID,recommended_retest_date){
        var retested_date = getRetestedDate(patientUniqueID,recommended_retest_date,$scope.patient_retested_dates);
        
        var months_missed = getNumberOfMonthsMissed(recommended_retest_date,retested_date);

        var colour = "";

        if(months_missed >= 2){
            colour = "two-months-over-due";
        }else if(months_missed >= 1){
            colour = "one-month-over-due";
        }else if(months_missed >= 0){
            colour = "due";
        }else if(months_missed == -1){
            colour = "not-due";
        }
        return colour;
    };
    var getRetestNSPatients = function(clean_results){
        var retestNSPatients = [];
        var array_size = clean_results.length;

         for (var i = 0; i< array_size; i++) {
            var clean_results_object = clean_results[i];
            var uniformResult = getUniformResults(clean_results_object.result);
            var retest_ns_patient_record = null;


            if(clean_results_object.result != null && uniformResult == 'notSuppressed'){
                var suppressionRetestType = "retest_not_suppressing";
                var recommended_retest_date = getRecommendedRetestDate(clean_results_object.collectionDate,suppressionRetestType);
                retest_ns_patient_record = {
                    "patientID":clean_results_object.patientID,
                    "vlSampleID":clean_results_object.vlSampleID,
                    "created":clean_results_object.created,
                    "patientUniqueID":clean_results_object.patientUniqueID,
                    "result":clean_results_object.result,
                    "status":uniformResult,
                    "hub":clean_results_object.hub,
                    "facility":clean_results_object.facility,
                    "collectionDate":clean_results_object.collectionDate,
                    "recommendedRetestDate":recommended_retest_date,
                    "receiptDate":clean_results_object.receiptDate,
                    "artNumber":clean_results_object.artNumber,
                    "phone":clean_results_object.phone
                    
                };
                retestNSPatients.push(retest_ns_patient_record);
            }//end for loop
        }

        return retestNSPatients;
    };

    var getRetestSuppressingPatients = function(clean_results){
        var retestSuppressingPatients = [];
        var array_size = clean_results.length;

         for (var i = 0; i< array_size; i++) {
            var clean_results_object = clean_results[i];
            var uniformResult = getUniformResults(clean_results_object.result);
            var retest_suppressing_patient_record = null;


            if(clean_results_object.result != null && uniformResult == 'suppressed'){
                var suppressionRetestType = "retest_suppressing";
                var recommended_retest_date = getRecommendedRetestDate(clean_results_object.collectionDate,suppressionRetestType);
                retest_suppressing_patient_record = {
                    "patientID":clean_results_object.patientID,
                    "vlSampleID":clean_results_object.vlSampleID,
                    "created":clean_results_object.created,
                    "patientUniqueID":clean_results_object.patientUniqueID,
                    "result":clean_results_object.result,
                    "status":uniformResult,
                    "hub":clean_results_object.hub,
                    "facility":clean_results_object.facility,
                    "collectionDate":clean_results_object.collectionDate,
                    "recommendedRetestDate":recommended_retest_date,
                    "receiptDate":clean_results_object.receiptDate,
                    "artNumber":clean_results_object.artNumber,
                    "phone":clean_results_object.phone
                    
                };
                retestSuppressingPatients.push(retest_suppressing_patient_record);
            }//end for loop
        }

        return retestSuppressingPatients;
    };
    var getData=function(){
            $scope.loading = true;
            var prms = {};
            
            prms.fro_date = $scope.fro_date;
            prms.to_date = $scope.to_date;
            
            $http({method:'GET',url:"/suppression_trends/reports",params:prms}).success(function(data) {
                
                //1. remove duplicates
                var clean_results = removeDuplicates(data.patient_results);
                
                //2. remove those with One VL test
                var patients_with_more_results = removeFirstTimeVLTesters(clean_results);
                //3. add status(NS or S)

                //4. generate one row for each patient showing Prev[result, date of collection, receipt date] and Recent Result
                var sorted_patient_records = sortPatientRecords(patients_with_more_results);
                
                //calculate those
                var suppression_trend = getSuppressionTrend(sorted_patient_records);

                //VPatients
                //var valid_patient_results = getValidPatients();

                //APatients
                //var all_patient_results = getAllPatientsResults(patients_with_more_results);

                //rejections
                //var patients_with_invalid_results = getPatientsWithInvalidResults(clean_results);

                $scope.previouslyNonSuppressingCurrentlyNotSuppressing=suppression_trend.previouslyNotSuppressingCurrentlyNotSuppressing;
                $scope.previouslyNonSuppressingCurrentlySuppressing=suppression_trend.previouslyNotSuppressing - suppression_trend.previouslyNotSuppressingCurrentlyNotSuppressing;
                $scope.previouslyNotSuppressing = suppression_trend.previouslyNotSuppressing;

                $scope.previouslySuppressingCurrentlyNotSuppressing = suppression_trend.previouslySuppressingCurrentlyNotSuppressing;
                $scope.previouslySuppressingCurrentlySuppressing = suppression_trend.previouslySuppressing - suppression_trend.previouslySuppressingCurrentlyNotSuppressing;
                $scope.previouslySuppressing = suppression_trend.previouslySuppressing;

                $scope.previouslyNScurrentlyNS = suppression_trend.previouslyNScurrentlyNS;
                $scope.previouslyScurrentlyNS = suppression_trend.previouslyScurrentlyNS;

                var patients_with_more_results_and_no_rejections = removeRejections(patients_with_more_results);
                $scope.validPatientResults = getValidPatientResults(patients_with_more_results_and_no_rejections);
                $scope.allPatientsResults = getAllPatientsResults(patients_with_more_results);

                $scope.patientsWithInvalidResults = getPatientsWithInvalidResults(clean_results);

                //$scope.patient_retested_dates = data.patient_retested_dates;
                $scope.retestNSPatients = getRetestNSPatients(patients_with_more_results);
                $scope.retestSuppressingPatients = getRetestSuppressingPatients(patients_with_more_results);

                $scope.filtered = $scope.date_filtered;    
                $scope.loading = false;
                
               });
    };

    getData();
    

    $scope.dateFilter=function(){
        if($scope.fro_date!="all" && $scope.to_date!="all"){
            var fro_nr=Number($scope.fro_date);//numberise the fro date
            var to_nr=Number($scope.to_date);//numberise the to date

            if(fro_nr>to_nr){
                alert("Please make sure that the FRO date is earlier than the TO date");
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

            case "age_group":
            $scope.filter_age_group[$scope.age_group]=age_group_json[$scope.age_group];
            $scope.params.age_ids.push(Number($scope.age_group));
            $scope.age_group='all';
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
        }

        delete $scope.filter_districts["all"];
        delete $scope.filter_hubs["all"];
        delete $scope.filter_age_group["all"];
        delete $scope.filter_gender["all"];
        delete $scope.filter_regimen["all"];
        delete $scope.filter_line["all"];
        delete $scope.filter_indication["all"];

        getData();

        //generalFilter(); //filter the results for each required event
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

            case "age_group": 
            delete $scope.filter_age_group[nr];
            $scope.params.age_ids=rmveFrmArr(nr,$scope.params.age_ids);
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
        }
        //$scope.filter(mode);
        getData();

    };

    $scope.clearAllFilters=function(){
        $scope.filter_districts={};
        $scope.filter_hubs={};
        $scope.filter_age_group={};
        $scope.filter_gender={};
        $scope.filter_regimen={};
        $scope.filter_line={};
        $scope.filter_indication={};
        $scope.filter_duration=$scope.init_duration;
        $scope.filtered=false;
        $scope.date_filtered=false;
        $scope.fro_date="all";
        $scope.to_date="all";
        $scope.params = {
                'districts':[],'hubs':[],'age_ids':[],'genders':[],
                'regimens':[],'lines':[],'indications':[]
            };
        getData();
        //generalFilter();
    };


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
        var facility_labels = scopeInstance.labels.facilities;
        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var facility_instance = {
                facility_name : facility_labels[facilityRecord._id],
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
                
                suppression_rate : Math.round(((districtRecord.suppressed/districtRecord.valid_results)*100),1)
            }

            export_district_suppression_numbers.push(district_instance);
        }

        return export_district_suppression_numbers;
    }

    function exportFacilitySuppressionNumbers(scopeInstance){
        var export_facility_numbers = [];
        var facility_labels = scopeInstance.labels.facilities;
        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var facility_instance = {
                facility_name : facility_labels[facilityRecord._id],
                valid_results : facilityRecord.valid_results,
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
        var facility_labels = scopeInstance.labels.facilities;
        var facility_numbers_from_scope = scopeInstance.facility_numbers;

        for( var index = 0; index < facility_numbers_from_scope.length; index++){
            var facilityRecord = facility_numbers_from_scope[index];

            var facility_instance = {
                facility_name : facility_labels[facilityRecord._id],
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