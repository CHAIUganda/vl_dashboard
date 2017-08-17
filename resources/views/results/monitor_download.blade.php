@extends('layout')

@section('content')
<?php
$date_from = \Request::has('date_from')?\Request::get('date_from'):date("Y-m-01");
$date_to = \Request::has('date_to')?\Request::get('date_to'):date("Y-m-d");

?>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>

<script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.24/build/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.24/build/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li ><a href="/monitor" >Monitor</a></li>
    <li class="active"><a href="/monitor_download" >Download</a></li>
    <li ><a href="/monitor_summary" >Summary</a></li>
</ul>

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <div class="form-inline" style="margin:10px">
            {!! Form::select('date',['receiptDate'=>'Date Received'],'', ['class'=>'form-control input-sm input_sm']) !!}
            {!! Form::text('date_from', $date_from,['placeholder'=>'From', 'id'=>'date_from', 'class'=>'form-control input-sm input_sm']) !!} 
            {!! Form::text('date_to', $date_to,['placeholder'=>'To', 'id'=>'date_to', 'class'=>'form-control input-sm input_sm']) !!}
        </div>
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
                <th>Date Received</th>
                <th>Test Date</th>
                <th>Auth At</th>
                <th>Released At</th>
                <th>Printed At</th>   
                <th>Result</th>           
            </tr>
        </thead>
        </table> 
    </div>
</div>  

<script type="text/javascript">
 $('#monitor').addClass('active'); 
$(function() {
    $("#date_from").datepicker({
         changeMonth: true,
         changeYear: true,
         minDate: new Date("2017-03-01"),
         maxDate: new Date(),
         dateFormat: "yy-mm-dd"
    });

    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '{!! url("/monitor_download/data?date_from=$date_from&date_to=$date_to") !!}',
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
            {data: 'receiptDate', name: 's.receiptDate'},            
            {data: 'test_date', name: 'rr.test_date'},
            {data: 'lab_qc_at', name: 'rr.lab_qc_at'},
            {data: 'qc_at', name: 'fp.qc_at'},
            {data: 'printed_at', name: 'fp.printed_at'},
            {data: 'result', name: 'rr.result'},
        ]
    });

});

$("#date_from").on("change",function(){
    $("#date_to").datepicker({
         changeMonth: true,
         changeYear: true,
         minDate: new Date($("#date_from").val()),
         maxDate: new Date(),
         dateFormat: "yy-mm-dd"
    });
});

$("#date_to").on("change",function(){
    window.location.assign("/monitor_download?date_from="+$("#date_from").val()+"&date_to="+$(this).val());
});

</script>
@endsection()