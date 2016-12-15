@extends('layout')

@section('content')
{!! Form::open(array('url'=>"/qc/$id",'id'=>'view_form', 'name'=>'view_form' )) !!}

<!-- <div id="my-tab-content" class="tab-content"> -->
    <div class="tab-pane active" id="print">  
        Worksheet Ref Number: <u>{{ $wk->worksheetReferenceNumber }}</u><br><br>
        <a href="#" class='btn btn-sm btn-danger' id="select_all">SELECT ALL</a>
        <input type="submit" id="download" name="download" class='btn btn-sm btn-danger' value="APPROVE SELECTED"   /> 
        <table id="results-table" class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th></th>               
                <th>Hub</th>
                <th>Facility</th>
                <th>Location&nbsp;ID&nbsp;&nbsp;&nbsp;</th>
            	<th>Form Number</th>
                <th>Art Number</th>
                <th>Other ID</th>
                <th>Date of collection</th>
                <th>Date received at CHPL</th>
                <th>Approved</th>
            </tr>
            </thead>
            <tbody>
                @foreach($samples AS $sample)
                <tr>
                    <td><?= (empty($sample->fp_id))? Form::checkbox('samples[]', $sample->sampleID,'', ['class'=>'samples']):"" ?></td>               
                    <td>{{$sample->hub}}</td>
                    <td>{{$sample->facility}}</td>
                    <td>{{$sample->lrCategory}}{{$sample->lrEnvelopeNumber}}/{{$sample->lrNumericID}}</td>
                    <td>{{$sample->formNumber}}</td>
                    <td>{{$sample->artNumber}}</td>
                    <td>{{$sample->otherID}}</td>
                    <td>{{$sample->collectionDate}}</td>
                    <td>{{$sample->receiptDate}}</td>
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
    if(status == 'SELECT ALL'){
        $(".samples").attr("checked", true);
        $(this).html('UNSELECT ALL');
    }else{
        $(".samples").attr("checked", false);
        $(this).html('SELECT ALL');
    }
    
})
</script>
@endsection()