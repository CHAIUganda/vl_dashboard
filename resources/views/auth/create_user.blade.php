@extends('layout')

@section('content')

<link   href="{{ asset('/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('/js/select2.min.js') }}" type="text/javascript"></script>
<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">New user</h3> </div>
    <div class="panel-body">
        {!! Session::get('msge') !!}
        {!! Form::open(['enctype'=>'multipart/form-data','url'=>'admin/create_user','id'=>'form_id','onsubmit'=>'return chkForm(this)']) !!}

        <table class='table table-bordered'>
            <tr>
                <td class='td_label' width='20%'><label for='a'>Name:</label></td>
                <td>{!! Form::text('name','',['class'=>'form-control','id'=>'a','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='c'>User name:</label></td>
                <td>{!! Form::text('username','',['class'=>'form-control','id'=>'c','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='d'>Password:</label></td>
                <td>{!! Form::password('password',['class'=>'form-control','id'=>'d','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='e'>Confirm Password:</label></td>
                <td>{!! Form::password('confirm_password',['class'=>'form-control','id'=>'e','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='user_r'>User role:</label></td>
                <td>{!! Form::select('role_id',[""=>""]+$roles,"",['id'=>'user_r']) !!}</td>
            </tr>
            <tr>
                <td class='td_label'><label for='g'>Email:</label></td>
                <td>{!! Form::email('email','',['class'=>'form-control','id'=>'g','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='i'>Telephone:</label></td>
                <td>{!! Form::text('telephone','',['class'=>'form-control phone','id'=>'i','required'=>1]) !!} </td>
            </tr>
            <tr>
                <td class='td_label'><label for='k'>Limit by:</label></td>
                <td >
                    {!! Form::radio('limit_by','1','',['onchange'=>'showLimit(1)']) !!} Facility
                    {!! Form::radio('limit_by','2','',['onchange'=>'showLimit(2)']) !!} Hub
                    <br>
                    <div class='limitby' id='limit1'>{!! Form::select('facility_id',[""=>""]+$facilities,"",['id'=>'fclty']) !!}</div>
                    <div class='limitby' id='limit2'>{!! Form::select('hub_id',[""=>""]+$hubs,"",['id'=>'hb']) !!}</div>
                </td>
            </tr>
            <tr><td/><td>{!! MyHTML::submit('Save','btn btn-danger','create_new') !!} </td></tr>
        </table>
        {!! Form::hidden('hub_name', '', ['id'=>'hub_name']) !!}
        {!! Form::hidden('facility_name', '', ['id'=>'facility_name']) !!}

        {!! Form::close() !!}
    </div>
</div>
<script type="text/javascript">
$('#admin').addClass('active');
 function chkForm(d){
    if(d.password.value!=d.confirm_password.value){
        alert('Password mismatch!!');
        return false;
    }else{
        return true;
    }   
 }

 function showLimit(val){
    $(".limitby").attr('style','display:none');
    document.getElementById('limit'+val).style.display="block";
 }

 $(document).ready(function() {
    document.getElementById('a').focus();
    $("#user_r").select2({  placeholder:"Select user role", width: '40%' });
    $("#fclty").select2({   placeholder:"Select facility", allowClear:true, width: '40%' });
    $("#hb").select2({  placeholder:"Select hub", allowClear:true, width: '40%' });
 });

 $(".phone").on("change", function() {

    var formattedPhoneNo = formatPhoneNumber(this.value);

    if(formattedPhoneNo === ""){
        alert("Phone Number is NOT valid: Please type it again");
        this.value = ""; // this.value = this.value.replace(/\D+/g, "");
        return false;
    }
    this.value = "+" + formattedPhoneNo.replace(/([\S\s]{3})/g , "$1 ");
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

@endsection()