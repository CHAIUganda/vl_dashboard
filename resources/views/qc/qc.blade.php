@extends('layout')

@section('content')
{!! Form::open(array('url'=>"/qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}

<!-- <div id="my-tab-content" class="tab-content"> -->
    <div class="tab-pane active" id="print">  
        Worksheet Ref Number: <u>{{ $wk->worksheetReferenceNumber }}</u>
        &nbsp;&nbsp;&nbsp;
        Machine Type: <u>{{ $wk->machineType }}</u><br><br>

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
                <th>Result</th>
                <th style="width:120px;">Choose</th>
            </tr>
            </thead>
            <tbody>
                @foreach($samples AS $sample)
                <?php $resultxxx = !empty($wk->machineType == 'abbott' )?$sample->abbott_result:$sample->roche_result ?>

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
                    <td>{{ $sample->result }}</td>
                    <td>
                       <label>{!! Form::radio("choices[$sample->sample_id]", 'approved', 0, ["sample"=>"$sample->sample_id", "class"=>"approvals"]) !!} Approve</label>
                       <label>{!! Form::radio("choices[$sample->sample_id]", 'reject', 0,["sample"=>"$sample->sample_id", "class"=>"rejects"]) !!} Reject</label><br>
                       <textarea style="display:none" id="comment{{ $sample->sample_id }}"></textarea>
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
<!-- </div> -->
  

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
</script>
@endsection()