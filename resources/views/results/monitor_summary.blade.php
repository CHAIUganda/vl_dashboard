@extends('layout')

@section('content')
<?php
$date_from = \Request::has('date_from')?\Request::get('date_from'):date("Y-m-d");
$date_to = \Request::has('date_to')?\Request::get('date_to'):date("Y-m-d");

?>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li ><a href="/monitor" >Monitor</a></li>
    <li ><a href="/monitor_download" >Download</a></li>
    <li class="active"><a href="/monitor_summary" >Summary</a></li>
</ul>

<div id="my-tab-content" class="tab-content">
    <div class="tab-pane active" id="print"> 
        <div class="form-inline" style="margin:10px">
            {!! Form::text('date_from', $date_from,['placeholder'=>'From', 'id'=>'date_from', 'class'=>'form-control input-sm input_sm']) !!} 
            {!! Form::text('date_to', $date_to,['placeholder'=>'To', 'id'=>'date_to', 'class'=>'form-control input-sm input_sm']) !!}
        </div>
        <table id="results-table" class="table table-condensed table-bordered  table-striped">
            <tr>
                <td>Number Pending</td>
                <td>{{ $res->num_pending }}</td>
            </tr>
            <tr>
                <td>Number Printed</td>
                <td>{{ $res->num_printed }}</td>
            </tr>
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