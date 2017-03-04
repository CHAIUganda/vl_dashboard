@extends('layout')

@section('content')

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
{!! Form::open(array('url'=>"/data_qc/x",'id'=>'view_form', 'name'=>'view_form' )) !!}
<?php
$roche_url = "/qc?tab=roche";
$abbott_url = "/qc?tab=abbott";
$released_url = "/qc?tab=passed_data_qc";
?>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li title='Roche'><a href="{!! $roche_url !!}" >Roche QC</a></li>
    <li title='Abbott'><a href="{!! $abbott_url !!}" >Abbott QC</a></li>
    <li class="active" title='Rejected'><a href="/qc_rejected/{{ date('Y-m-d') }}" >QC Rejected Samples</a></li>
    <li title='Completed'><a href="{!! $released_url !!}" >Completed</a></li>
</ul>
<div id="my-tab-content" class="tab-content">
    
    Date Rejected: <input type="text" id="date_rejected" value="{{ $date_rejected }}" readonly='true'>
    <a class="btn btn-danger btn-xs" href="#" id="go">Go</a>
    <div class="tab-pane active" id="print" >       

        <table id="results-table" class="table table-condensed table-bordered  table-striped" style="font-size:12px">
            <thead>
            <tr>
                <th>Location&nbsp;ID&nbsp;&nbsp;&nbsp;</th>
                <th width="1%">Form Number</th>
                <th>Facility</th>
                <th>District</th>                
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Gender</th>
                <th width="1%">Date of collection</th>
                <th width="1%">Date received at CHPL</th>
                <th style="width:120px;">Choose</th>
            </tr>
            </thead>
            <tbody>
                @foreach($samples AS $sample)                
                 <tr>
                    <td>{{ $sample->lrCategory }}{{ $sample->lrEnvelopeNumber }}/{{ $sample->lrNumericID }}</td>
                    <td><a href="javascript:windPop('/result/{{ $sample->sampleID }}?view=yes')">{{ $sample->formNumber }}</a></td>
                    <td>{{ $sample->facility }}</td>    
                    <td>{{ $sample->district }}</td>   
                    
                    <td>{{ $sample->artNumber }}</td>
                    <td>{{ $sample->otherID }}</td>
                    <td>{{ $sample->gender }}</td>
                    <td>{{ $sample->collectionDate }}</td>
                    <td>{{ $sample->receiptDate }}</td>
                    <td>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'approved', 0, ["sample"=>"$sample->sampleID", "class"=>"approvals"]) !!} Approve</label>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'reject', 0,["sample"=>"$sample->sampleID", "class"=>"rejects"]) !!} Reject</label><br>
                       {!! Form::textarea("comments[$sample->sampleID]", "", ["style"=>"display:none", "id"=>"comment$sample->sampleID", "rows"=>"4", "cols"=>"30"]) !!}
                    </td> 
                </tr>
                @endforeach

            </tbody>
        </table>

        {!! Form::hidden('len', count($samples), ['id'=>'len']) !!}
        <br>
        <div style="float:right">
            <input type="submit" id="save" class='btn btn-sm btn-danger' value="Save Data QC" />
        </div>
        <br><br>

        </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">
$('#qc').addClass('active');

$(function() {
    $('#results-table').DataTable({paging:false, sorting:false});
});


$("#save").click(function(){
    var len_selected = $('input:radio:checked').length;
    var len_samples = $('#len').val();
    if(len_selected!=len_samples){
        alert("Please Complete QC for all samples");
        return false;
    }else{
        return true;
    }   
});

$(".rejects").click(function(){
    var sample = $(this).attr('sample');
    $("#comment"+sample).show();
});

$(".approvals").click(function(){
    var sample = $(this).attr('sample');
    $("#comment"+sample).hide();
});
$( function() {
    $( "#date_rejected" ).datepicker({
         changeMonth: true,
         changeYear: true,
         maxDate: new Date(),
         dateFormat: "yy-mm-dd"
    });
} );

$("#go").click(function(){
    var date = $("#date_rejected").val();
    var url = "/qc_rejected/"+date;
    if(date!=''){ return window.location.assign(url);}else{ alert("input date rejected"); }
})
</script>
@endsection()