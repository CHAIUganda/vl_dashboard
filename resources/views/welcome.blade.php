<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('meta-title', 'EID LIMS')</title>

    <!-- Bootstap -->

    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/jquery.dataTables.css') }}" rel="stylesheet">    
    <link href="{{ asset('/css/jquery-ui.css')}}" rel="stylesheet" >

    <link href="{{ asset('/twitter-bootstrap-3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/eid.css') }}" rel="stylesheet">
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />

    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/jquery-ui.js')}}" type="text/javascript"></script>

   
    <script src="{{ asset('/twitter-bootstrap-3.3/js/bootstrap.min.js') }}" type="text/javascript" ></script>
   


</head>

<body ng-app="dashboard" ng-controller="DashController">
<div class="navbar-custom navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/"> <span class='glyphicon glyphicon-home'></span> VIRAL LOAD</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                    <li id='l1' class='active'>{!! link_to("/","DASHBOARD",['class'=>'hdr']) !!}</li>            
            </ul>
        </div>
    </div>
</div> 

<div class='container'>
    <br>
    <?php //if(!isset($filter_val)) $filter_val="National Metrics, ".$time." thus far" ?>
      
     <?php 

     function yearByMonths($from_year=1900,$from_month=1,$to_year="",$to_month=""){
        if(empty($to_year)) $to_year=date("Y");
        if(empty($to_month)) $to_month=date("m");
        $ret=[];
        $i=$from_year;
        while($i<=$to_year){
            $stat=($i==$from_year)?$from_month:1;
            $end=($i==$to_year)?$to_month:12;
            $j=$stat;
            while($j<=$end){
                $ret[$i][]=$j;
                $j++;   
            } 
            $i++; 
        }
        return $ret;
    }

     //$start_year=2011,$start_month=1;
     $current_year=date('Y');$current_month=date('m');
     $init_duration=[];
     $m=1;
     while($m<=$current_month){
        $init_duration[]="$current_year-$m";
        $m++;
     }
     $drp_down=yearByMonths(2000,1);    
     ?>

     <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">drop down <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li> errered </li>
                        <li> drfrcx </li>
                    </ul>
                </div>

     <br>
     <label class='hdr hdr-grey'> KEY METRICS</label>
     <br>
     <div class="tabss tabs-style-flip">
        <nav>
            
        </nav>
      
    </div><!-- /tabs -->
    
    <br>
   
    <br>
</div>




</body>



</html>
