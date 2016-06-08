
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
            var month_labels={'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sept','10':'Oct','11':'Nov','12':'Dec'};
            return month_labels[y_m.slice(-2)]+" '"+y_m.slice(2,4);
        }
    });




var ctrllers={};

ctrllers.DashController=function($scope,$http){
    $scope.identity = angular.identity;
    $scope.params = {'districts':[],'hubs':[],'age_ids':[]};

    var districts_json={};
    var hubs_json={};
    var age_group_json={1:"0<5",2:"5-9",3:"10-18",4:"19-25",5:"26+"};  
    var regimen_groups_json={1:'AZT' ,2:'TDF/XTC/EFV' ,3:'TDF/XTC/NVP', 4:'ABC',5:'TDF/XTC/LPV/r' , 6:'TDF/XTC/ATV/r', 7:'Other'};
    var regimen_times_json={1:'6-12 months',2:'1-2 years',3:'2-3 years',4:'3-5 years',5:'5+ years'};   
    var facilities_json={};   
    var results_json={}; //to hold a big map will all processed data to later on be used in the generalFilter
    var genders_json={'m':'Male','f':'Female','x':'Unknown'};

    $scope.month_labels={'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sept','10':'Oct','11':'Nov','12':'Dec'};

    $scope.labels={};
    $scope.labels.reg_grps=regimen_groups_json;
    $scope.labels.reg_times=regimen_times_json;
    $scope.labels.age_grps=age_group_json;
    $scope.labels.genders=genders_json;

    var vvvrrr=0;

    $scope.districts2=[];
    $scope.hubs2=[];
    $scope.age_group_slct=age_group_json;

    $http.get("/other_data/").success(function(data){
        for(var i in data.districts){
            var obj=data.districts[i];
            districts_json[obj.id]=obj.name;
            $scope.districts2.push({"id":obj.id,"name":obj.name});
        }

        for(var i in data.hubs){
            var obj=data.hubs[i];
            hubs_json[obj.id]=obj.name;
            $scope.hubs2.push({"id":obj.id,"name":obj.name});
        }

        for(var i in data.facilities){
            var f=data.facilities[i];
            facilities_json[f.id]={'name':f.name,'district_id':f.district_id,'hub_id':f.hub_id};
        }
    });

    $scope.labels.facilities=facilities_json;
    $scope.labels.districts=districts_json;

    var getData=function(){
            var prms={};
            prms.districts=JSON.stringify($scope.params.districts);
            prms.hubs=JSON.stringify($scope.params.hubs);
            prms.age_ids=JSON.stringify($scope.params.age_ids);
            $http({method:'GET',url:"/live/",params:prms}).success(function(data) {
                $scope.loading=true;
                //console.log("we rrrr"+JSON.stringify($scope.params));

                $scope.samples_received=data.whole_numbers.samples_received||0;
                $scope.suppressed=data.whole_numbers.suppressed||0;
                $scope.valid_results=data.whole_numbers.valid_results||0;
                $scope.rejected_samples=data.whole_numbers.rejected_samples||0;   

                $scope.cd4_less_than_500=data.t_indication[3]||0;
                $scope.pmtct_option_b_plus=data.t_indication[1]||0;
                $scope.children_under_15=data.t_indication[2]||0;
                $scope.tb_infection=data.t_indication[4]||0;
                $scope.other_treatment=data.t_indication[5]||0;
                $scope.treatment_blank_on_form=data.t_indication[0]|| 0; 

                $scope.duration_numbers=data.drn_numbers||{};
                $scope.facility_numbers=data.f_numbers||{};
                $scope.district_numbers=data.dist_numbers||{};
                $scope.regimen_group_numbers=data.reg_groups||{};
                $scope.regimen_time_numbers=data.reg_times||{};

                $scope.displaySamplesRecieved(); //to display the samples graph - for the first time
    
                $scope.loading=false;
                //console.log("lalallalal:: samples_received:: "+data.samples_received+" suppressed:: "+data.suppressed+" "+data.valid_results);
            });
    };

    getData();    


    $scope.dateFilter=function(){
        if($scope.fro_date!="all" && $scope.to_date!="all"){
            var fro_nr=Number($scope.fro_date);//numberise the fro date
            var to_nr=Number($scope.to_date);//numberise the to date

            if(fro_nr>to_nr){
                alert("Please make sure that the fro date is earlier than the to date");
            }else{
                $scope.date_filtered=true;
                $scope.params.fro_date=fro_nr;
                $scope.params.to_date=to_nr;
                $scope.filter("duration");
            }
        }
    }

    $scope.filter=function(mode){
        switch(mode){
            case "district":
            $scope.filter_districts[$scope.district]=districts_json[$scope.district];
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
        }

        delete $scope.filter_districts["all"];
        delete $scope.filter_hubs["all"];
        delete $scope.filter_age_group["all"];

        getData();

        //generalFilter(); //filter the results for each required event
    }


    $scope.displaySamplesRecieved=function(){       //$scope.samples_received=100000;       
        var data=[{"key":"DBS","values":[] },{"key":"PLASMA","values":[] }];

        for(var i in $scope.duration_numbers){
            var obj=$scope.duration_numbers[i];
            data[0].values.push({"x":dateFormat(obj._id),"y":Math.round(obj.dbs_samples||0)});
            data[1].values.push({"x":dateFormat(obj._id),"y":Math.round(obj.plasma_samples||0)});            
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

    $scope.displayRejectionRate=function(){
        var data=[{"key":"SAMPLE QUALITY","values":[]},
                  {"key":"INCOMPLETE FORM","values":[] },
                  {"key":"ELIGIBILITY","values":[] }];

        for(var i in $scope.duration_numbers){
            var obj=$scope.duration_numbers[i];
            var ttl=obj.sample_quality+obj.incomplete_form+obj.eligibility;
            var sq_rate=((obj.sample_quality/ttl)||0)*100;
            var inc_rate=((obj.incomplete_form/ttl)||0)*100;
            var el_rate=((obj.eligibility/ttl)||0)*100;
            data[0].values.push({"x":dateFormat(obj._id),"y":Math.round(sq_rate) });
            data[1].values.push({"x":dateFormat(obj._id),"y":Math.round(inc_rate)});
            data[2].values.push({"x":dateFormat(obj._id),"y":Math.round(el_rate)});
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

        for(var i in $scope.regimen_group_numbers){
            var obj=$scope.regimen_group_numbers[i];
            var sprsd=obj.suppressed||0;
            var vld=obj.valid_results||0;
            var s_rate=((sprsd/vld)||0)*100;
            //s_rate.toPrecision(3);
            var label=regimen_groups_json[obj._id];
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
            //chart.reduceXTicks(false);
            //chart.bars.forceY([0]);
            chart.lines.forceY([0,100]);
            chart.legendRightAxisHint(" (R)").legendLeftAxisHint(" (L)");

            $('#regimen_groups svg').html(" ");
            d3.select('#regimen_groups svg').datum(data).transition().duration(500).call(chart);
            return chart;
        });

        
    };


    $scope.displayRegimenTime=function(){
        var data=[{"key":"SUPRESSION RATE","color": "#607D8B","values":[] },
                  {"key":"SAMPLES RECEIVED","bar":true,"color": "#F44336","values":[]}];

        for(var i in $scope.regimen_time_numbers){
            var obj=$scope.regimen_time_numbers[i];
            var sprsd=obj.suppressed||0;
            var vld=obj.valid_results||0;
            var s_rate=((sprsd/vld)||0)*100;
            //s_rate.toPrecision(3);
            var label=regimen_times_json[obj._id];
            data[0].values.push([label,Math.round(s_rate)]);
            data[1].values.push([label,obj.samples_received]);
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
        $scope.fro_date="all";
        $scope.to_date="all";
        getData({});
        //generalFilter();
    }

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
    }

    var inArray=function(val,arr){
        var ret=false;
        for(var i in arr){
            if(val==arr[i]) ret=true;
        }
        return ret;
    }

    /*var dateFormat=function(y_m){
        var arr=y_m.split('-');
        var yr=arr[0];
        var mth=arr[1];
        return $scope.month_labels[mth]+" '"+yr.slice(-2);
    }*/

    var dateFormat=function(x){
        var ym=isNaN(x)?x:x.toString();
        return $scope.month_labels[ym.slice(-2)]+" '"+ym.slice(2,4);
    }

    var count=function(json_obj){
        return Object.keys(json_obj).length;
    }

};

app.controller(ctrllers);
