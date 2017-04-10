@extends('layout')

@section('content')
{!! Form::open(array('url'=>"lab_qc/qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}

<!-- <div id="my-tab-content" class="tab-content"> -->


    <ul class="breadcrumb">
        <li><a href="/">HOME</a></li>
        <li><a href="/lab_qc/index">RESULTS AUTH</a></li>
        <li class="active">{{ $wk->worksheetReferenceNumber }}</li>
    </ul>

    <div class="tab-pane active" id="print">
        {!! Form::hidden('worksheet_id', $wk->id) !!}
       
        Machine Type: <u>{{ $wk->machineType }}</u>

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
                <th>Suppressed?</th>
                <th style="width:250px">Choose</th>
            </tr>
            </thead>
            <tbody>
                
                <tr> <td>1</td> <td>NC</td> <td/> <td/> <td/> <td/> <td/> <td/> <td/></tr>
                <tr> <td>2</td> <td>PC</td> <td/> <td/> <td/> <td/> <td/> <td/> <td/></tr>
                <tr> <td>3</td> <td>HPC</td> <td/> <td/> <td/> <td/> <td/> <td/> <td/></tr>
                <?php
                $smpl_arr = array();
                $nr = 4;
                if($wk->includeCalibrators == 1){
                    while($nr<=11){
                        echo " <tr> <td>$nr</td> <td>Calibrator</td> <td/> <td/> <td/> <td/> <td/> <td/> <td/></tr>";
                        $nr++;
                    }
                } 

                ?>
                @foreach($samples AS $sample)
                <?php
                $smpl_arr[$sample->sampleID] = "xx"; 

                $styl = "";
                $resultxxx = "";
                $flag = "";
                $interpretation = "";
                $wid = "";
                if($wk->machineType == 'abbott'){
                    $resultxxx = $sample->abbott_result;
                    $flag = $sample->flags;
                    $test_date = $sample->abbott_date;
                    $interpretation = $sample->interpretation;
                    $wid = $sample->wid_a;
                }else{
                    $resultxxx = $sample->roche_result;
                    $test_date = $sample->roche_date;
                    $wid = $sample->wid_r;
                }
   

                $failed = MyHTML::isResultFailed($wk->machineType,$resultxxx,$flag, $interpretation);
                $suppressed = "UNKNOWN";
                if($failed == 1){
                    $styl = "background:#F5A9A9;";
                    $pat_result = 'Failed';
                }else{
                    $pat_result = MyHTML::getVLNumericResult($resultxxx, $wk->machineType, $sample->factor);
                    $num_result = MyHTML::getNumericalResult($pat_result);
                    $suppressed = MyHTML::isSuppressed2($num_result);
                    
                }
                ?>

                <tr  style="<?php echo $styl; if($wid!=$id) echo "font-weight: bold;"; ?>" > 
                    <td>{{ $nr }}</td>
                    <td>{{ $sample->vlSampleID }}</td>                    
                    <td>{{ $sample->lrCategory }}{{ $sample->lrEnvelopeNumber }}/{{ $sample->lrNumericID }}</td>
                    <td>{{ $sample->formNumber }}</td>
                    <td>{{ $sample->artNumber }}</td>
                    <td>{{ $resultxxx }}</td>
                    <td>{{ $pat_result  }} {!! Form::hidden("pat_results[$sample->sampleID]",$pat_result) !!}</td> 
                    <td>{{ $suppressed }}</td>
                    <td>
                        {!! Form::hidden("suppressions[$sample->sampleID]",$suppressed) !!}
                       @if($wid == $id)
                       <?php if($pat_result!='Failed'){ ?><label>{!! Form::radio("choices[$sample->sampleID]", 'release') !!} Release</label><?php } ?>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'reschedule') !!} Reschedule</label>
                       <label>{!! Form::radio("choices[$sample->sampleID]", 'invalid') !!} Invalid</label>
                       @else
                        
                        <?php 
                        try {
                             $other_wk = \EID\WorksheetResults::getWorksheet(1);
                             echo "As tested on worksheet ($other_wk->worksheetReferenceNumber)";
                        } catch (Exception $e) {
                             echo "problem here, should be investigated";
                        }

                        ?>
                       @endif
                    </td>                   
                </tr> 
                <?php $nr++; ?>
                @endforeach

                

            </tbody>
        </table>
        {!! Form::hidden('test_date', $test_date) !!}
        {!! Form::hidden('len', count($smpl_arr), ['id'=>'len']) !!}
        
        
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

<style type="text/css">
.h{

}
</style>
@endsection()