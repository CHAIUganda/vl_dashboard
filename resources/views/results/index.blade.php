@extends('layout')

@section('content')
<link href="{{ asset('/css/vl2.css') }}" rel="stylesheet">
<?php 
$facility_id = \Request::has('f')?'&f='.\Request::get('f'):"";
if($printed=='YES'){
    $printed_actv="class=active";
    $print_actv="";
}else{
    $print_actv="class=active";
    $printed_actv="";
}

$print_url="/results_list?printed=NO$facility_id";
$printed_url="/results_list?printed=YES$facility_id";
?>

<div style="text-align:center;text-decoration: underline;" class='print-ttl'>
    {{ $facility_name }}
    <a href="javascript:windPop('/print_envelope/{{ \Request::get('f') }}')" style="float:right" class="btn btn-danger btn-xs">Print Envelope</a>
</div>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{ $print_actv }} title='Print'><a href="{!! $print_url !!}" >Pending</a></li>
    <li {{ $printed_actv }} title='Already printed'><a href="{!! $printed_url !!}" >Printed/Downloaded</a></li>
</ul>
{!! Form::open(array('url'=>'/result','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}

{!! Form::hidden('printed', $printed) !!}

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <a href="#" class='btn btn-xs btn-danger' id="select_all" >Select all visible</a>
        {!! MyHTML::submit('Download selected','btn  btn-xs btn-danger','pdf') !!}
        <input type="button" class='btn btn-xs btn-danger' value="Print preview selected" onclick="viewSelected();"   /> 

        <table id="results-table" class="table table-condensed table-bordered  table-striped" style="max-width:1100px;margin-top:10px">
            <thead>
            <tr>
                <th>Select</th>               
            	<th>Form Number</th>
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Date of collection</th>
                <th>Date received</th>
                <th>Released on</th>
                @if($printed=='YES')
                 <th>Print date/time</th>
                 <th>Printed by</th>
                @endif

                <th>Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        @if($printed=='YES') pageLength: 50, @endif
        @if($printed=='NO') paging:false, @endif

        ajax: '{!! url("/results_list/data?printed=$printed$facility_id") !!}',
        columns: [
            {data: 'sample_checkbox', name: 'sample_checkbox', orderable: false, searchable: false},
            {data: 'formNumber', name: 's.formNumber'},
            {data: 'artNumber', name: 'p.artNumber'},
            {data: 'otherID', name: 'p.otherID'},
            {data: 'collectionDate', name: 's.collectionDate'},
            {data: 'receiptDate', name: 's.receiptDate'},
            {data: 'qc_at', name: 'pr.qc_at'},     
            @if($printed=='YES')
                {data: 'printed_at', name: 'pr.printed_at'},
                {data: 'printed_by', name: 'pr.printed_by'},                 
            @endif       
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
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
})

function viewSelected() {     
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