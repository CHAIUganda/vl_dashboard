@extends('layout')

@section('content')
{!! Form::open(array('url'=>"/data_qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}
    <ul class="breadcrumb">
        <li><a href="/">HOME</a></li>
        <li><a href="/qc/index">RESULTS RELEASE</a></li>
        <li class="active">{{ $wk->worksheetReferenceNumber }}</li>
    </ul>

<!-- <div id="my-tab-content" class="tab-content"> -->
    <div class="tab-pane active" id="print">  
        
        Machine Type: <u>{{ $wk->machineType }}</u>
        {!! Form::hidden('worksheet_id', $wk->id ) !!}
        <?php $num_samples=0 ?>

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
                <tr>
                    <td>{{ $sample->lrCategory }}{{ $sample->lrEnvelopeNumber }}/{{ $sample->lrNumericID }}</td>
                    <td><a href="javascript:windPop('/result/{{ $sample->sample_id }}?view=yes')">{{ $sample->formNumber }}</a></td>
                    <td>{{ $sample->facility }}</td>    
                    <td>{{ $sample->district }}</td>   
                    
                    <td>{{ $sample->artNumber }}</td>
                    <td>{{ $sample->otherID }}</td>
                    <td>{{ $sample->gender }}</td>
                    <td>{{ $sample->collectionDate }}</td>
                    <td>{{ $sample->receiptDate }}</td>
                    <td>{{ $sample->result }}</td>
                    <td>
                         @if(empty($sample->fp_id))
                            <?php $num_samples++ ?>
                            <label>{!! Form::radio("choices[$sample->sample_id]", 'approved', 0, ["sample"=>"$sample->sample_id", "class"=>"approvals"]) !!} Release</label>
                            <label>{!! Form::radio("choices[$sample->sample_id]", 'reject', 0,["sample"=>"$sample->sample_id", "class"=>"rejects"]) !!} Retain</label><br>
                            {!! Form::textarea("comments[$sample->sample_id]", "", ["style"=>"display:none", "id"=>"comment$sample->sample_id", "rows"=>"4", "cols"=>"30"]) !!}
                         @else
                            @if($sample->ready=='YES') 
                                <span >Released </span>
                            @else
                                <span style="color:red">Retained</span>
                            @endif
                            <?php ?>
                        @endif
                    </td> 
                </tr>
                @endforeach

            </tbody>
        </table>

        {!! Form::hidden('len', $num_samples, ['id'=>'len']) !!}
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