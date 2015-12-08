
//angular stuff
var app=angular.module('dashboard', ['datatables'], function($interpolateProvider) {
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
            var month_labels={1:'Jan',2:'Feb',3:'Mar',4:'Apr',5:'May',6:'Jun',7:'Jul',8:'Aug',9:'Sept',10:'Oct',11:'Nov',12:'Dec'};
            var arr=y_m.split('-');
            var yr=arr[0]||"";
            var mth=arr[1]||"";
            return month_labels[mth]+" '"+yr.slice(-2);
        }
    });




var ctrllers={};

ctrllers.DashController=function($scope,$http){
    $scope.identity = angular.identity;

    var districts_json={};
    var hubs_json={};
    var age_group_json={1:"0<5",2:"5-9",3:"10-18",4:"19-25",5:"26+"};    
    var facilities_json={};   
    var results_json={}; //to hold a big map will all processed data to later on be used in the generalFilter

    var vvvrrr=0;

    $scope.districts2=[];
    $scope.hubs2=[];

    
   /* $http.get("http://vl.trailanalytics.com/json/districts/amg299281fmlasd5dc02bd238919260fg6ad261d094zafd9/").success(function(data) {
        for(var i in data){
            console.log(" first piece is "+i+" and "+JSON.stringify(data[i]));
        }

        //Cross-Origin Request Blocked: The Same Origin Policy disallows reading the remote resource at http://vl.trailanalytics.com/json/districts/amg299281fmlasd5dc02bd238919260fg6ad261d094zafd9/. This can be fixed by moving the resource to the same domain or enabling CORS.
    
    })*/

    /*$http.get("{{ asset('/json/hh.json') }}").success(function(data) {
        for(var i in data){
            console.log(" first piece is "+i+" and "+JSON.stringify(data[i]));
        }
    })*/

   /* $http.get("http://vl.trailanalytics.com/json/data/amg299281fmlasd5dc02bd238919260fg6ad261d094zafd9/").success(function(datah){
        var lenght=count(datah);
        console.log("the length is "+lenght);
    })*/

    /*$http.get("../json/data.json").success(function(data) {
        console.log(JSON.stringify(data[0]));
        districts_json=data['districts']||{};
        hubs_json=data['hubs']||{};
        age_group_json=data['age_group']||{};
        facilities_json=data['facilities']||{};
  

        $scope.districts_slct=districts_json;
        $scope.hubs_slct=hubs_json;
        $scope.age_group_slct=age_group_json;

        var res=data['results']||{};
        for(var i in res){
           var that=res[i];
           var facility_details=facilities_json[that.facility_id];        
           results_json[i]=that;
           results_json[i].year_month=that.year+"-"+that.month;
           results_json[i].facility_name=facility_details.name;
           results_json[i].hub_id=facility_details.hub_id;
           results_json[i].district_id=facility_details.district_id;
           results_json[i].district_name=districts_json[facility_details.district_id];

        }

       generalFilter(); //call the filter for the first time
    });*/

    $http.get("../json/districts.20151204.json").success(function(data){
        for(var i in data){
            var dst=data[i];
            districts_json[dst.id]=dst.district;
            $scope.districts2.push({"id":dst.id,"name":dst.district});
        }
        console.log("number of districts:"+count($scope.districts2)+"  "+dist);
    });

    $http.get("../json/hubs.20151204.json").success(function(data){
        for(var i in data){
            var hb=data[i];
            hubs_json[hb.id]=hb.hub;
            $scope.hubs2.push({"id":hb.id,"name":hb.hub});
        }
        console.log("number of hubs:"+count(hubs_json));
    });

    $http.get("../json/facilities.20151204.json").success(function(data){
        for(var i in data){
            var f=data[i];
            facilities_json[f.id]={'id':f.id,'name':f.facility,'district_id':f.districtID,'hub_id':f.hubID};
        }
        console.log("number of facilities:"+count(facilities_json));
        console.log("first facility:"+JSON.stringify(facilities_json[2]));
    });

    $http.get("../json/data.20151206.json").success(function(data) {
       
        $scope.districts_slct=districts_json;
        $scope.hubs_slct=hubs_json;
        $scope.age_group_slct=age_group_json;

        var res=data||{};
        for(var i in res){
           var that=res[i];
           var facility_details=facilities_json[that.facility_id]||{};  
           results_json[i]={}; 
           results_json[i].year_month=that.year+"-"+that.month;
           results_json[i].facility_id=that.facility_id;
           results_json[i].age_group=that.age_group_id           
           results_json[i].facility_name=facility_details.name||"";
           results_json[i].hub_id=facility_details.hub_id;
           results_json[i].district_id=facility_details.district_id;
           results_json[i].district_name=districts_json[facility_details.district_id];

           results_json[i].samples_received=Number(that.samples_received)||0;
           results_json[i].dbs_samples=Number(that.dbs_samples)||0;
           results_json[i].total_results=Number(that.total_results)||0;
           results_json[i].valid_results=Number(that.valid_results)||0;
           results_json[i].rejected_samples=Number(that.rejected_samples)||0;
           results_json[i].suppressed=Number(that.suppressed)||0;

           results_json[i].sample_quality_rejections=Number(that.sample_quality_rejections)||0;
           results_json[i].eligibility_rejections=Number(that.eligibility_rejections)||0;
           results_json[i].incomplete_form_rejections=Number(that.incomplete_form_rejections)||0;

           results_json[i].cd4_less_than_500=Number(that.cd4_less_than_500)||0;
           results_json[i].pmtct_option_b_plus=Number(that.pmtct_option_b_plus)||0;
           results_json[i].children_under_15=Number(that.children_under_15)||0;
           results_json[i].other_treatment=Number(that.other_treatment)||0;
           results_json[i].treatment_blank_on_form=Number(that.treatment_blank_on_form)||0;
           results_json[i].tb_infection=Number(that.tb_infection)||0;
           
        }

        console.log("first facility:"+JSON.stringify(results_json[0]));

        console.log("number of data records:"+count(data));
       generalFilter(); //call the filter for the first time
    });

    $scope.dateFilter=function(mode){
        if($scope.fro_date!="all" && $scope.to_date!="all"){
            var vals={};var fro_s=$scope.fro_date.split("-");var to_s=$scope.to_date.split("-");
            vals.from_year=Number(fro_s[0]);
            vals.from_month=Number(fro_s[1]);
            vals.to_year=Number(to_s[0]);
            vals.to_month=Number(to_s[1]);

            var eval1=vals.from_year<=vals.to_year;
            var eval2=(vals.from_month>vals.to_month)&&(vals.from_year<vals.to_year);
            var eval3=(vals.from_month<=vals.to_month);

            if(eval1 && (eval2||eval3)){
                console.log("duration expression passed");
                computeDuration(vals);
               /* if(count($scope.filter_duration)<=12){
                    
                }else{
                    alert("Please choose a duration of 12 months or less");
                }*/
                $scope.date_filtered=true;
               /* $scope.fro_date="all";
                $scope.to_date="all";*/
                $scope.filter("duration");                
            }else{
                alert("Please make sure that the fro date is earlier than the to date");
                console.log("duration expression failing eval1="+eval1+" eval2"+eval2+" eval3"+eval3);
                console.log("fro yr="+vals.from_year+" fro m"+vals.from_month+" to yr="+vals.to_year+" to m"+vals.to_month);
            }
        }
    }

    var computeDuration=function(vals){
        $scope.filter_duration=[];
        var i=vals.from_year;
        while(i<=vals.to_year){
            var stat=(i==vals.from_year)?vals.from_month:1;
            var end=(i==vals.to_year)?vals.to_month:12;
            var j=stat;
            while(j<=end){
                $scope.filter_duration.push(i+"-"+j);
                j++;   
            }   
            i++;  
        }
    }

    $scope.filter=function(mode){
        switch(mode){
            case "district":
            $scope.filter_districts[$scope.district]=districts_json[$scope.district];
            $scope.district='all';
            
            break;

            case "hub":
            $scope.filter_hubs[$scope.hub]=hubs_json[$scope.hub];
            $scope.hub='all';
            break;

            case "age_group":
            $scope.filter_age_group[$scope.age_group]=age_group_json[$scope.age_group];
            $scope.age_group='all';
            break;
        }

        delete $scope.filter_districts["all"];
        delete $scope.filter_hubs["all"];
        delete $scope.filter_age_group["all"];

        generalFilter(); //filter the results for each required event
    }



    var evaluator=function(that){  
        var d_num=count($scope.filter_districts);
        var h_num=count($scope.filter_hubs);
        var a_num=count($scope.filter_age_group);

        var time_eval=inArray(that.year_month,$scope.filter_duration);
        var dist_eval=$scope.filter_districts.hasOwnProperty(that.district_id);
        var hub_eval=$scope.filter_hubs.hasOwnProperty(that.hub_id);
        var ag_eval=$scope.filter_age_group.hasOwnProperty(that.age_group);

        var eval1=d_num==0&&h_num==0&&a_num==0;     // districts(OFF) and hubs(OFF) and age_groups (OFF)
        var eval2=dist_eval&&h_num==0&&a_num==0;    // districts(ON) and hubs(OFF) and age_groups (OFF)
        var eval3=(dist_eval&&hub_eval)&&a_num==0;  // districts(ON) or hubs(ON) and age_groups (OFF)
        var eval4=dist_eval&&h_num==0&&ag_eval;     // districts(ON) and hubs(OFF) and age_groups (ON)
        var eval5=(dist_eval&&hub_eval)&&ag_eval;   // districts(ON) or hubs(ON) and age_groups (ON)
        var eval6=d_num==0&&hub_eval&&ag_eval;      // districts(OFF) and hubs(ON) and age_groups (ON)
        var eval7=d_num==0&&hub_eval&&a_num==0;     // districts(OFF) and hubs(ON) and age_groups (OFF)
        var eval8=d_num==0&&h_num==0&&ag_eval;      // districts(OFF) and hubs(OFF) and age_groups (ON)

        if( time_eval && (eval1||eval2||eval3||eval4||eval5||eval6||eval7||eval8)){
            return true;
        }else{
            return false;
        }
    }

    var setKeyIndicators=function(that){
        $scope.samples_received+=that.samples_received;
        $scope.suppressed+=that.suppressed;
        $scope.valid_results+=that.valid_results;
        $scope.rejected_samples+=that.rejected_samples;
    }

    var setOtherIndicators=function(that){
        $scope.cd4_less_than_500+=that.cd4_less_than_500;
        $scope.pmtct_option_b_plus+=that.pmtct_option_b_plus;
        $scope.children_under_15+=that.children_under_15;
        //$scope.other_treatment+=that.other_treatment;
        $scope.treatment_blank_on_form+=that.treatment_blank_on_form;  
        $scope.tb_infection+=that.tb_infection;
    }

    var setDataByDuration=function(that){
        var prev_plasma=$scope.samples_received_data.plasma[that.year_month]||0;
        var prev_dbs= $scope.samples_received_data.dbs[that.year_month]||0;
        $scope.samples_received_data.plasma[that.year_month]=prev_plasma+(that.samples_received-that.dbs_samples);
        $scope.samples_received_data.dbs[that.year_month]=prev_dbs+that.dbs_samples;
        
        var prev_sprsd= $scope.suppressed_by_duration[that.year_month]||0;
        $scope.suppressed_by_duration[that.year_month]=prev_sprsd+that.suppressed;
        
        var prev_vld= $scope.valid_res_by_duration[that.year_month]||0;
        $scope.valid_res_by_duration[that.year_month]=prev_vld+that.valid_results;

        rjrctionSetter(that);//for rejection graphs
    }

    var rjrctionSetter=function(that){
        var prev_sq=$scope.rejected_by_duration.sample_quality[that.year_month]||0;
        var prev_eli=$scope.rejected_by_duration.eligibility[that.year_month]||0;
        var prev_inc=$scope.rejected_by_duration.incomplete_form[that.year_month]||0;

        $scope.rejected_by_duration.sample_quality[that.year_month]=prev_sq+that.sample_quality_rejections;
        $scope.rejected_by_duration.eligibility[that.year_month]=prev_eli+that.eligibility_rejections;
        $scope.rejected_by_duration.incomplete_form[that.year_month]=prev_inc+that.incomplete_form_rejections;
    }

    var setDataByFacility=function(that){
        $scope.facility_numbers[that.facility_id]=$scope.facility_numbers[that.facility_id]||{};
        var f_smpls_rvd=$scope.facility_numbers[that.facility_id].samples_received||0;
        var f_vls_rsts=$scope.facility_numbers[that.facility_id].valid_results||0;
        var f_rjctd_smpls=$scope.facility_numbers[that.facility_id].rejected_samples||0;
        var f_sprrsd=$scope.facility_numbers[that.facility_id].suppressed||0;
        var f_dbs_smpls=$scope.facility_numbers[that.facility_id].dbs_samples||0;
        var f_ttl_results=$scope.facility_numbers[that.facility_id].total_results||0;

        $scope.facility_numbers[that.facility_id].samples_received=f_smpls_rvd+that.samples_received;
        $scope.facility_numbers[that.facility_id].valid_results=f_vls_rsts+that.valid_results;
        $scope.facility_numbers[that.facility_id].rejected_samples=f_rjctd_smpls+that.rejected_samples;
        $scope.facility_numbers[that.facility_id].suppressed=f_sprrsd+that.suppressed;
        $scope.facility_numbers[that.facility_id].dbs_samples=f_dbs_smpls+that.dbs_samples;
        $scope.facility_numbers[that.facility_id].total_results=f_ttl_results+that.total_results;
        $scope.facility_numbers[that.facility_id].name=that.facility_name;
    }

    var setDistrictData=function(that){
        $scope.district_numbers[that.district_id]=$scope.district_numbers[that.district_id]||{};

        var d_smpls_rvd=$scope.district_numbers[that.district_id].samples_received||0;
        var d_dbs_smpls=$scope.district_numbers[that.district_id].dbs_samples||0;
        var d_ttl_results=$scope.district_numbers[that.district_id].total_results||0;

        $scope.district_numbers[that.district_id].samples_received=d_smpls_rvd+that.samples_received;
        $scope.district_numbers[that.district_id].dbs_samples=d_dbs_smpls+that.dbs_samples;
        $scope.district_numbers[that.district_id].total_results=d_ttl_results+that.total_results;
        $scope.district_numbers[that.district_id].name=that.district_name;
    }

    var generalFilter=function(){
        $scope.loading=true;
        $scope.samples_received=0;$scope.suppressed=0;$scope.valid_results=0;$scope.rejected_samples=0;   
        $scope.cd4_less_than_500=0;$scope.pmtct_option_b_plus=0;$scope.children_under_15=0;$scope.tb_infection=0;
        $scope.other_treatment=0;$scope.treatment_blank_on_form=0;        
        $scope.samples_received_data={'plasma':{},'dbs':{}};
        $scope.suppressed_by_duration={};
        $scope.valid_res_by_duration={};
        $scope.rejected_by_duration={'sample_quality':{},'eligibility':{},'incomplete_form':{}};
        $scope.facility_numbers={};
        $scope.district_numbers={};

        for(var i in results_json){
            var that = results_json[i];
            if(evaluator(that)){
                setKeyIndicators(that); //set the values for the key indicators
                setOtherIndicators(that); //set the values for other indicators
                setDataByDuration(that); //set data by duration to be displayed in graphs    
                setDataByFacility(that); //set data by facility to be displayed in tables
                setDistrictData(that); //set data by district to displayed in the table
            }         
        }

        $scope.other_treatment=$scope.samples_received-($scope.cd4_less_than_500+$scope.pmtct_option_b_plus+$scope.children_under_15+$scope.treatment_blank_on_form+$scope.tb_infection);

        $scope.displaySamplesRecieved();
        $scope.displaySupressionRate();
        $scope.displayRejectionRate();

        $scope.filtered=count($scope.filter_districts)>0||count($scope.filter_hubs)>0||count($scope.filter_age_group)||$scope.date_filtered;
        $scope.loading=false;    
    };


    $scope.displaySamplesRecieved=function(){       //$scope.samples_received=100000;
        var srd=$scope.samples_received_data;        
        var data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];

        for(var i in srd.dbs){
            data[0].values.push({"x":dateFormat(i),"y":Math.round(srd.dbs[i])});
            data[1].values.push({"x":dateFormat(i),"y":Math.round(srd.plasma[i])});            
        }

        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().color(["#F44336","#607D8B"]);
            if(count(srd.dbs)<=8) { chart.reduceXTicks(false); }

            chart.yAxis.tickFormat(d3.format(',.0d'));
            $('#samples_received svg').html(" ");
            d3.select('#samples_received svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    };


    $scope.displaySupressionRate=function(){
        var data=[{"key":"SUPRESSION RATE","color": "#607D8B","values":[] },
                  {"key":"VALID RESULTS","bar":true,"color": "#F44336","values":[]}];

        for(var i in $scope.valid_res_by_duration){
            var sprsd=$scope.suppressed_by_duration[i]||0;
            var vld=$scope.valid_res_by_duration[i]||0;
            var s_rate=(sprsd/vld)*100;
            //s_rate.toPrecision(3);
            data[0].values.push([dateFormat(i),Math.round(s_rate)]);
            data[1].values.push([dateFormat(i),vld]);
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
            $('#supression_rate svg').html(" ");
            d3.select('#supression_rate svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });
    }

    $scope.displayRejectionRate=function(){
        var rbd=$scope.rejected_by_duration;
        var data=[{"key":"SAMPLE QUALITY","values":[]},
                  {"key":"INCOMPLETE FORM","values":[] },
                  {"key":"ELIGIBILITY","values":[] }];

        for(var i in rbd.sample_quality){
            var ttl=rbd.sample_quality[i]+rbd.incomplete_form[i]+rbd.eligibility[i];
            var sq_rate=(rbd.sample_quality[i]/ttl)*100;
            var inc_rate=(rbd.incomplete_form[i]/ttl)*100;
            var el_rate=(rbd.eligibility[i]/ttl)*100;
            data[0].values.push({"x":dateFormat(i),"y":Math.round(sq_rate) });
            data[1].values.push({"x":dateFormat(i),"y":Math.round(inc_rate)});
            data[2].values.push({"x":dateFormat(i),"y":Math.round(el_rate)});
        }
        nv.addGraph( function(){
            var chart = nv.models.multiBarChart().stacked(true).color(["#607D8B","#FFCDD2","#F44336"]);
            if(count(rbd.sample_quality)<=8) { chart.reduceXTicks(false); }
            chart.yAxis.tickFormat(d3.format(',.0d'));
            $('#rejection_rate svg').html(" ");
            d3.select('#rejection_rate svg').datum(data).transition().duration(500).call(chart);
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

    $scope.removeTag=function(mode,nr){
        switch(mode){
            case "district": delete $scope.filter_districts[nr];break;
            case "hub": delete $scope.filter_hubs[nr];break;
            case "age_group": delete $scope.filter_age_group[nr];break;
        }
        $scope.filter(mode);
    };

    $scope.clearAllFilters=function(){
        $scope.filter_districts={};
        $scope.filter_hubs={};
        $scope.filter_age_group={};
        $scope.filter_duration=$scope.init_duration;
        $scope.filtered=false;
        $scope.date_filtered=false;
        generalFilter();
    }

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

    $scope.nana=function(){
        if($scope.show_fclties==true){
            $("#d_shw").attr("class","active");
            $("#f_shw").attr("class","");
            $scope.show_fclties=false;
        }else{
            $("#f_shw").attr("class","active");
            $("#d_shw").attr("class","");
            $scope.show_fclties=true;
        }
    }

    var inArray=function(val,arr){
        var ret=false;
        for(var i in arr){
            if(val==arr[i]) ret=true;
        }
        return ret;
    }

    var dateFormat=function(y_m){
        var arr=y_m.split('-');
        var yr=arr[0];
        var mth=arr[1];
        return $scope.month_labels[mth]+" '"+yr.slice(-2);
    }

    var count=function(json_obj){
        return Object.keys(json_obj).length;
    }

};

app.controller(ctrllers);
