<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title> 

    <style type="text/css">

    .print-container{
        width: 1000px;
        margin-left: 50px;
        min-height: 1400px;
    }

    .print-header{
        text-transform: uppercase;
        font-size: 16px;
        text-align: center;
    }

    .print-header-moh{
        font-weight: bolder;
    }

    .print-ttl{
        font-weight: bolder;
        text-transform: uppercase;
        /*background-color: #D8D8D8;*/
        padding: 5px;
        font-size: 14px;
        margin-top: 10px;
    }

    .print-sect, .print-sect2{
        font-size: 14px;
        padding: 5px;
        border: 1px solid #a9a6a6;
        min-height: 110px;
    }

    .print-sect2{
        height: 120px;
    }

    .print-sect table td{
        padding: 4px;

    }


    .print-val{
        text-decoration: underline;
    }

    .print-check,.print-uncheck{
        font-size: 20px;
    }


    page {
      display: block;
      margin: 0 auto;
      margin-bottom: 0.5cm;
      padding: 5px;
    }
    page[size="A4"] {  
      width: 21cm;
      height: 29.7cm; 
    }
    page[size="A4"][layout="portrait"] {
      width: 29.7cm;
      height: 21cm;  
    }

    .printmm-container{
        width: 20cm;
    }

    .stamp{
      position: relative;
    }

    .stamp-date{
     position: absolute;
     margin-top: 55px;
     margin-left: -145px;
     font-size: 14px;
     font-weight: bold;
     color: #F01319;
    }

    .date-released{
      font-size:11px;color:#000;
      border-top:dotted 1px;
      font-weight: lighter;
    }

    </style>

</head>

<body>
<?php 
$local_today = date('d M Y');
$local_today = strtoupper($local_today);
?> 
@foreach ($vldbresult AS $result_arr)
<?php $result_obj = (object)$result_arr;  ?>
 @include('api_results._result_slip')     
@endforeach
</body>
<script type="text/javascript">
 // window.print(); 
   
    /*$('#print-btn').click(function(){
        $('#print-btn-div').hide();
        $.get("/log_printing?printed=yes&s="+$('#ss').val(), function(data){     });
        window.print(); 
        setTimeout(window.close, 0);          
    });*/
</script>

</html>