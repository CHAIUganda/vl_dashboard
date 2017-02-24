@extends('layout')

@section('content')
{!! Form::open(array('url'=>"lab_qc/qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}

<!-- <div id="my-tab-content" class="tab-content"> -->
    <div class="tab-pane active" id="print">  
        Worksheet Ref Number: <u>{{ $wk->worksheetReferenceNumber }}</u>
        {!! Form::hidden('worksheet_id', $wk->id) !!}
        &nbsp;&nbsp;&nbsp;
        Machine Type: <u>{{ $wk->machineType }}</u><br><br>

        <table id="results-table" class="table table-condensed table-bordered table-striped" style="font-size:12px">
            <thead>
            <tr>
                <td>#</td>
                <th>Sample ID</th>               
                <th>Location&nbsp;ID&nbsp;&nbsp;&nbsp;</th>
                <th>Form Number</th>
                <th>Art Number</th>
                <th>Machine Result</th>
                <th>Result </th>
                <th style="width:250px">Choose</th>
            </tr>
            </thead>
            <tbody>
                
                <tr> <td>1</td> <td>NC</td> <td/> <td/> <td/> <td/> <td/> <td/> </tr>
                <tr> <td>2</td> <td>PC</td> <td/> <td/> <td/> <td/> <td/> <td/> </tr>
                <tr> <td>3</td> <td>HPC</td> <td/> <td/> <td/> <td/> <td/> <td/> </tr>
                <?php
                $nr = 4;
                if($wk->includeCalibrators == 1){
                    while($nr<=11){
                        echo " <tr> <td>$nr</td> <td>Calibrator</td> <td/> <td/> <td/> <td/> <td/> <td/> </tr>";
                        $nr++;
                    }
                } 

                ?>
                @foreach($samples AS $sample)
                <?php 

                $styl = "";
                $resultxxx = "";
                $flag = "";
                if($wk->machineType == 'abbott'){
                    $resultxxx = $sample->abbott_result;
                    $flag = $sample->flags;
                }else{
                    $resultxxx = $sample->roche_result;
                }
   

                $failed = MyHTML::isResultFailed($wk->machineType,$resultxxx,$flag);
                $suppressed = "UNKNOWN";
                if($failed == 1){
                    $styl = "style=background:#F5A9A9;";
                    $pat_result = 'Failed';
                }else{
                    $num_result = MyHTML::getNumericalResult($resultxxx);
                    $suppressed = MyHTML:: isSuppressed2($num_result);
                    $pat_result = MyHTML::getVLNumericResult($resultxxx, $wk->machineType, $sample->factor);
                }
                ?>

                <tr {{ $styl }}>
                    <td>{{ $nr }} </td>
                    <td>{{ $sample->vlSampleID }}</td>                    
                    <td>{{ $sample->lrCategory }}{{ $sample->lrEnvelopeNumber }}/{{ $sample->lrNumericID }}</td>
                    <td>{{ $sample->formNumber }}</td>
                    <td>{{ $sample->artNumber }}</td>
                    <td>{{ $resultxxx }}</td>
                    <td>{{ $pat_result  }} {!! Form::hidden("pat_results[$sample->sampleID]",$pat_result) !!}</td> 
                    <td>
                        {!! Form::hidden("suppressions[$sample->sampleID]",$suppressed) !!}
                        
                       <?php if($pat_result!='Failed'){ ?><label>{!! Form::radio("choices[$sample->sampleID]", 'release') !!} Release</label><?php } ?>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'reschedule') !!} Reschedule</label>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'invalid') !!} Invalid</label>
                    </td>                   
                </tr> 
                <?php $nr++; ?>
                @endforeach
                

            </tbody>
        </table>
        {!! Form::hidden('len', count($samples), ['id'=>'len']) !!}
        <br>
        <div style="float:right">
            <input type="submit" id="save" class='btn btn-sm btn-danger' value="Save Lab QC" />
        </div>
        <br><br>
    </div>
<!-- </div> -->
  

{!! Form::close() !!}
<script type="text/javascript">
$('#lab_qc').addClass('active');

$(function() {
    $('#results-table').DataTable({paging:false});
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
    
})


</script>
@endsection()