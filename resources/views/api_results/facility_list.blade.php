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
@if(empty(Auth::user()->facility_id) AND empty(Auth::user()->hub_id) AND $sect == 'results')
   <div class="row">
   <!--  <div class="col-md-6">
        {!! Form::text('hub','', ['id'=>'hub','class' => 'form-control input-sm input_md', 'autocomplete'=>'off', 'placeholder'=>"Search Hub"] ) !!}
        <div class='live_drpdwn' id="worksheet_dropdown" style='display:none'></div>
        <br> 
    </div> -->
     <div class="col-md-6">
        {!! Form::text('search','', ['id'=>'id-search','class' => 'form-control input-sm input_md', 'autocomplete'=>'off', 'placeholder'=>"Search result..."] ) !!}
        <div class='live_drpdwn' id="id-dropdown" style='display:none'></div>
     </div>
   </div>     
   
@endif() 

@if(empty(Auth::user()->facility_id))
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
        <th>Action</th>
       <!--  <th># Downloaded</th>
        @if($sect == 'admin')<th># Last Printed/ Downloaded</th>@endif 
        @if($sect == 'results')<th>Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>@endif
     -->
    </tr>
</thead>
<tbody>
    @foreach($facilities AS $facility)
    <?php 
    $f_id = $facility['_id']['pk'];
    $h_id = $facility['_id']['hub']['pk'];
     $pilot = "";
    if(empty(\Auth::user()->facility_id) AND empty(\Auth::user()->hub_id)){
        $pilot_facilities = 0;
        if(!empty($result->hubID)) $pilot_facilities = \EID\User::where('facility_id',  $f_id )->orWhere('hub_id',  $h_id)->count();
        $pilot = $pilot_facilities>=1? "background-color:#AED6F1":"";
    }

    if(empty(\Auth::user()->facility_id) && empty($pilot)){
        $pilot_facility_account = \EID\User::where('facility_id',  $f_id)->count();
        $pilot = $pilot_facility_account>=1? "background-color:#F5A9A9;":"";
    }
    $url = "/api/results/$f_id/?";

    $db = \EID\Mongo::connect();
    $f_details = $db->api_facilities->findOne(['pk'=>$f_id]); 

    ?>
    <tr style="{{$pilot}}">
        <td><a href="{{ $url }}">{{ $facility['_id']['facility'] }}</a></td>
        <td>{{ $facility['_id']['hub']['hub'] }}</td>
        <td>{{ $f_details['coordinator_name'] }}</td>
        <td>{{ $f_details['coordinator_contact'] }}</td>
        <td>{{ $f_details['coordinator_email'] }}</td>
        <td><a href="{{ $url }}">{{ $facility['num_pending'] }}</a></td>
        <td>{{ $facility['num_dispatched'] }}</td>
        <td>
            <a title='view pending' class='btn btn-danger btn-xs' href="{{ $url }}">view pending</a>
            <a title='view printed/downloaded' class='btn btn-danger btn-xs' href='{{ $url }}&tab=completed'><span class='glyphicon glyphicon-ok'></span></a>
            <a title='print envelope' class='btn btn-danger btn-xs' href='javascript:windPop("/print_envelope/{{ $facility['_id']['pk'] }}")'><span class='glyphicon glyphicon-envelope'></span></a>
        </td>
    </tr>
    @endforeach()
</tbody>
</table>  

<script type="text/javascript">
$(function() {
    $('#results').addClass('active');
    $('#results-table').DataTable({
        processing: true,
        pageLength: 10
    });

});

drpdwn= $(".live_drpdwn");

function get_data(q,drpdwn,link){
    if(q && q.length>=3){   
        //console.log("this is what you have just typed:"+ q+"link"+link);      
        $.get(link+q+"/{{ $tab_limit }}", function(data){
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
    get_data(q, dd, "/api/search_result/");
});

</script>
@endsection()