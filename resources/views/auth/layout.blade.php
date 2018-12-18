@extends('layout')

@section('content')  

<!-- <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li id='logs-tab' ><a href="/logs" >Logs</a></li>
    <li id='users-tab' ><a href="/admin/list_users" >User Management</a></li>
</ul> -->

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active"> 
    	 @yield('admin_content')
    </div>
</div>

<script type="text/javascript">
$('#admin').addClass('active');
</script>
@endsection()