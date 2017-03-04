@extends('layout')

@section('content')
<link href="{{ asset('/css/vl2.css') }}" rel="stylesheet">
<?php 
$roche_actv="";
$abbott_actv="";
$released_actv="";

if($tab=='roche'){
    $roche_actv="class=active";
}elseif($tab=='abbott'){
    $abbott_actv="class=active";
}else{
    $released_actv="class=active";
}

$roche_url = "/qc?tab=roche";
$abbott_url = "/qc?tab=abbott";
$released_url = "/qc?tab=passed_data_qc";
?>


<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{ $roche_actv }} title='Roche'><a href="{!! $roche_url !!}" >Roche QC</a></li>
    <li {{ $abbott_actv }} title='Abbott'><a href="{!! $abbott_url !!}" >Abbott QC</a></li>
    <li title='Rejected'><a href="/qc_rejected/{{ date('Y-m-d') }}" >QC Rejected Samples</a></li>
    <li {{ $released_actv }} title='Completed'><a href="{!! $released_url !!}" >Completed</a></li>
</ul>
{!! Form::open(array('url'=>'/result','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <table id="results-table" class="table table-condensed table-bordered  table-striped" style="max-width:1100px;margin-top:10px">
            <thead>
                <tr>
                    <th>Worksheet Number</th> 
                    <th>Date time</th> 
                    <th>Created by</th>             
                </tr>
            </thead>
        </table>
    </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">

$('#qc').addClass('active');

$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: '{!! url("/qc/data?tab=".$tab) !!}',
        columns: [
            {data: 'worksheetReferenceNumber', name: 'w.worksheetReferenceNumber'},
            {data: 'created', name: 'w.created'},
            {data: 'createdby', name: 'u.names'}, 
        ]
    });
});

</script>
@endsection()