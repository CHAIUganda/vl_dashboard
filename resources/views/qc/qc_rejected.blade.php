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
    <div id="sh"></div>
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
                <th style="width:120px;">   </th>
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
                        <span id="action{{ $sample->sampleID }}">
                        @if(empty($sample->fpid))

                           <label>{!! Form::radio("choices$sample->sampleID", 'approved', 0, ["sample"=>"$sample->sampleID", "class"=>"approvals"]) !!} Release</label>
                           <label>{!! Form::radio("choices$sample->sampleID", 'reject', 0,["sample"=>"$sample->sampleID", "class"=>"rejects"]) !!} Retain</label><br>
                           <label>{!! Form::hidden("xx", "", ["id"=>"ready".$sample->sampleID]) !!}</label>
                           {!! Form::textarea("comments[$sample->sampleID]", "", ["style"=>"display:none", "id"=>"comment$sample->sampleID", "rows"=>"4", "cols"=>"30"]) !!}
                           <span style="display:none" class="btn btn-primary btn-xs qc_save" id="save{{ $sample->sampleID }}" sample="{{ $sample->sampleID }}">save</span>
                        @else
                            @if($sample->ready=='YES') 
                                <span >Released </span>
                            @else
                                <span style="color:red">Retained</span>
                            @endif
                        @endif
                        </span>
                    </td> 
                </tr>
                @endforeach

            </tbody>
        </table>

        {!! Form::hidden('len', count($samples), ['id'=>'len']) !!}
     

        </div>
</div>
  

{!! Form::close() !!}
<script type="text/javascript">
$('#qc').addClass('active');

$(function() {
    $('#results-table').DataTable({paging:false, sorting:false});
});


/*$("#save").click(function(){
    var len_selected = $('input:radio:checked').length;
    var len_samples = $('#len').val();
    if(len_selected!=len_samples){
        alert("Please Complete QC for all samples");
        return false;
    }else{
        return true;
    }   
});*/

$(".rejects").click(function(){
    var sample = $(this).attr('sample');
    $("#comment"+sample).show();
    $("#ready"+sample).attr('value','NO');
    $("#save"+sample).show();

});

$(".approvals").click(function(){
    var sample = $(this).attr('sample');
    $("#comment"+sample).hide();
    $("#ready"+sample).attr('value','YES');
    $("#save"+sample).show();
    
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

$(".qc_save").click(function(){
    var sample = $(this).attr('sample');
    var ready = $("#ready"+sample).val();
    var comments = "";
    var status = "";
    if(ready=='NO'){
        status = "retaining";
        comments = $("#comment"+sample).val();
    }else{
        status = "releasing";
    } 
    var token = $("[name=_token]").val();

    $.post("/qc_rejected_save/"+sample+"/",  {ready:ready, comments: comments, _token: token}).done(function( data ) {
      if(data==1){
         $("#action"+sample).html("<span>"+status+" successful</span>");
      }else{
         $("#action"+sample).html("<span style='color:red'>"+data+" failed</span>");
      }
    });
});

</script>
@endsection()