<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/jquery.dataTables.css') }}" rel="stylesheet">    
    <link href="{{ asset('/css/jquery-ui.css')}}" rel="stylesheet" >

     <link href="{{ asset('/css/nv.d3.min.css') }}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="{{ asset('/css/demo.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabs.css') }} " />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/tabstyles.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/angular-datatables.css') }}" />
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"> -->
    <link href="{{ asset('/css/dash.css') }}" rel="stylesheet">
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />

    
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/jquery-ui.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/twitter-bootstrap-3.3/js/bootstrap.min.js') }}" type="text/javascript" ></script>

    <script src="{{ asset('/js/angular.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('/js/angular-route.js')}}" type="text/javascript"></script>

    <script src="{{ asset('/js/angular-datatables.min.js') }}" type="text/javascript"></script>

   <script src="{{ asset('/js/modernizr.custom.js') }}"></script>

    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>
   
    <script src="{{ asset('/js/d3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('/js/nv.d3.min.js') }}"></script>
    
    <!--script src="{{ asset('/js/jquery-1.11.1.min.js') }}" type="text/javascript"></script -->
    <script src="{{ asset('/js/jquery.tabletoCSV.js')}}" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular-sanitize.min.js"></script>
<script src="{{ asset('/js/ng-csv.min.js') }}"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.3.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<link href="{{ asset('/css/hub.css') }}" rel="stylesheet">
</head>



<body ng-app="dashboard" ng-controller="DashController">

    <div class="navbar-custom navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header"> 
                <!-- <img src="{{ asset('/images/icon.png') }}" height="20" width="20"> -->
                <a class="navbar-brand" href="/" style="font-weight:800px;color:#FFF"> UGANDA VIRAL LOAD</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                        <li id='dashboard' >{!! link_to("/","DASHBOARD",['class'=>'hdr']) !!}</li>

                        @if(Auth::check())   
                        @permission('qc')<li id='qc' >{!! link_to("/qc","DATA QC",['class'=>'hdr']) !!}</li>@endpermission
                        @permission('lab_qc')<li id='lab_qc' >{!! link_to("/lab_qc/index","LAB QC",['class'=>'hdr']) !!}</li>@endpermission       
                        @permission('print_results')
                        <li id='results' >{!! link_to("/results","RESULTS",['class'=>'hdr']) !!}</li>
                        <li id='suppression_trends' >{!! link_to("/suppression_trends/index","SUPPRESSION TRENDS",['class'=>'hdr']) !!}</li>
                        @endpermission
                        @role('admin')<li id='admin' >{!! link_to("/monitor","ADMIN",['class'=>'hdr']) !!}</li> @endrole
                        @else
                        <li id='login'>{!! link_to("auth/login","LOGIN",['class'=>'hdr']) !!}</li> 
                        @endif 
                </ul>

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

    <div class='container'>
        @yield('content')
    </div>
</body>
</html>