@extends('layout')

@section('content')
<?php 
$params = "";
$limit = "?";
if(\Request::has('h')) $limit .= "h=". \Request::get('h');

$pending_actv="";
$completed_actv="";
if(isset($tab)){
    if($tab=='pending'){
        $pending_actv="class=active";
    }else{
        $completed_actv="class=active";    
    }
    $limit .= "&tab=$tab";
} 

$pending_url = "/results?tab=pending";
$completed_url = "/results?tab=completed";

$tab_limit = isset($tab)?"?tab=$tab":"";
?>
<ul class="breadcrumb">
    <li><a href="/">HOME</a></li>
    <li action="active">RESULTS</li>
</ul>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li class="active"><a href="/#" >Print</a></li>
    <li><a href="/#" >Print/Downloaded</a></li>
</ul>


<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <table id="results-table" class="table table-condensed table-bordered  table-striped">
        <thead>
            <tr>
                <th>Select</th>
                <th>Form Number</th>                     
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Date Collected</th>
                <th>Date Received</th>
                <th>Date Released</th>
                <th>Options</th>
            </tr>
        </thead>
        </table> 
       
    </div>
</div>  

<script type="text/javascript">
$('#results').addClass('active');
$(function() {
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '/api/results/data/{{ $facility_id }}'
    });

});
</script>
@endsection()