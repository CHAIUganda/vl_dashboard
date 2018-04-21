<?php
$genders = ['F'=>'Female', 'M'=>'Male', 'L'=>'Left Blank'];
$suppressed_vals = [1=>'YES', 2=>'NO', 3=>'UNKNOWN'];
//$suppressed = $suppressed_vals[$result_obj->suppressed];

$date_collected = !empty($result_obj->date_collected)?$result_obj->date_collected:$result_obj->test_date;
switch ($result_obj->suppressed) {
	case 1: // patient suppressed, according to the guidlines at that time
		$smiley="smiley.smile.gif";
		$recommendation = MyHTML::getRecommendation2(1, $date_collected, $result_obj->dob);
		break;

	case 2: // patient suppressed, according to the guidlines at that time
		$smiley="smiley.sad.gif";
		$recommendation = MyHTML::getRecommendation2(2, $date_collected, $result_obj->dob, $result_obj->treatment_line['code']);					
		break;
	
	default:
		$smiley="";
		$recommendation="There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.";
		break;
}

$location_id = $result_obj->locator_category.$result_obj->envelope_number."/".$result_obj->locator_position;
$rejected = $result_obj->accepted==0?1:2;
$now_s = strtotime(date("Y-m-d"));
$signature_arr = explode("/",$result_obj->signature);
$signature = end($signature_arr);
$signature_img = MyHTML::getImageData("images/signatures/$signature");
$signature_img = empty($signature_img)|| empty($signature) ||$signature_img=="data:image/;base64,"?MyHTML::getImageData('images/signatures/signature.148.png'):$signature_img;

//$phone = isset($result_obj->patient['patientphone_set'][0]['phone'])?$result_obj->patient['patientphone_set'][0]['phone'] 	:"";
 ?>
<page size="A4">
	<div style="height:95%">
<!-- <div class="print-container"> -->
	<div class="print-header">
		<img src="{{ MyHTML::getImageData('images/uganda.emblem.gif') }}">
		<div class="print-header-moh">
			ministry of health uganda<br>
			national aids control program<br>
		</div>

	central public health laboratories<br>
	
	<u>viral load test results</u><br>
	</div>
	<!-- <div class="print-henader">
		<div style="width:10%;float:left"><img src="{{ MyHTML::getImageData('images/uganda.emblem.gif') }}"></div>
		<div  style="width:90%;float:left" class="print-header">		
			<h4>MINISTRY OF HEALTH UGANDA</h4>
			<h4>CENTRAL PUBLIC HEALTH LABORATORIES</h4>
			<h6>P.O. Box 7272, Plot 1062-106 Butabika Road, Luzira,Toll free line 0800-221100</h6>	
		</div>
	</div> -->
	<div class="row">
		<div style="width:49%;float:left">
			<div class="print-ttl">facility details</div>
			<div class="print-sect">
				<table>
					<tr>
						<td>Name:</td>
						<td class="print-val"><?=$result_obj->facility?></td>
					</tr>
					<tr>
						<td>District:</td>
						<td class="print-val"><?=$result_obj->district?></td>
					</tr>
					<tr>
						<td>Hub:</td>
						<td class="print-val"><?=$result_obj->hub ?></td>
					</tr>
				</table>
			</div>
			
		</div>
		
		<div style="width:49%;float:right">
			<div class="print-ttl">sample details</div>
			<div class="print-sect">
				<table>
					<tr>
						<td>Form #:</td>
						<td class="print-val"><?=$result_obj->form_number?></td>
					</tr>
					<tr>
						<td>Sample Type:</td>
						<td class="print-val-check"><?=MyHTML::boolean_draw(['D'=>'DBS','P'=>'Plasma'], $result_obj->sample_type)?></td>
					</tr>
					<tr>
						<td >Collection&nbsp;Date:</td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->date_collected, 'd-M-Y') ?></td>
					</tr>
					<tr>
						<td>Reception&nbsp;Date:</td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->date_received, 'd-M-Y') ?></td>
					</tr>
				</table>
			</div>
		</div>

	</div>

	<div class="row">
		<div style="width:49%;float:left" >	
			<div class="print-ttl">patient information</div>
			<div class="print-sect2">			
				<table cellspacing="3">
					<tr>
						<td>ART Number: &nbsp;</td>
						<td class="print-val"><?=$result_obj->art_number?></td>
					</tr>
					<tr>
						<td>Other ID:</td>
						<td class="print-val"><?=$result_obj->other_id ?></td>
					</tr>
					<tr>
						<td>Sex:</td>
						<td class="print-val-check"><?=MyHTML::boolean_draw($genders, $result_obj->gender)?></td>
					</tr>
				
					<tr>
						<td>Date of Birth:</td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->dob, 'd-M-Y') ?></td>
					</tr>
					
				</table>				
			</div>
		</div>
		<div style="width:49%;float:right">	
			<div class="print-ttl">treatment information</div>
			<div class="print-sect2">			
				<table cellspacing="3">
					<tr>
						<td>Current&nbsp;regimen:</td>
						<td class="print-val"><?=$result_obj->current_regimen ?></td>
					</tr>
					<tr>
						<td width="30%">Treatment&nbsp;Initiation&nbsp;date:</td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->treatment_initiation_date, 'd-M-Y') ?></td>
					</tr>
					<tr>
						<td>Pregnant?:<?=MyHTML::boolean_draw(['N'=>'No','Y'=>'Yes'], $result_obj->pregnant)?></td>
						<td class="print-val-check">  ANC #: <u><?=$result_obj->anc_number?></u></td>
					</tr>
					<tr>
						<td>Breastfeeding? :</td>
						<td class="print-val-check"><?=MyHTML::boolean_draw(['N'=>'No','Y'=>'Yes'], $result_obj->breast_feeding)?></td>
					</tr>
					
				</table>				
			</div>
		</div>

	</div>
	<?php if($rejected==1){ ?>

	<div class="row">
		<div style="width:100%;float:left" >	
			<div class="print-ttl">Rejected sample</div>
			<div class="print-sect" style="width:80%;float:left">
				<br><b>Rejection Reason:</b> &nbsp; <?=$result_obj->rejection_reason ?>	
			</div>
			<div style="width:16%;float:right">
				{!! QrCode::errorCorrection('H')->size("90")->generate("VL,$location_id,'yes',$now_s") !!}
			</div>
		</div>	
	</div>

	<?php } else { ?>
	<div class="row">
		<div style="width:100%;float:left" >	
			<div class="print-ttl">viral load results</div>
				<div class="print-sect" style="height:150px;">
				<div class="row">
					<div style="width:79%;float:left">
						<table cellspacing="4">
							<tr>
								<td>Test Date: &nbsp; </td>
								<td ><?=MyHTML::localiseDate($result_obj->test_date, 'd-M-Y') ?></td>
							</tr>
							<tr>
								<td width="50%">Method Used: </td>
								<td ><?=MyHTML::methodUsed($result_obj->method)?></td>
							</tr>

							<tr>
								<td>Location ID: </td>
								<td><?=$location_id?></td>
							</tr>

							<tr>
								<td>Viral Load Testing #: </td>
								<td ><?=$result_obj->vl_sample_id ?></td>
							</tr>

							<tr>
								<td valign="top">Result of Viral Load: </td>
								<td ><?=$result_obj->result_alphanumeric ?></td>
							</tr>
						</table>		
						
					</div>
					<div style="width:20%;float:right">

						@if($result_obj->suppressed!='3') 
						 <img src= "{{ MyHTML::getImageData('images/'.$smiley) }}" height="150" width="150">
						@endif
					</div>

				</div>		 				

			</div>	
		</div>			
	</div>
	<div class="row">
		<div style="width:100%;float:left" >	
			<div class="print-ttl">Suggested Clinical Action based on National Guidelines</div>
			<div class="print-sect" style="width:80%;float:left">
				<div><?=$recommendation ?></div>
			</div>
			<div style="width:16%;float:right">
				{!! QrCode::errorCorrection('H')->size("90")->generate("VL,$location_id,'yes',$now_s") !!}
			</div>
		</div>	
	</div>
	<?php } ?>
	<div class="row">
		<div style="width:100%;float:left; margin-top:15px;" >
			<?php if ($rejected!=1){ ?>
			<div style="width:15%;float:left">
				Lab Technologist: 
			</div>
			<div style="width:15%;float:left">
				<img src= "{{ $signature_img }}" height="50" width="100">
				<hr>
			</div>
			<?php } ?>
			<div style="width:10%;float:left">
				Lab Manager: 
			</div>
			<div style="width:15%;float:left">
				<img src="{{ MyHTML::getImageData('images/signatures/signature.14.gif') }}" height="50" width="100">
				<hr>
			</div>

			<div style="width:35%;float:right">
				<img src="{{ MyHTML::getImageData('images/stamp.vl.png') }}" class="stamp" >
				<span class="stamp-date"><?=strtoupper(date('d M Y', strtotime($result_obj->released_at))) ?><br><span class='date-released'>DATE RELEASED</span></span>

			</div>
		</div>
		
	</div>
	</div>
	<footer><span style='float:left'>"a SANAS Accredited Medical Laboratory, No. M0589"</span> <span style='float:right'>1 of 1</span></footer>
</page>
<!-- </div> -->