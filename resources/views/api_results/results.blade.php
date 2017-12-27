@extends('layout')

@section('content')
<?php 
$params = "";
$limit = "?";
if(\Request::has('h')) $limit .= "h=". \Request::get('h');

$pending_actv="";
$completed_actv="";

if($tab=='completed'){
    $completed_actv="class=active"; 
}else{
    $pending_actv="class=active";
}
?>
<ul class="breadcrumb">
    <li><a href="/">HOME</a></li>
    <li><a href="/api/facility_list">FACILITIES</a></li>
    <li action="active">{{ $facility_name }}</li>
</ul>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{$pending_actv}} ><a href="/api/results/{{ $facility_id }}/?tab=pending">Print</a></li>
    <li {{$completed_actv}}><a href="/api/results/{{ $facility_id }}/?tab=completed" >Printed/Downloaded</a></li>
</ul>


<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        {!! Form::open(array('url'=>'/api/result/','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}
        <a href="#" class='btn btn-xs btn-danger' id="select_all" >Select all visible</a>
        {!! MyHTML::submit('Download selected','btn  btn-xs btn-danger','pdf') !!}
        <input type="button" class='btn btn-xs btn-danger' value="Print selected" onclick="printSelected();"   /> 

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
       {!! Form::close() !!}
    </div>
</div>  

<script type="text/javascript">

$(function() {
    $('#results').addClass('active');
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '/api/results/data/{{ $facility_id }}/?tab={{ $tab }}',
    });

    $('#select_all').click(function(){
        var status = $(this).html();
        if(status == 'Select all visible'){
            $(".samples").attr("checked", true);
            $(this).html('Unselect all visible');
        }else{
            $(".samples").attr("checked", false);
            $(this).html('Select all visible');
        }    
    });

});

function printSelected() {     
   var mapForm = document.getElementById("view_form");
   map = window.open("","Map","width=1100,height=1000,menubar=no,resizable=yes,scrollbars=yes");
   //map=window.open("","Map","status=0,title=0,height=600,width=800,scrollbars=1");

   if (map) {
      mapForm.submit();
   } else {
      alert('You must allow popups for this map to work.');
   }
}


</script>
@endsection()