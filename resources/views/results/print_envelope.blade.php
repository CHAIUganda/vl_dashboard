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
   
</head>

<body>
    <div style="font-size:20px; ">
    
    
        <div id="print-btn-div" style='text-align:center; padding:20px;'>
            <!-- <button id="pdf-btn" class='btn btn-danger' >Download</button> -->
            <button id="print-btn" class='btn btn-danger' >Print</button>
        </div>

        <div style="text-align:center;font-size:30px">{{ $facility->hub }}</div>
        <br><br>

        {{ $facility->facility }}<br>
        District: {{ $facility->district }}<br>
        {{ $facility->contactPerson }}<br>
        {{ $facility->phone }}<br>
        C/O:
        <br> Viral Load Results

    </div>   

    <script type="text/javascript">

    $('#print-btn').click(function(){
        $('#print-btn-div').hide();
         window.print(); 
         setTimeout(window.close, 0);          
    });

    </script>


</body>

</html>
