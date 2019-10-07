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
    <li action="active">FACILITIES</li>
</ul>
<h2 style="text-align:center;text-transform:uppercase">{{ Auth::user()->hub_name }}</h2>

<div class="alert alert-danger alert-dismissible show" role="alert">
  <strong>Please Note!</strong> The numbers showing in the printed column are for 2019 only. The older data has been archived to improve the speed of loading the page.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

@if(empty(Auth::user()->facility_id) AND empty(Auth::user()->hub_id) AND $sect == 'results')
   <div class="row">
   
     <div class="col-md-6">
        {!! Form::text('search','', ['id'=>'id-search','class' => 'form-control input-sm input_md', 'autocomplete'=>'off', 'placeholder'=>"Search result..."] ) !!}
        <div class='live_drpdwn' id="id-dropdown" style='display:none;width:480px'></div>
     </div>
      <span id="id-search-button" class="btn btn-danger btn-sm">search</span>
   </div>     
   
@endif() 

@if(empty(Auth::user()->facility_id))
<br>
<div style="text-align:center;"> 
    <span style="background-color:#F5A9A9;border: 1px solid;"> &nbsp; &nbsp;  &nbsp;</span> 
    Facility has an account and may be printing
</div>
@endif()
<table id="results-table" class="table table-condensed table-bordered  table-striped">
<thead>
    <tr>
       <!--  @if(empty(Auth::user()->hub_id))<th>Hub</th> @endif  -->
        <th>Facility</th>  
        <th>Hub</th>                   
        <th>Contact Person</th>
        <th>Phone</th>
        <th>Email</th>

        <th># Pending</th>
        <th># Printed/ Downloaded</th>
        <th>Last print date</th>
        <th>Action</th>
       <!--  <th># Downloaded</th>
        @if($sect == 'admin')<th># Last Printed/ Downloaded</th>@endif 
        @if($sect == 'results')<th>Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>@endif
     -->
    </tr>
</thead>
</table>  

<style type="text/css">
#id-search{
    width: 480px;
}
</style> 

<script type="text/javascript">
$(function() {
    $('#results').addClass('active');
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '/direct/facility_data/',
        drawCallback: function( settings ) {
           $(".has-account").parent().parent().attr("style","background-color:#F5A9A9");
        },
        
    }); 

});

drpdwn= $(".live_drpdwn");

function get_data(q,drpdwn,link){
    if(q && q.length>=3){   
        //console.log("this is what you have just typed:"+ q+"link"+link);  
        $.get(link+"?txt="+q, function(data){
            drpdwn.show();
            drpdwn.html(data);
        });
    }else{
        drpdwn.hide();
        drpdwn.html("");
    }
}

$("#hub").keyup(function(){
    var q = $(this).val();
    var dd = $("#worksheet_dropdown");
    get_data(q, dd, "/searchbyhub/");
});

$("#id-search").keyup(function(){
    var q = $(this).val();
    var dd = $("#id-dropdown");
    get_data(q, dd, "/direct/search_result/");
});

$("#id-search-button").click(function(){
    var q = $("#id-search").val();
    var dd = $("#id-dropdown");
    get_data(q, dd, "/direct/search_result/");
})

</script>

<style type="text/css">
.has_account{
    color: red;
    background-color:#F5A9A9;
}
</style>
@endsection()