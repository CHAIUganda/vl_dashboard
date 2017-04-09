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
?>
<ul class="breadcrumb">
    <li><a href="/">HOME</a></li>
    <li action="active">RESULTS</li>
</ul>

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <table id="results-table" class="table table-condensed table-bordered  table-striped">
        <thead>
            <tr>                
                <th>District</th>                     
                <th>Hub</th>
                <th>Facility</th>
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Form Number</th>
                <th>Sample ID</th>
                <th>Result</th>
                <th>Test Date</th>
                <th>Auth At</th>
                <th>Released At</th>
                <th>Printed At</th>              
            </tr>
        </thead>
        </table> 
    </div>
</div>  

<script type="text/javascript">
 $('#monitor').addClass('active'); 
$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '{!! url("/monitor_download/data") !!}',
        dom: 'Bfrtip',
        buttons: ['csv', 'excel'],
        columns: [        
            {data: 'district', name: 'd.district'},
            {data: 'hub', name: 'h.hub'},
            {data: 'facility', name: 'h.facility'},
            {data: 'artNumber', name: 'p.artNumber'},
            {data: 'otherID', name: 'p.otherID'},
            {data: 'formNumber', name: 's.formNumber'},
            {data: 'vlSampleID', name: 's.vlSampleID'},
            {data: 'result', name: 'rr.result'},
            {data: 'test_date', name: 'rr.test_date'},
            {data: 'lab_qc_at', name: 'rr.lab_qc_at'},
            {data: 'qc_at', name: 'fp.qc_at'},
            {data: 'printed_at', name: 'fp.printed_at'},
        ]
    });

});

</script>
@endsection()