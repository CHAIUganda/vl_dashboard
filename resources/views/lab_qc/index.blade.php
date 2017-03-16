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

$roche_url = "/lab_qc/index?tab=roche";
$abbott_url = "/lab_qc/index?tab=abbott";
$released_url = "/lab_qc/index?tab=released";
?>
<ul class="breadcrumb">
    <li><a href="/">HOME</a></li>
    <li class="active">RESULTS AUTH</li>
    
</ul>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li {{ $roche_actv }} title='Roche'><a href="{!! $roche_url !!}" >Roche QC</a></li>
    <li {{ $abbott_actv }} title='Abbott'><a href="{!! $abbott_url !!}" >Abbott QC</a></li>
    <li {{ $released_actv }} title='Released'><a href="{!! $released_url !!}" >Released Worksheets</a></li>
</ul>
{!! Form::open(array('url'=>'/result','id'=>'view_form', 'name'=>'view_form', 'target' => 'Map' )) !!}

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <table id="results-table" class="table table-condensed table-bordered  table-striped" style="max-width:1100px;margin-top:10px">
            <thead>
                <tr>
                    <th>Worksheet Number</th> 
                    <th>Number Failed</th>
                    <th>Date time</th> 
                    <th>Created by</th>             
                </tr>
            </thead>
        </table>
    </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">

$('#lab_qc').addClass('active');

$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: '{!! url("/lab_qc/index/data?tab=$tab") !!}',
        columns: [
            {data: 'worksheetReferenceNumber', name: 'w.worksheetReferenceNumber'},
            {data: 'num_failed', name: 'num_failed', orderable: false, searchable: false},
            {data: 'created', name: 'w.created'},
            {data: 'createdby', name: 'u.names'}, 

        ]
    });
});

</script>
@endsection()