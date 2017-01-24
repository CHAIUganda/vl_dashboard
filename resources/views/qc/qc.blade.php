@extends('layout')

@section('content')
{!! Form::open(array('url'=>"/qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}

<!-- <div id="my-tab-content" class="tab-content"> -->
    <div class="tab-pane active" id="print">  
        Worksheet Ref Number: <u>{{ $wk->worksheetReferenceNumber }}</u><br><br>
        <a href="#" class='btn btn-sm btn-danger' id="select_all">Select all</a>
        <input type="submit" id="download" name="download" class='btn btn-sm btn-danger' value="Approve selected"   /> 
        <table id="results-table" class="table table-condensed table-bordered" style="font-size:12px">
            <thead>
            <tr>
                <th></th>
                <th>Hub</th>
                <th>Facility</th>
                <th>Location&nbsp;ID&nbsp;&nbsp;&nbsp;</th>
            	<th width="1%">Form Number</th>
                <th>Art Number</th>
                <th>Other ID</th>
                <th >DOB</th>
                <th>Gender</th>
                <th width="1%">Date of collection</th>
                <th width="1%">Date received at CHPL</th>
                <th>Result</th>
                <th>Approved</th>
            </tr>
            </thead>
            <tbody>
                @foreach($samples AS $sample)
                <tr>
                    <td><?= (empty($sample->fp_id))? Form::checkbox('samples[]', $sample->sampleID,'', ['class'=>'samples']):"" ?></td>               
                    <td>{{ $sample->hub }}</td>
                    <td>{{ $sample->facility }}</td>
                    <td>{{ $sample->lrCategory }}{{ $sample->lrEnvelopeNumber }}/{{ $sample->lrNumericID }}</td>
                    <td><a href="javascript:windPop('/result/{{ $sample->id }}?view=yes')">{{ $sample->formNumber }}</a></td>
                    <td>{{ $sample->artNumber }}</td>
                    <td>{{ $sample->otherID }}</td>
                    <td>{{ $sample->dateOfBirth }}</td>
                    <td>{{ $sample->gender }}</td>
                    <td>{{ $sample->collectionDate }}</td>
                    <td>{{ $sample->receiptDate }}</td>
                    <td>{{ $sample->result }}</td>
                    <td class='<?= (!empty($sample->fp_id))?"alert alert-success":"alert alert-info" ?>'>
                        <?= (!empty($sample->fp_id))?"Approved":"Pending" ?>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
<!-- </div> -->
  

{!! Form::close() !!}
<script type="text/javascript">
$('#qc').addClass('active');

$(function() {
    $('#results-table').DataTable({paging:false});
});

$('#select_all').click(function(){
    var status = $(this).html();
    if(status == 'Select all'){
        $(".samples").attr("checked", true);
        $(this).html('Unselect all');
    }else{
        $(".samples").attr("checked", false);
        $(this).html('Select all');
    }    
})
</script>
@endsection()