
//angular stuff

/*
Authors
Name                        @       Period      Role       
Logan Smith                 CHAI    2015(v1)    Interface Design, Q/A
Lena Derisavifard           CHAI    2015(v1)    Req Specification, Q/A, UAT
Kitutu Paul                 CHAI    2015(v1)    System development
Sam Otim                    CHAI    2015(v1)    System development
Simon Peter Muwanga         METS    2016(v2)    System Development


Credit to CHAI Uganda,METS Program-School of Public Health Makerere University, CPHL and stakholders
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
 
    $scope.labels.facilities_details=[];
    $scope.labels.regimens = [];
    $scope.labels.regimens2 = [];
    $scope.labels.indications = t_indication_json;

    $scope.labels.suppression={'yes':'Suppressed','no':'Not Suppressed'};
    var vvvrrr = 0;

    $scope.districts2 = [];
    $scope.hubs = []
    $scope.age_group_slct = age_group_json;




    /*
    * gets the latest lab tests for all patients
    */
    var getTheLatestTests = function(patient_results){
        var clean_results =[];
        var temporary_patient_samples=[];
        for (var i = 1; i < patient_results.length; i++) {
            
           

            var previous_index = i - 1;
            var current_index=i;
            var next_index= i+1;
            var last_index=patient_results.length - 1;

            var current_patient_results_object = patient_results[i];
            var previous_patient_results_object = patient_results[previous_index];
            var next_patient_results_object=null;
           

            if(next_index < last_index){//initialise next_patient_object
                next_patient_results_object = patient_results[next_index];
            }

            if(previous_patient_results_object.patient_unique_id==current_patient_results_object.patient_unique_id){
                temporary_patient_samples.push(previous_patient_results_object);
            }else if(current_index == last_index && 
                previous_patient_results_object.patient_unique_id==current_patient_results_object.patient_unique_id){
                temporary_patient_samples.push(current_patient_results_object);
                var latest_patient_sample_record=getLatestPatientSampleRecord(temporary_patient_samples);
                clean_results.push(latest_patient_sample_record);
            }else if(previous_patient_results_object.patient_unique_id != current_patient_results_object.patient_unique_id){
                if(temporary_patient_samples.length > 1){
                    var latest_patient_sample_record=getLatestPatientSampleRecord(temporary_patient_samples);
                    clean_results.push(latest_patient_sample_record);
                    temporary_patient_samples=[];
                }else if(temporary_patient_samples.length == 0){
                    clean_results.push(current_patient_results_object);
                }
            }else if(previous_index ==0 &&
                previous_patient_results_object.patient_unique_id != current_patient_results_object.patient_unique_id){
                clean_results.push(previous_patient_results_object);
            }else if( previous_patient_results_object.patient_unique_id != current_patient_results_object.patient_unique_id
                && next_patient_results_object.patient_unique_id == current_patient_results_object.patient_unique_id
                && next_index < last_index
                ){//this can be remove to only get people who are testing for the first time in this period of time/year/month
                clean_results.push(current_patient_results_object);
            }
            
            
        };//end for loop
        return clean_results;
    };
    var convertStringIntoDate=function(date_string){
        var date_array=date_string.split("-");

        var year=date_array[0];
        var month=date_array[1] - 1;
        var day=date_array[2];

        var new_date = new Date(year,month,day);
        return new_date;
    };
    var compareDates =function(a,b){//getTime() returns numberOfMilliSeconds from 1970
        if(a.getTime() == b.getTime()){
            return 0;
        }else if(a.getTime() > b.getTime()){
            return 1;
        }else if(a.getTime() < b.getTime()){
            return -1;
        }

    };

    var convertYearMonthIntoDateString=function(year_month){

        var dummy_string=""+year_month;
        var year_month_string = dummy_string.split("");
        var year_string=""+year_month_string[0]+""+year_month_string[1]+""+year_month_string[2]+""+year_month_string[3];
        var month_string=""+year_month_string[4]+""+year_month_string[5];
        var day_string="01";
        var date_string=year_string+"-"+month_string+"-"+day_string;
        return date_string;
    };
    var convertYearMonthIntoCustomDateString=function(year_month,day){

        var dummy_string=""+year_month;
        var year_month_string = dummy_string.split("");
        var year_string=""+year_month_string[0]+""+year_month_string[1]+""+year_month_string[2]+""+year_month_string[3];
        var month_string=""+year_month_string[4]+""+year_month_string[5];
        var day_string=""+day;
        var date_string=year_string+"-"+month_string+"-"+day_string;
        return date_string;
    };
    var getLatestPatientSampleRecord = function(sample_records_of_one_patient){
        var latest_sample_record=null;
        for (var i = 0; i < sample_records_of_one_patient.length; i++) {
            if(i == 0){
                latest_sample_record=sample_records_of_one_patient[i];
            }
      
            //use year_month in case date_collected is not yet created in the object
            if (!latest_sample_record.hasOwnProperty('date_collected')){
                latest_sample_record.date_collected=convertYearMonthIntoDateString(latest_sample_record.year_month);
            }

            var latest_date=convertStringIntoDate(latest_sample_record.date_collected);


            var current_sample_record = sample_records_of_one_patient[i];
            if (!current_sample_record.hasOwnProperty('date_collected')){
                current_sample_record.date_collected=convertYearMonthIntoDateString(current_sample_record.year_month);
            }
            var current_date = convertStringIntoDate(current_sample_record.date_collected);
            
            // -1 if a < b, 
            // 0 if a = b,
            // 1 if a > b, 
            // NaN if a or b is an illegal date
            var date_flag=compareDates(latest_date,current_date);
            if(date_flag == -1){
                latest_sample_record=current_sample_record;
            }

        }

        return latest_sample_record;
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

    var sortPatientRecordsWithMostRecentTests = function(tests_of_patient){
        var sorted_tests_of_patient=[];
        var dummy_test=null;
        dummy_test = tests_of_patient[0];
        var last_index = tests_of_patient.length - 1;
        for (var i = 1; i < tests_of_patient.length; i++){
            var current_test = tests_of_patient[i];
            var current_date = convertStringIntoDate(current_test.date_collected);
            var dummy_date = convertStringIntoDate(dummy_test.date_collected);

            var comparison_result_flag = compareDates(dummy_date,current_date);
            if(comparison_result_flag == 1){//Dummy_date is the longest date from 1970, ... it is the latest
                sorted_tests_of_patient.push(dummy_test);
                dummy_test = current_test;
            }else if(comparison_result_flag == -1){//current date is the latest.
                sorted_tests_of_patient.push(current_test);

            }//end if else

            if(comparison_result_flag == 1 && i ==last_index){
                sorted_tests_of_patient.push(dummy_test);
            }else if(comparison_result_flag == -1 && i ==last_index){
                sorted_tests_of_patient.push(current_test);
            }
        }//end for loop

        return sorted_tests_of_patient;
    };

    var getTwoMostRecentTestsForAllPatients = function(valid_patient_results){
        var all_patients_two_most_recent_tests_array=[];
        var dummy_patient_array=[];
        var dummy_patient_record=null;

        if(valid_patient_results.length > 0)
            dummy_patient_record = valid_patient_results[0];

        for (var i = 0; i < valid_patient_results.length; i++){

            var current_record = valid_patient_results[i];
            if(current_record.patient_unique_id == dummy_patient_record.patient_unique_id){
                //if collection date is empty, use year_month

                if(current_record.date_collected == null){
                    var collection_date_string = convertYearMonthIntoDateString(current_record.year_month);
                    current_record.date_collected = collection_date_string;
                    current_record.date_received = convertYearMonthIntoCustomDateString(current_record.year_month,"15");
                }
                
                dummy_patient_array.push(current_record);
            }else if(current_record.patient_unique_id != dummy_patient_record.patient_unique_id){
                //process dummy_patient_array: 
                //sort the array by date of collection, pick the top two dates, create the suppression_trend object
                if(dummy_patient_array.length <2){
                    dummy_patient_array=[];
                    dummy_patient_record=current_record;
                    continue;
                }
                    
                var sorted_tests_of_patient=[];
                sorted_tests_of_patient = sortPatientRecordsWithMostRecentTests(dummy_patient_array);

                var recent_test_object=sorted_tests_of_patient[0];
                var previous_test_object=sorted_tests_of_patient[1];

                

                var two_most_recent_tests_object = {
                    "patient_id":dummy_patient_record.patient_unique_id,
                    "facility_id":dummy_patient_record.facility_id,
                    "art_number":(typeof dummy_patient_record.art_number === 'undefined')?'null':dummy_patient_record.art_number,
                    "patient_unique_id":(typeof dummy_patient_record.patient_unique_id === 'undefined')?'null':dummy_patient_record.patient_unique_id,

                    "previous_collection_date":(typeof previous_test_object.date_collected === 'undefined')?'null':previous_test_object.date_collected,
                    "prevoius_receipt_date":(typeof previous_test_object.date_received === 'undefined')?'null':previous_test_object.date_received,
                    "previous_alpha_numeric_result":(typeof previous_test_object.alpha_numeric_result === 'undefined')?'null':previous_test_object.alpha_numeric_result,
                    "previous_suppression_status":(typeof previous_test_object.suppression_status === 'undefined')?'null':previous_test_object.suppression_status,

                    "recent_collection_date":(typeof recent_test_object.date_collected === 'undefined')?'null':recent_test_object.date_collected,
                    "recent_receipt_date":(typeof recent_test_object.date_received === 'undefined')?'null':recent_test_object.date_received,
                    "recent_alpha_numeric_result":(typeof recent_test_object.alpha_numeric_result === 'undefined')?'null':recent_test_object.alpha_numeric_result,
                    "recent_suppression_status":(typeof recent_test_object.suppression_status === 'undefined')?'null':recent_test_object.suppression_status,
                    "phone": (typeof dummy_patient_record.phone === 'undefined')?'null':dummy_patient_record.phone

                };

                all_patients_two_most_recent_tests_array.push(two_most_recent_tests_object)
                dummy_patient_array=[];
                
                //reset dummy_patient_record to current
                dummy_patient_record=current_record;
            }

        }//end For Loop

        return all_patients_two_most_recent_tests_array;

    };

    var getPreviouslyNScurrentlyNS = function(sorted_patient_records){

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
            if(sorted_patient_records_object.previous_suppression_status == "no"){
                previouslyNotSuppressing ++;
                if(sorted_patient_records_object.recent_suppression_status == "yes"){
                    previouslyNotSuppressingCurrentlyNotSuppressing ++;
                    previouslyNScurrentlyNS.push(sorted_patient_records_object);
                }
            }else if(sorted_patient_records_object.previous_suppression_status == "no"){
                previouslySuppressing++;
                if(sorted_patient_records_object.recent_suppression_status == "yes"){
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
        var patients_with_valid_results = [];
        

        var array_size = patients_with_more_results.length;
        for (var i = 0; i< array_size; i++) {
            var clean_results_object = patients_with_more_results[i];
            
            if(clean_results_object.sample_result_validity=='valid'){
                valid_patient_record = {
                    "vl_sample_id":clean_results_object.vl_sample_id,
                    "created":clean_results_object.date_created,
                    "patient_unique_id":clean_results_object.patient_unique_id,
                    "alpha_numeric_result":clean_results_object.alpha_numeric_result,
                    "suppression_status":clean_results_object.suppression_status,
                    "hub_id":clean_results_object.hub_id,
                    "facility_id":clean_results_object.facility_id,
                    "date_collected":clean_results_object.date_collected,
                    "date_received":clean_results_object.date_received,
                    "art_number":clean_results_object.art_number,
                    "phone_number":clean_results_object.phone_number,
                    "year_month":clean_results_object.year_month
                };
                patients_with_valid_results.push(valid_patient_record);
            }//end for loop
        }

        return patients_with_valid_results;
    };
    var getPatientsWithRejections = function(clean_results){
        var rejected_samples = [];
        

        var array_size = clean_results.length;
        for (var i = 0; i< array_size; i++) {
            var clean_results_object = clean_results[i];
            
            if(clean_results_object.rejection_reason !='UNKNOWN'){
                rejected_sample_record = {
                    "patientID":clean_results_object.patient_unique_id,
                    "vl_sample_id":clean_results_object.vl_sample_id,
                    "created":clean_results_object.date_created,
                    "patient_unique_id":clean_results_object.patient_unique_id,
                    "hub_id":clean_results_object.hub_id,
                    "facility_id":clean_results_object.facility_id,
                    "date_collected":clean_results_object.date_collected,
                    "date_received":clean_results_object.date_received,
                    "art_number":clean_results_object.art_number,
                    "phone_number":clean_results_object.phone_number,
                    "rejection_category":clean_results_object.rejection_category,
                    "rejection_reason":clean_results_object.rejection_reason
                };
                rejected_samples.push(rejected_sample_record);
            }//end for loop
        }

        return rejected_samples;
    };
    var getPatientsWithInvalidResults = function(clean_results){
        var patients_with_invalid_results = [];
        

        var array_size = clean_results.length;
        for (var i = 0; i< array_size; i++) {
            var clean_results_object = clean_results[i];
            
            if(clean_results_object.sample_result_validity=='valid'){
                invalid_patient_record = {
                    "patientID":clean_results_object.patient_unique_id,
                    "vlSampleID":clean_results_object.vl_sample_id,
                    "created":clean_results_object.date_created,
                    "patient_unique_id":clean_results_object.patientUniqueID,
                    "alpha_numeric_result":clean_results_object.alpha_numeric_result,
                    "suppression_status":clean_results_object.suppression_status,
                    "hub_id":clean_results_object.hub_id,
                    "facility_id":clean_results_object.facility_id,
                    "date_collected":clean_results_object.date_collected,
                    "date_received":clean_results_object.date_received,
                    "art_number":clean_results_object.art_number,
                    "phone_number":clean_results_object.phone_number
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
        var collection_date_object = convertStringIntoDate(collectionDate);

        var recommended_retest_date = null;
        //add 6 months if retest_not_suppressing
        if(suppressionRetestType == "retest_not_suppressing"){
            recommended_retest_date = collection_date_object.addMonths(3);
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
            var retest_ns_patient_record = null;


            if(clean_results_object.suppression_status == 'no'){
                //use year_month in case date_collected is not yet created in the object
                if (!clean_results_object.hasOwnProperty('date_collected')){
                    clean_results_object.date_collected=convertYearMonthIntoDateString(clean_results_object.year_month);
                }

                var suppressionRetestType = "retest_not_suppressing";
                var recommended_retest_date = getRecommendedRetestDate(clean_results_object.date_collected,suppressionRetestType);
                retest_ns_patient_record = {
                    "patient_id":clean_results_object.patient_unique_id,
                    "vl_sample_id":clean_results_object.vl_sample_id,
                    "created":clean_results_object.created,
                    "patient_unique_id":clean_results_object.patient_unique_id,
                    "alpha_numeric_result":clean_results_object.alpha_numeric_result,
                    "suppression_status":clean_results_object.suppression_status,
                    "hub_id":clean_results_object.hub_id,
                    "facility_id":clean_results_object.facility_id,
                    "date_collected":clean_results_object.date_collected,
                    "recommended_retest_date":recommended_retest_date,
                    "date_received":clean_results_object.date_received,
                    "art_number":clean_results_object.art_number,
                    "phone_number":clean_results_object.phone_number
                    
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
            var retest_suppressing_patient_record = null;


            if(clean_results_object.suppression_status == 'yes'){
                //use year_month in case date_collected is not yet created in the object
                if (!clean_results_object.hasOwnProperty('date_collected')){
                    clean_results_object.date_collected=convertYearMonthIntoDateString(clean_results_object.year_month);
                }

                var suppressionRetestType = "retest_suppressing";
                var recommended_retest_date = getRecommendedRetestDate(clean_results_object.date_collected,suppressionRetestType);
                retest_suppressing_patient_record = {
                    "patient_id":clean_results_object.patient_unique_id,
                    "vl_sample_id":clean_results_object.vl_sample_id,
                    "created":clean_results_object.created,
                    "patient_unique_id":clean_results_object.patient_unique_id,
                    "alpha_numeric_result":clean_results_object.alpha_numeric_result,
                    "suppression_status":clean_results_object.suppression_status,
                    "hub_id":clean_results_object.hub_id,
                    "facility_id":clean_results_object.facility_id,
                    "date_collected":clean_results_object.date_collected,
                    "recommended_retest_date":recommended_retest_date,
                    "date_received":clean_results_object.date_received,
                    "art_number":clean_results_object.art_number,
                    "phone_number":clean_results_object.phone_number
                };
                retestSuppressingPatients.push(retest_suppressing_patient_record);
            }//end for loop
        }

        return retestSuppressingPatients;
    };

    $http.get("/other_data/").success(function(data){
        //console.log("Ehealth at chai rocks 1 "+JSON.stringify(data.facilities));
        for(var i in data.hubs){
            var obj = data.hubs[i];
            $scope.hubs.push({"id":obj.id,"name":obj.name});
        }

        for(var i in data.new_hubs){
            var obj = data.new_hubs[i];
            
            $scope.hubs.push({"id":obj.id,"name":obj.hub});
        }

        for(var i in data.facilities){
            var obj = data.facilities[i];
          
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
    });

    var getData=function(){
            $scope.loading = true;
            var prms = {};
            
            prms.fro_date = $scope.fro_date;
            prms.to_date = $scope.to_date;
            
            $http({method:'GET',url:"/suppression_trends/reports",params:prms}).success(function(data) {
                
                //1. remove duplicates
                var clean_results = null;
                
                //2. remove those with One VL test
                var patients_with_more_results = null
                //3. add status(NS or S)

                //4. generate one row for each patient showing Prev[result, date of collection, receipt date] and Recent Result
                var sorted_patient_records =null;
                
                

                var valid_patient_results = getValidPatientResults(data.all_patient_results);
                $scope.validPatientResults = valid_patient_results;




                var all_patients_two_most_recent_tests_array =getTwoMostRecentTestsForAllPatients(valid_patient_results);
                

                //calculate those
                var suppression_trend =getSuppressionTrend(all_patients_two_most_recent_tests_array);

                
                $scope.previouslyNonSuppressingCurrentlyNotSuppressing=suppression_trend.previouslyNotSuppressingCurrentlyNotSuppressing;
                $scope.previouslyNonSuppressingCurrentlySuppressing=suppression_trend.previouslyNotSuppressing - suppression_trend.previouslyNotSuppressingCurrentlyNotSuppressing;
                $scope.previouslyNotSuppressing = suppression_trend.previouslyNotSuppressing;

                $scope.previouslySuppressingCurrentlyNotSuppressing =suppression_trend.previouslySuppressingCurrentlyNotSuppressing;
                $scope.previouslySuppressingCurrentlySuppressing = suppression_trend.previouslySuppressing - suppression_trend.previouslySuppressingCurrentlyNotSuppressing;
                $scope.previouslySuppressing = suppression_trend.previouslySuppressing;

                $scope.previouslyNScurrentlyNS = suppression_trend.previouslyNScurrentlyNS;
                $scope.previouslyScurrentlyNS = suppression_trend.previouslyScurrentlyNS;




                $scope.allPatientsResults = data.all_patient_results;

                $scope.patientsWithRejections = getPatientsWithRejections(data.all_patient_results);


                var latest_tests=getTheLatestTests(data.all_patient_results);
                $scope.retestNSPatients = getRetestNSPatients(latest_tests);
                $scope.retestSuppressingPatients = getRetestSuppressingPatients(latest_tests);


                $scope.current_timestamp = getCurrentTimeStamp();
                $scope.export_retest_ns_results=exportRetestNotSuppressingPatients($scope);
                $scope.export_retest_suppressing=exportRetestSuppressingPatients($scope);
                $scope.export_rejection_results=exportRejectionResults($scope);
                $scope.export_valid_patient_results = exportValidPatientResults($scope);
                $scope.export_all_patient_results = exportAllPatientResults($scope);

                $scope.filtered = $scope.date_filtered;    
                $scope.loading = false;
                
               });
    };


    getData();
    function exportRetestNotSuppressingPatients(scopeInstance){
       
        var retest_not_suppressing_patients_array = [];
        var facility_labels = scopeInstance.labels.facilities_details;
        var retest_results_from_scope = scopeInstance.retestNSPatients;

        for( var index = 0; index < retest_results_from_scope.length; index++){
            var patientRecord = retest_results_from_scope[index];

            var retest_not_suppressing_patient_object={

                patient_unique_id:patientRecord.patient_unique_id,
                
                facility: (isEmpty(facility_labels[patientRecord.facility_id]))?'null':facility_labels[patientRecord.facility_id].dhis2_name,
                art_number:(typeof patientRecord.art_number === 'undefined')?'null':patientRecord.art_number,
                vl_sample_id:(typeof patientRecord.vl_sample_id === 'undefined')?'null':patientRecord.vl_sample_id,
                
                date_collected:(typeof patientRecord.date_collected === 'undefined')?'null':patientRecord.date_collected,
                date_received:(typeof patientRecord.date_received === 'undefined')?'null':patientRecord.date_received,
                

                alpha_numeric_result:(typeof patientRecord.alpha_numeric_result === 'undefined')?'null':patientRecord.alpha_numeric_result,
                recommended_retest_date:(typeof patientRecord.recommended_retest_date  === 'undefined')?'null':patientRecord.recommended_retest_date,
                phone:(typeof patientRecord.phone_number === 'undefined')?'null':patientRecord.phone_number,

                action:'',
                comment:'',

            };


            retest_not_suppressing_patients_array.push(retest_not_suppressing_patient_object);
        }

        return retest_not_suppressing_patients_array;
    }
    function isEmpty(val){
            return (val === undefined || val == null || val.length <= 0) ? true : false;
    }
    function exportRetestSuppressingPatients(scopeInstance){
       
        var retest_suppressing_patients_array = [];
        var facility_labels = scopeInstance.labels.facilities_details;
        var retest_results_from_scope = scopeInstance.retestSuppressingPatients;


        for( var index = 0; index < retest_results_from_scope.length; index++){
            var patientRecord = retest_results_from_scope[index];



            var retest_suppressing_patient_object={

                patient_unique_id:(typeof patientRecord.patient_unique_id === 'undefined')?'null':patientRecord.patient_unique_id,
                
                //facility: (facility_labels[patientRecord.facility_id] === 'undefined')?facility_labels[patientRecord.facility_id].cphl_name:'',
                facility:(isEmpty(facility_labels[patientRecord.facility_id]))?'null':facility_labels[patientRecord.facility_id].dhis2_name,
                art_number:(typeof patientRecord.art_number === 'undefined')?'null':patientRecord.art_number,
                vl_sample_id:(typeof patientRecord.vl_sample_id === 'undefined')?'null':patientRecord.vl_sample_id,
                
                date_collected:(typeof patientRecord.date_collected === 'undefined')?'null':patientRecord.date_collected,
                date_received:(typeof patientRecord.date_received === 'undefined')?'null':patientRecord.date_received,
                

                alpha_numeric_result:(typeof patientRecord.alpha_numeric_result === 'undefined')?'null':patientRecord.alpha_numeric_result,
                recommended_retest_date:(typeof patientRecord.recommended_retest_date === 'undefined')?'null':patientRecord.recommended_retest_date,
                phone:(typeof patientRecord.phone_number === 'undefined')?'null':patientRecord.phone_number,

                action:'',
                comment:'',

            };


            retest_suppressing_patients_array.push(retest_suppressing_patient_object);
        }

        return retest_suppressing_patients_array;
    }
    function exportRejectionResults(scopeInstance){
       
        var rejection_results_array = [];
        var facility_labels = scopeInstance.labels.facilities_details;
        var rejection_results_from_scope = scopeInstance.patientsWithRejections;

        for( var index = 0; index < rejection_results_from_scope.length; index++){
            var patientRecord = rejection_results_from_scope[index];

            var rejection_result_object={

                patient_unique_id:patientRecord.patient_unique_id,
                vl_sample_id:patientRecord.vl_sample_id,
                facility: (isEmpty(facility_labels[patientRecord.facility_id]))?'null':facility_labels[patientRecord.facility_id].dhis2_name,
                art_number:(typeof patientRecord.art_number === 'undefined')?'null':patientRecord.art_number,
                date_collected:(typeof patientRecord.date_collected === 'undefined')?'null':patientRecord.date_collected,
                date_received:(typeof patientRecord.date_received)?'null':patientRecord.date_received,
                
                rejection_category:(typeof patientRecord.rejection_category === 'undefined')?'null':patientRecord.rejection_category,
                rejection_reason:(typeof patientRecord.rejection_reason === 'undefined')?'null':patientRecord.rejection_reason,

                alpha_numeric_result:(typeof patientRecord.alpha_numeric_result === 'undefined')?'null':patientRecord.alpha_numeric_result,
                suppression_status:(typeof patientRecord.suppression_status === 'undefined')?'null':patientRecord.suppression_status,
                phone:(typeof patientRecord.phone_number === 'undefined')?'null':patientRecord.phone_number,

                action:'',
                comment:'',

            };


            rejection_results_array.push(rejection_result_object);
        }

        return rejection_results_array;
    }
    
    function exportValidPatientResults(scopeInstance){
       
        var valid_patient_results_array = [];
        var facility_labels = scopeInstance.labels.facilities_details;
        var valid_patient_results_from_scope = scopeInstance.validPatientResults;

        for( var index = 0; index < valid_patient_results_from_scope.length; index++){
            var patientRecord = valid_patient_results_from_scope[index];

            var valid_patient_result_object={

                patient_unique_id:patientRecord.patient_unique_id,
                vl_sample_id:patientRecord.vl_sample_id,
                facility: (isEmpty(facility_labels[patientRecord.facility_id]))?'null':facility_labels[patientRecord.facility_id].dhis2_name,

                art_number:(typeof patientRecord.art_number === 'undefined')?'null':patientRecord.art_number,
                date_received:(typeof patientRecord.date_received === 'undefined')?'null':patientRecord.date_received,
                alpha_numeric_result:(typeof patientRecord.alpha_numeric_result === 'undefined')?'null':patientRecord.alpha_numeric_result,
                suppression_status:(typeof patientRecord.suppression_status === 'undefined')?'null':patientRecord.suppression_status

            };


            valid_patient_results_array.push(valid_patient_result_object);
        }

        return valid_patient_results_array;
    }

    function exportAllPatientResults(scopeInstance){
       
        var all_patient_results_array = [];
        var facility_labels = scopeInstance.labels.facilities_details;
        var all_patient_results_from_scope = scopeInstance.allPatientsResults;

        for( var index = 0; index < all_patient_results_from_scope.length; index++){
            var patientRecord = all_patient_results_from_scope[index];

            var all_patient_result_object={

                patient_unique_id:patientRecord.patient_unique_id,
                vl_sample_id:(typeof patientRecord.vl_sample_id === 'undefined')?'null':patientRecord.vl_sample_id,
                facility: (isEmpty(facility_labels[patientRecord.facility_id]))?'null':facility_labels[patientRecord.facility_id].dhis2_name,

                art_number:(typeof patientRecord.art_number === 'undefined')?'null':patientRecord.art_number,
                date_received:(typeof patientRecord.date_received === 'undefined')?'null':patientRecord.date_received,
                alpha_numeric_result:(typeof patientRecord.alpha_numeric_result === 'undefined')?'null':patientRecord.alpha_numeric_result,
                suppression_status:(typeof patientRecord.suppression_status === 'undefined')?'null':patientRecord.suppression_status

            };


            all_patient_results_array.push(all_patient_result_object);
        }

        return all_patient_results_array;
    }

    var removeRepeatingDates=function(patient_viral_loads){
        var clean_results =[];
        var last_index = patient_viral_loads.length -1;
        for (var i = 1; i < patient_viral_loads.length; i++) {
            var previous_index = i -1;
            var previous_record = patient_viral_loads[previous_index];
            var current_record = patient_viral_loads[i];

            if(previous_record.collectionDate != current_record.collectionDate){
                clean_results.push(previous_record);
            }
            if(i == last_index){
                clean_results.push(current_record);
                
            }
        }
        return clean_results;
    };
    var getResult=function(result){
     
            var newResult = 0.0;
            if(result == "" || result == null)
                return newResult;
            var dummy = result.replace(/\s/g, '');
            if(dummy=="Notdetected"){
                newResult = 20.0;
            }
            else if(dummy=="Targetnotdetected"){
                newResult = 20.0;
            }else if(dummy.startsWith("-1")){
                        newResult = -1;
                    }else if(dummy.startsWith("4442")){
                        newResult = -4442
                    }else if(dummy.startsWith("3109")){
                        newResult = -3109;
                    }else if(dummy.startsWith("3110")){
                        newResult = -3110;
                    }else if(dummy.startsWith("3118")){
                        newResult = -3118;
                    }else if(dummy.startsWith("3119")){
                        newResult = -3119;
                    }else if(dummy.startsWith("3153")){
                        newResult = -3153;
                    }else if(dummy.startsWith("4408")){
                        newResult = -4408;
                    }else if(dummy.startsWith("3130")){
                        newResult = -3130;
                    }else if(dummy.startsWith("<75Copies")){
                        newResult = 74;
                    }else if(dummy.startsWith("<75copies")){
                        newResult = 74;
                    }else if(dummy.startsWith("<550Copies")){
                        newResult = 549;
                    }else if(dummy.startsWith(">10,000,000Copies")){
                        newResult = 10000001;
                    }else if(dummy.startsWith("<150Copies")){
                        newResult = 149;
                    }else if (dummy.includes("Log")) {////logs
                        //other numbers
                        var originalStr = dummy;
                        dummy = dummy.match(/[0-9]+/g);
                        var newfloat = null;
                        if(dummy.length > 1){
                            newfloat = dummy[0]+'.'+dummy[1];
                        }else{
                            newfloat = dummy[0];
                        }
                         
                        var powerof = parseFloat(newfloat);
                        newResult = Math.pow(10,powerof);
                        if(originalStr.startsWith("<"))
                          newResult = parseInt(newResult) - 1;
                        else if(originalStr.startsWith(">"))
                            newResult = parseInt(newResult) + 1;

                    }else{
                        //other numbers
                         while(dummy.includes(",")){
                            dummy = dummy.replace(',','');
                        }
                        dummy = dummy.match(/[0-9]+/g);
                        newResult = parseInt(dummy);
                    }
        return newResult;
    };
    var getMonthAndYear =function(datestring){
            var month=datestring.slice(5,7);
            var year = datestring.slice(2,4);
            switch(month){
                case "01":
                    monthString = "Jan";
                    break;
                case "02":
                    monthString = "Feb";
                    break;
                case "03":
                    monthString = "Mar";
                    break;
                case "04":
                  monthString = "Apr";
                  break;

                case "05":
                    monthString = "May";
                    break;

                case "06":
                    monthString = "Jun";
                    break;

                case "07":
                    monthString = "Jul";
                    break;
                case "08":
                    monthString = "Aug";
                    break;
                case "09":
                    monthString = "Sep";
                    break;
                case "10":
                  monthString = "Oct";
                  break;

                case "11":
                    monthString = "Nov";
                    break;

                case "12":
                    monthString = "Dec";
                    break;
            }
        return monthString+"-"+year;
    };
    var generateFormattedResults = function(no_repeating_dates) {
        
        var clean_results = [];

        for (var i = 0; i < no_repeating_dates.length; i++) {
            var current_record = no_repeating_dates[i];
            clean_results.push({x:current_record.collectionDate,y:getResult(current_record.result)});
        };
        
        return clean_results;
    };
    
    var getPatientViralLoads=function(progressMapType){
            $scope.loading = true;
            var prms = {};
            
            prms.patientID=$scope.patientID;
            
            $http({method:'GET',url:"/suppression_trends/patientviralloads",params:prms}).success(function(data) {
                //drawing of the graph
                 
                //remove repeating dates
                var no_repeating_dates = removeRepeatingDates(data.patient_viral_loads);

                //generate VL results
                $scope.progressMapData = generateFormattedResults(no_repeating_dates);
                //$scope.progressMapData = [{"2016-04-26":4000},{"2016-07-21":900}];
                
                
                try{
                    $scope.drawProgessMap(progressMapType);
                    console.log("finished fetching data");
                }catch(err){
                    console.log(err.message);
                }
                
                //end progressmap graph
            });
            $scope.loading = false;
    };//getPatientViralLoads
    $scope.loadProgressMap = function(patientID,patientUniqueID,progressMapType){
        $scope.patientID = patientID;
        $scope.patientUniqueID = patientUniqueID;
        getPatientViralLoads(progressMapType);

    };
    $scope.drawProgessMap = function(progressMapType){     
        var data=[{"key":"Selection","values":[],"color":"#f44336" }];
        var labels=[];
        var x=0;
        var y_vals=[];
        $scope.progressmaplabels=[];
            
            for (var index = 0; index < $scope.progressMapData.length; index++) {
                var current_record = $scope.progressMapData[index];

                //add values i.e. indeces to the x axis, and actual figures to the y axis
                data[0].values.push({x: index,y:current_record.y});

                //generate labels for the X-axis in the format MMM-YY
                labels.push(getMonthAndYear(current_record.x));
                $scope.progressmaplabels.push({x:getMonthAndYear(current_record.x)});
                //generate Y-axis values as well
                y_vals.push({y:current_record.y});
            };
        
        $scope.progressmapresults= y_vals;

        nv.addGraph(function() {
            var chart = nv.models.lineChart()
                        .margin({right: 50})
                        .useInteractiveGuideline(true)
                        .x(function(d) { return d.x })
                        .y(function(d) { return d.y })
                        .forceY(y_vals);
            
            chart.xAxis.tickFormat(function(d) {
                return labels[d];
            });

            chart.yAxis.tickFormat(d3.format(',.0d'));
        if( progressMapType =="retestNonSuppressedMap"){
             d3.select('#progressmap_retest_not_suppressed_id svg').datum(data).transition().duration(500).call(chart);
        }else if(progressMapType =="retestSuppressedMap"){
             d3.select('#progressmap_retest_suppressed_id svg').datum(data).transition().duration(500).call(chart);
        }
            return chart;
        });
    };
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