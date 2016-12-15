@extends('layout')

@section('content')
<div class="panel panel-default">
	<div class="panel-heading"><b>Changing password</b></div>
	<div class="panel-body">
		{!! Session::get('msge') !!}
		{!! Form::open(['url'=>'/change_password','id'=>'form_id','onsubmit'=>'return chkForm(this)']) !!}

		<table class='table table-bordered'>
			<tr>
				<td class='td_label' width='30%'><label for='a'>User name:</label></td>
				<td>{!! \Auth::user()->username !!} </td>
			</tr>
			<tr>
				<td class='td_label'><label for='b'>Current Password:</label></td>
				<td>{!! Form::password('current_password',['class'=>'form-control','id'=>'b','required'=>1]) !!} </td>
			</tr>
			<tr>
				<td class='td_label'><label for='c'>New Password:</label></td>
				<td>{!! Form::password('password',['class'=>'form-control','id'=>'c','required'=>1]) !!} </td>
			</tr>
			<tr>
				<td class='td_label'><label for='d'>Confirm Password:</label></td>
				<td>{!! Form::password('confirm_password',['class'=>'form-control','id'=>'d','required'=>1]) !!} </td>
			</tr>
			
			<tr><td/><td>{!! Form::submit('Change Password', ['class'=>'btn btn-danger']) !!} </td></tr>
		</table>

		{!! Form::close() !!}
	</div>
</div>
<script type="text/javascript">
 function chkForm(d){
 	if(d.password.value!=d.confirm_password.value){
 		alert('Password mismatch!!');
 		return false;
 	}else{
 		return true;
 	}
 	
 }
</script>
@endsection



