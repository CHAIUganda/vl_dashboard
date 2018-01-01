@extends('layout')

@section('content')
<?php 
$params = "";
$limit = "?";
if(\Request::has('h')) $limit .= "h=". \Request::get('h');

$pending_actv="";
$completed_actv="";
$search_actv="";

if(\Request::has('search')){
    $search_actv="class=active";
}elseif($tab=='completed'){
    $completed_actv="class=active"; 
}else{
    $pending_actv="class=active";
}
$facility_str = str_replace(" ", "_", $facility_name);
$facility_str = str_replace("/", "", $facility_str);
$facility_str = str_replace("'", "", $facility_str);
?>
<ul class="breadcrumb">
    <li><a href="/">HOME</a></li>
    <li><a href="/api/facility_list">FACILITIES</a></li>
    <li action="active">{{ $facility_name }}</li>
</ul>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{$pending_actv}} ><a href="/api/results/{{ $facility_id }}/?tab=pending">Print</a></li>
    <li {{$completed_actv}}><a href="/api/results/{{ $facility_id }}/?tab=completed" >Printed/Downloaded</a></li>
    <li {{ $search_actv }} title='Search'><a href="/api/results/{{ $facility_id }}/?search=1" >Search</a></li>
</ul>


<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        @if(\Request::has('search'))
        Search using ART Number or Form Number:
          {!! Form::text('search','', ['id'=>'id-search','class' => 'form-control input-sm input_md', 'autocomplete'=>'off', 'placeholder'=>"Search..."] ) !!}
          <div class='live_drpdwn' id="id-dropdown" style='display:none'></div>
        @else
        {!! Form::open(array('url'=>'/api/result/','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}
        <input type="hidden" name="facility" value="{{ $facility_str }}">
        <input type="hidden" name="tab" value="{{ $tab }}">
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
       @endif
    </div>
</div>  

<script type="text/javascript">

$(function() {
    $('#results').addClass('active');
    @if(!\Request::has('search'))
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: '/api/results/data/{{ $facility_id }}/?tab={{ $tab }}',
    });
    @endif

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

var drpdwn= $(".live_drpdwn");

function get_data(q,drpdwn,link){
    if(q && q.length>=3){   
        //console.log("this is what you have just typed:"+ q+"link"+link);      
        $.get(link+q+"?f="+{{ $facility_id }}, function(data){
            drpdwn.show();
            drpdwn.html(data);
        });
    }else{
        drpdwn.hide();
        drpdwn.html("");
    }
}

$("#id-search").keyup(function(){
    var q = $(this).val();
    var dd = $("#id-dropdown");
    get_data(q, dd, "/api/search_result/");
});


</script>
@endsection()