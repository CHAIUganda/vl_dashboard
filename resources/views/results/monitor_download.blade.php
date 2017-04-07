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
        columns: [        
            {data: 'district', name: 'district'},
            {data: 'hub', name: 'hub'},
            {data: 'facility', name: 'facility'},
            {data: 'artNumber', name: 'artNumber'},
            {data: 'otherID', name: 'otherID'},
            {data: 'formNumber', name: 'formNumber'},
            {data: 'printed_at', name: 'printed_at'},
        ]
    });

});

</script>
@endsection()