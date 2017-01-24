@extends('auth.layout')

@section('admin_content')
<link   href="{{ asset('/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('/js/select2.min.js') }}" type="text/javascript"></script>

{!! Session::get('msge') !!}
{!! Form::open(['enctype'=>'multipart/form-data','url'=>"/admin/user_edit/$id",'id'=>'form_id','onsubmit'=>'return chkForm(this)']) !!}
Edit User
<table class='table table-bordered'>
    <tr>
        <td class='td_label' width='20%'><label for='a'>Name:</label></td>
        <td>{!! Form::text('name',$user->name,['class'=>'form-control','id'=>'a','required'=>1]) !!} </td>
    </tr>
  
    <tr>
        <td class='td_label'><label for='c'>User name:</label></td>
        <td>{!! Form::text('username',$user->username,['class'=>'form-control','id'=>'c','required'=>1]) !!} </td>
    </tr>
    
    <tr>
        <td class='td_label'><label for='g'>Email:</label></td>
        <td>{!! Form::email('email',$user->email,['class'=>'form-control','id'=>'g','required'=>1]) !!} </td>
    </tr>
   
    <tr>
        <td class='td_label'><label for='i'>Telephone:</label></td>
        <td>{!! Form::text('telephone',$user->telephone,['class'=>'form-control','id'=>'i','required'=>1]) !!} </td>
    </tr>
    <tr>
        <td class='td_label'><label for='k'>Limit by:</label></td>
        <td >
            <?php
            $chkt1=empty($user->facilityID)?"unchecked":"checked";
            $chkt2=empty($user->hubID)?"unchecked":"checked";

            $dsply1=empty($user->facilityID)?"none":"block";
            $dsply2=empty($user->hubID)?"none":"block";
            ?>
            {!! Form::radio('limit_by','1','',['onchange'=>'showLimit(1)',"$chkt1"=>"1"]) !!} Facility
            {!! Form::radio('limit_by','2','',['onchange'=>'showLimit(2)',"$chkt2"=>"1"]) !!} Hub
            {!! Form::radio('limit_by','3','',['onchange'=>'showLimit(3)']) !!} None
            <br>
            <div class='limitby' style="display:{!! $dsply1 !!}" id='limit1'>{!! Form::select('facility',[""=>""]+$facilities,$user->facility_id,['id'=>'fclty']) !!}</div>
            <div class='limitby' style="display:{!! $dsply2 !!}" id='limit2'>{!! Form::select('hub',[""=>""]+$hubs,$user->hub_id,['id'=>'hb']) !!}</div>
        </td>
    </tr>
    <tr><td/><td>{!! MyHTML::submit('Update User') !!} </td></tr>
</table>
{!! Form::hidden('hub_name', $user->hub_name, ['id'=>'hub_name']) !!}
{!! Form::hidden('facility_name', $user->facility_name, ['id'=>'facility_name']) !!}
{!! Form::hidden('edit',1) !!}

{!! Form::close() !!}
<script type="text/javascript">
$('#users-tab').addClass('active');
function showLimit(val){
    $(".limitby").attr('style','display:none');
    document.getElementById('limit'+val).style.display="block";
 }

 $(document).ready(function() {
    $("#user_r").select2({  placeholder:"Select user role", allowClear:true, width: '40%' });
    $("#fclty").select2({   placeholder:"Select facility", allowClear:true, width: '40%' });
    $("#hb").select2({  placeholder:"Select hub", allowClear:true, width: '40%' });
 });

$("#fclty").on("change",function(){
    $("#facility_name").val($("#fclty option:selected").text());
})
 
$("#hb").on("change",function(){
  $("#hub_name").val($("#hb option:selected").text());
})
</script>

<style type="text/css">
.limitby{
    display: none;
}
</style>
@endsection


