@extends('layout')

@section('content')
   <div class="container">
        <div class="card card-container">
            <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
            <div class='top-part'>
                <img  src="{{ asset('images/coa2.png') }}" >
                <br><h4>Viral Load Program</h4>
            </div>
           Login Please
           {!! Form::open(['url' => '/auth/login','class'=>'form-signin']) !!}
               <span class='error'>{!! Session::get('flash_message') !!}</span>
               <input name='email' type="email" id="inputUsername" class="form-control glyphicon gyphicon-asterisk" placeholder="Email" value="{{ old('email') }}" required autofocus>
              
               <input name='password' type="password" id="inputPassword" class="form-control" placeholder="Password" required>
               
                <button class="btn btn-danger btn-lg " type="submit">Log in</button>
            {!! Form::close() !!}
            <!-- <a href="#" class="forgot-password">
                Forgot the password?
            </a> -->
        </div><!-- /card-container -->
    </div><!-- /container -->

    <style type="text/css">
/*
 * Specific styles of signin component
 */
/*
 * General styles
 */
body, html {
    height: 100%;
    background-repeat: no-repeat;
    background-color: #BDBDBD;
}

.card-container.card {
    max-width: 400px;
    padding: 40px 40px;
    border-radius: 10px;
}


/*
 * Card component
 */
.card {
    background-color: #F7F7F7;
    /* just in case there no content*/
    padding: 20px 25px 30px;
    margin: 0 auto 25px;
    margin-top: 50px;
    /* shadows and rounded borders */
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
    border-radius: 2px;
    -moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    -webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
}


.form-signin #inputUsername,
.form-signin #inputPassword {
    direction: ltr;
    height: 44px;
    font-size: 16px;
}

.form-signin .input-group-addon{
    margin-bottom: 10px;
    height: 40px;
}

.form-signin input[type=email],
.form-signin input[type=password],
.form-signin input[type=text],
.form-signin button {
    width: 100%;
    display: block;
    margin-bottom: 10px;
    z-index: 1;
    position: relative;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}

.form-signin .form-control:focus {
    border-color: rgb(104, 145, 162);
    outline: 0;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgb(104, 145, 162);
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgb(104, 145, 162);
}

.forgot-password {
    color: rgb(104, 145, 162);
}

.forgot-password:hover,
.forgot-password:active,
.forgot-password:focus{
    color: rgb(12, 97, 33);
}

.top-part{
    text-align: center;
}
</style>

@stop