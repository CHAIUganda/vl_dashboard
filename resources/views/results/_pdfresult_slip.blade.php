<?php 
$smpl_types = array(1=>'DBS', 2=>'Plasma');
$sample_type = $result_obj->sampleTypeID == 1? 'DBS' : 'Plasma';
$genders = array(
	'Female'=>'Female',
	'Male'=>'Male',
	'Left Blank'=>'Left Blank',
	);
$yes_no = array(1=>"Yes", 2=>"No");

$method="";
$machine_result = "";
$test_date = "";
$machine_type = $result_obj->machineType;
$factor = $result_obj->factor;
switch ($machine_type) {
	case 'abbott':
		$method = "Abbott Real time HIV-1 PCR";
		$mmmm_arr = explode("::", $result_obj->abbott_result);
		$mr = end($mmmm_arr);
		$mr_arr = explode("|||", $mr);
		$machine_result = isset($mr_arr[0])?$mr_arr[0]:"";
		$test_date = isset($mr_arr[1])?$mr_arr[1]:"";
		break;

	case 'roche':
		$method = "HIV-1 RNA PCR Roche";
		$mmmm_arr = explode("::", $result_obj->roche_result);
		$mr = end($mmmm_arr);
		$mr_arr = explode("|||", $mr);
		$machine_result = isset($mr_arr[0])?$mr_arr[0]:"";
		$test_date = isset($mr_arr[1])?$mr_arr[1]:"";
		break;
	
	default:
		$method = "";
		$machine_result = "";
		$test_date = "";
		break;
}

$result = "";
if(!empty($result_obj->override_result)){
	$or_arr = explode("::", $result_obj->override_result);
	$or = end($or_arr);
	$or = explode("|||", $or);
	$result = $or[0];
	$test_date = "";
}else{
	$result = $machine_result;
}

$result = MyHTML::getVLNumericResult($result, $machine_type, $factor);

$numerical_result = MyHTML::getNumericalResult($result);

$suppressed = MyHTML::isSuppressed2($numerical_result, $sample_type, $test_date);
switch ($suppressed) {
	case 'YES': // patient suppressed, according to the guidlines at that time
		$smiley="smiley.smile.gif";
		$recommendation = MyHTML::getRecommendation($suppressed, $test_date, $sample_type);
		break;

	case 'NO': // patient suppressed, according to the guidlines at that time
		$smiley="smiley.sad.gif";
		$recommendation = MyHTML::getRecommendation($suppressed, $test_date, $sample_type);					
		break;
	
	default:
		$smiley="smiley.sad.gif";
		$recommendation="There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.";
		break;
}

$location_id = "$result_obj->lrCategory$result_obj->lrEnvelopeNumber/$result_obj->lrNumericID";

$s_arr = explode("/", $result_obj->signaturePATH);
$signature = end($s_arr);

$now_s = strtotime(date("Y-m-d"));

$repeated = !empty($result_obj->repeated)?1:2;

$rejected = $result_obj->verify_outcome=="Rejected"?1:2;

$phones_arr = array_unique(explode(",", $result_obj->phone));
$phones = implode(", ", $phones_arr);
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
						<td class="print-val"><?=$result_obj->district?> | Hub: <?=$result_obj->hub_name?></td>
					</tr>
				</table>
			</div>
			
		</div>
		<div style="width:49%;float:right">
			<div class="print-ttl">sample details</div>
			<div class="print-sect">
				<table>
					<tr>
						<td>Form #: </td>
						<td class="print-val"><?=$result_obj->formNumber?></td>
					</tr>
					<tr>
						<td>Sample Type: </td>
						<td class="print-val-check"> &nbsp; <?=MyHTML::boolean_draw($smpl_types, $result_obj->sampleTypeID)?></td>
					</tr>
				</table>
			</div>
		</div>

	</div>
	<div class="print-ttl">patient information</div>
	<div class="print-sect">
		<div class="row">
			<div style="width:49%;float:left" >				
				<table>
					<tr>
						<td>ART Number: &nbsp;</td>
						<td class="print-val"><?=$result_obj->artNumber ?></td>
					</tr>
					<tr>
						<td>Other ID:</td>
						<td class="print-val"><?=$result_obj->otherID ?></td>
					</tr>
					<tr>
						<td>Gender:</td>
						<td class="print-val-check"><?=MyHTML::boolean_draw($genders, $result_obj->gender)?></td>
					</tr>
				</table>
				
			</div>
			<div style="width:49%;float:right">
				
				<table>
					<tr>
						<td>Date of Birth:</td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->dateOfBirth, 'd-M-Y') ?></td>
					</tr>
					<tr>
						<td>Phone Number:</td>
						<td class="print-val-"><?=$phones?></td>
					</tr>
				</table>
				
			</div>

		</div>
	</div>
	<div class="print-ttl">sample test information</div>
	<div class="print-sect">
		<div class="row">
			<div style="width:49%;float:left">
				
				<table>
					<tr>
						<td>Sample Collection Date: &nbsp; </td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->collectionDate, 'd-M-Y') ?></td>
					</tr>
					<tr>
						<td>Reception Date: &nbsp; </td>
						<td class="print-val"><?=MyHTML::localiseDate($result_obj->receiptDate, 'd-M-Y') ?></td>
					</tr>
					<tr>
						<td>Test Date: &nbsp; </td>
						<td class="print-val"><?=MyHTML::localiseDate($test_date, 'd-M-Y') ?></td>
					</tr>

				</table>
				
			</div>

			<div style="width:49%;float:right">
				
				<table>
					<tr>
						<td>Repeat Test:  &nbsp; </td>
						<td><?=MyHTML::boolean_draw($yes_no, $repeated)?></td>
					</tr>
					<tr>
						<td>Sample Rejected:  &nbsp; </td>
						<td><?=MyHTML::boolean_draw($yes_no, $rejected)?></td>
					</tr>
				</table>
				
			</div>

		</div>
			If rejected Reason: <?=$result_obj->rejection_reason ?>
	</div>
	<?php if ($result_obj->verify_outcome!="Rejected"){ ?>
	<div class="print-ttl">viral load results</div>
	<div class="print-sect" style="height:150px;">
		<div class="row">
			<div style="width:79%;float:left">
				<table colspan="2">
					<tr>
						<td width="40%">Method Used: </td>
						<td ><?=$method ?></td>
					</tr>

					<tr>
						<td>Location ID: </td>
						<td ><?=$location_id ?></td>
					</tr>

					<tr>
						<td>Viral Load Testing #: </td>
						<td ><?=$result_obj->vlSampleID ?></td>
					</tr>

					<tr>
						<td valign="top">Result of Viral Load: </td>
						<td ><?=$result ?></td>
					</tr>
				</table>		
				
			</div>
			<div style="width:20%;float:right">

				<img src= "{{ MyHTML::getImageData('images/'.$smiley) }}" height="150" width="150">
			</div>

		</div>		 				

	</div>


	<div class="print-ttl">recommendations</div>
	<div class="print-sect" style="width:75%;float:left">
		Suggested Clinical Action based on National Guidelines:<br>
		<div style="margin-left:10px"><?=$recommendation ?></div>
	</div>
	<?php if ($result_obj->verify_outcome!="Rejected"){ ?>
		<div style="width:20%;float:right">
			{!! QrCode::errorCorrection('H')->size("90")->generate("VL,$location_id,$suppressed,$now_s") !!}
			<!-- <div class="qrcode-output" value="<?="VL,$location_id,$suppressed,$now_s" ?>"></div> -->
		</div>
		<?php } ?>
	<?php } ?>

	<br><br>
	<div class="row">
		<?php if ($result_obj->verify_outcome!="Rejected"){ ?>
		<div style="width:15%;float:left">
			Lab Technologist: 
		</div>
		<div style="width:15%;float:left">
			<img src= "{{ MyHTML::getImageData('images/signatures/'.$signature ) }}" height="50" width="100">
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
			<span class="stamp-date" style="position: absolute;margin-top: 55px;margin-left: -145px;font-size: 18px;font-weight: bold;color: #F01319;"><?=$local_today ?></span>

		</div>
		
	</div>
	</div>
	<footer style='float:right'>1 of 1</footer>
</page>
<!-- </div> -->