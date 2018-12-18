<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Sierra Leone Viral Load Dashboard')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/jquery.dataTables.css') }}" rel="stylesheet">    
    <link href="{{ asset('/css/jquery-ui.css')}}" rel="stylesheet" >

     <link href="{{ asset('/css/nv.d3.min.css') }}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="{{ asset('/css/demo.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabs.css') }} " />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabstyles.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/angular-datatables.css') }}" />
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"> -->
   

  
   
    <link href="{{ asset('/css/dash.css') }}" rel="stylesheet"/>
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />

    
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/jquery-ui.min.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>

    <script src="{{ asset('/twitter-bootstrap-3.3/js/bootstrap.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/angular.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular-route.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/angular-datatables.min.js') }}" type="text/javascript"></script>

   <script src="{{ asset('/js/modernizr.custom.js') }}"></script>

    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>
   
    <script src="{{ asset('/js/d3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('/js/nv.d3.min.js') }}"></script>
    <script type="text/javascript">
        function activeRetestNSTab () {
            //remove class
            $('#li-suppression-trend').removeClass('active');
            $('#li-action-pane').removeClass('active');
            $('#li-retest-s').removeClass('active');
            $('#li-rejections').removeClass('active');
            $('#li-v-patients').removeClass('active');
            $('#li-a-patients').removeClass('active');
            
            //Add class
            $('#li-retest-ns').addClass('active');

        }
        
        function activeRetestSTab () {
            //remove class
            $('#li-suppression-trend').removeClass('active');
            $('#li-action-pane').removeClass('active');
            $('#li-retest-ns').removeClass('active');
            $('#li-rejections').removeClass('active');
            $('#li-v-patients').removeClass('active');
            $('#li-a-patients').removeClass('active');
            
            //Add class
            $('#li-retest-s').addClass('active');

        }
        function activeRejectionsTab () {
            //remove class
            $('#li-suppression-trend').removeClass('active');
            $('#li-action-pane').removeClass('active');
            $('#li-retest-ns').removeClass('active');
            $('#li-retest-s').removeClass('active');
            
            $('#li-v-patients').removeClass('active');
            $('#li-a-patients').removeClass('active');
            
            //Add class
            $('#li-rejections').addClass('active');

        }
    </script>
    
    <!--script src="{{ asset('/js/jquery-1.11.1.min.js') }}" type="text/javascript"></script -->
    <script src="{{ asset('/js/jquery.tabletoCSV.js')}}" type="text/javascript"></script>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular-sanitize.min.js"></script>
<script src="{{ asset('/js/ng-csv.min.js') }}"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css">

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.3.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>

<link href="{{ asset('/css/hub.css') }}" rel="stylesheet"/>
<style type="text/css">
    
    /* Popup box BEGIN */
.hover_bkgr_fricc{
    background:rgba(0,0,0,.4);
    cursor:pointer;
    display:none;
    height:100%;
    position:fixed;
    text-align:center;
    top:0;
    width:100%;
    z-index:10000;
}
.hover_bkgr_fricc .helper{
    display:inline-block;
    height:100%;
    vertical-align:middle;
}
.hover_bkgr_fricc > div {
    background-color: #fff;
    box-shadow: 10px 10px 60px #555;
    display: inline-block;
    height: auto;
    max-width: 551px;
    min-height: 100px;
    vertical-align: middle;
    width: 60%;
    position: relative;
    border-radius: 8px;
    padding: 15px 5%;
}
.popupCloseButton {
    background-color: #fff;
    border: 3px solid #999;
    border-radius: 50px;
    cursor: pointer;
    display: inline-block;
    font-family: arial;
    font-weight: bold;
    position: absolute;
    top: -20px;
    right: -20px;
    font-size: 25px;
    line-height: 30px;
    width: 30px;
    height: 30px;
    text-align: center;
}
.popupCloseButton:hover {
    background-color: #ccc;
}
.trigger_popup_fricc {
    cursor: pointer;
    font-size: 20px;
    margin: 20px;
    display: inline-block;
    font-weight: bold;
}
/* Popup box BEGIN */
</style>

<script type="text/javascript">

 $(document).ready(function(){
    $(window).load(function () {
        $('.hover_bkgr_fricc').show();

        $(".trigger_popup_fricc").click(function(){
           $('.hover_bkgr_fricc').show();
        });
        $('.hover_bkgr_fricc').click(function(){
            $('.hover_bkgr_fricc').hide();
        });
        $('.popupCloseButton').click(function(){
            $('.hover_bkgr_fricc').hide();
        });
    });
    
});
</script>


</head>



<body ng-app="dashboard" ng-controller="DashController">

    <div class="navbar-custom navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header"> 
                <!-- <img src="{{ asset('/images/icon.png') }}" height="20" width="20"> -->
                <a class="navbar-brand" href="/" style="font-weight:800px;color:#FFF"> SIERRA LEONE VIRAL LOAD</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                        <li id='dashboard' >{!! link_to("/","DASHBOARD",['class'=>'hdr']) !!}</li>
<!-- 
                        @if(Auth::check())   
                        @permission('qc')<li id='qc' >{!! link_to("/qc","RESULTS RELEASE",['class'=>'hdr']) !!}</li>@endpermission
                        @permission('lab_qc')<li id='lab_qc' >{!! link_to("/lab_qc/index","RESULTS AUTH",['class'=>'hdr']) !!}</li>@endpermission    -->    
                        @permission('print_results')
                        <li id='results' >{!! link_to("/direct/facility_list/","RESULTS",['class'=>'hdr']) !!}</li>
                        <li id='suppression_trends' >{!! link_to("/suppression_trends/index","REPORTS",['class'=>'hdr']) !!}</li>
                        @endpermission

                        

                        @permission('monitoring')<li id='monitor'>{!! link_to("/monitor", "MONITOR") !!}</li> @endpermission

                        @role('admin')<li id='admin' >{!! link_to("/logs","ADMIN",['class'=>'hdr']) !!}</li> @endrole
                        @else
                        <li id='login'>{!! link_to("auth/login","LOGIN",['class'=>'hdr']) !!}</li> 
                        @endif 
                        

                        
                </ul>
               
                 <!-- <ul class="nav navbar-nav navbar-right">
                     <li id='notifications' class="dropdown">
                            <a href="#" cclass="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="glyphicon glyphicon-bell"></span> 
                                <sup>4 Updates</sup>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                               <li><a href="#">Facilities Access
                                <span>Facilities can view their reports just like hubs</span></a>
                               </li>
                               <li><a href="#">Template for loading
                                <span>Template for loading from non-CPHL site has been made</span>
                               </a></li> 
                               <li><a href="#">eMTCT Filter Renamed
                                <span>items have names changed</span>
                               </a></li>
                               <li><a href="#">TB Status Filter Renamed
                                <span>items in the filter have been renamed following the VL Meeting as of 4-Aug-17</span>
                               </a></li>
                           </ul>
                     </li>
                </ul> -->
                @if(Auth::check())
                 <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> {{ Auth::user()->name }} <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                           <li><a href="/change_password">Change Password</a></li>
                           <li>{!! link_to("auth/logout","logout",['class'=>'hdr']) !!}</li> 
                        </ul>
                    </li>
                </ul>

                @endif
                
            </div>
        </div>
    </div> 
    <div class='row'>
        <div class="col-xs-1"> @yield('content1') </div>
        <div class='col-s-10 container'>
            @yield('content')
        </div>
        <div class="col-xs-1">  </div>

    </div>
</body>
</html>