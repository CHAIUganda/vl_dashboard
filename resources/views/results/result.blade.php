<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet"> 

    <link href="{{ asset('/css/vl2.css') }}" rel="stylesheet">
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />

    
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript" ></script>
    <script src="{{ asset('/js/jquery.qrcode.min.js')}}" type="text/javascript"></script>

   
    <script src="{{ asset('/js/general.js') }}" type="text/javascript"></script>    

</head>

<body>
    <?php 
    $local_today = date('d M Y');
    $local_today = strtoupper($local_today);
    ?>
    <?php $view = \Request::get('view'); ?>
    <div id="print-btn-div" style='text-align:center; padding:20px;'>
        <button id="pdf-btn" class='btn btn-danger' >Download</button>
        @if($view!='yes')<button id="print-btn" class='btn btn-danger' >Print</button>@endif
    </div>
    <?php $samples_str="" ?>
    @foreach ($vldbresult AS $result_obj) 
    <?php $samples_str .= $result_obj->id."," ?>
     @include('results._result_slip')      
    @endforeach
    {!! Form::hidden('samples',trim($samples_str, ','),['id'=>'ss']) !!}

    <script type="text/javascript">
        jQuery(function(){
            jQuery('.qrcode-output').each(function (index){
                var val = $(this).attr("value");
                $(this).qrcode({
                    text: val,
                    width: 75,
                    height:75
                });
            });         
        });

        $('#print-btn').click(function(){
            $('#print-btn-div').hide();
            $.get("/log_printing?printed={{$printed}}&s="+$('#ss').val(), function(data){     });
             window.print(); 
             setTimeout(window.close, 0);          
        });

        $('#pdf-btn').click(function(){
            window.location.assign("/result?pdf=1&samples="+$('#ss').val());
            //$.get("/result?samples="+$('#ss').val(), function(data){     });
        });

        </script>


</body>

</html>
