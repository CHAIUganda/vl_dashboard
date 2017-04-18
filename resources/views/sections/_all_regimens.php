        <div class="facilties-sect " >
        	<div id="regimen_by_line_of_treatment" class="border-frame">
        		<span>By Line of Regimen</span>
        		<table class="table">
			      <tr>
			            <th></th>
			            <th colspan=2 class="text-center">1st Line</th>
			            
			            
			            <th colspan=2 class="text-center">2nd Line</th>
			            
			            <th colspan=2 class="text-center">Others</th>
			            
			            <th colspan=2 class="text-center">Total</th>
			            
			            
			      </tr>
			      <tr>
			          <th class="text-center">Results</th>
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			         <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			          
			      </tr>
			      <tr>
			          <td class="text-center">NS</td>
			          
			          <td class="text-center"><% regimenLineOfTreatment.firstLineNotSuppressed %></td>
			          <td class="text-center"><%((regimenLineOfTreatment.firstLineNotSuppressed/regimenLineOfTreatment.firstLine)*100 )| number:1%></td>

			          <td class="text-center"><% regimenLineOfTreatment.secondLineNotSuppressed %></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.secondLineNotSuppressed/regimenLineOfTreatment.secondLine)*100 )| number:1 %></td>

			          <td class="text-center"><% regimenLineOfTreatment.otherLineNotSuppressed%></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.otherLineNotSuppressed/regimenLineOfTreatment.otherLine)*100 )| number:1 %></td>

			          <td class="text-center"><% regimenLineOfTreatment.notSuppressed%></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.notSuppressed/regimenLineOfTreatment.total)*100 )| number:1 %></td>
			          
			      </tr>
			      <tr>
			          <td class="text-center">S</td>
			          
			          <td class="text-center"><% regimenLineOfTreatment.firstLineSuppressed %> </td>
			          <td class="text-center"><% ((regimenLineOfTreatment.firstLineSuppressed/regimenLineOfTreatment.firstLine)*100 )| number:1 %></td>
			          
			          <td class="text-center"><% regimenLineOfTreatment.secondLineSuppressed %></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.secondLineSuppressed/regimenLineOfTreatment.secondLine)*100 )| number:1 %></td>

			          <td class="text-center"><% regimenLineOfTreatment.otherLineSuppressed %></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.otherLineSuppressed/regimenLineOfTreatment.otherLine)*100 )| number:1 %></td>

			          <td class="text-center"><% regimenLineOfTreatment.suppressed%></td>
			          <td class="text-center"><% ((regimenLineOfTreatment.suppressed/regimenLineOfTreatment.total)*100 )| number:1 %></td>
			          
			          
			      </tr>
			      <tr>
			          <th  class="text-center">Total</th>
			          
			          <td class="text-center"><%regimenLineOfTreatment.firstLine %></td>
			          <td></td>
			          <td class="text-center"><%regimenLineOfTreatment.secondLine %></td>
			          <td></td>
			          <td class="text-center"><%regimenLineOfTreatment.otherLine %></td>
			          <td></td>
			          <td class="text-center"><%regimenLineOfTreatment.total %></td>
			          <td></td>
			          
			      </tr>
			  </table>
        	</div>
            <div id="first_line_regimens" class="border-frame">
            	<span>By first line regimens [Adult &amp; Paeds]</span>
        		<table class="table">
        		 <tr>
        		 	<th colspan=17 class="text-center">1st Line (ADULT)</th>
        		 </tr>
			      <tr>
			            <th></th>
			            <th colspan=2 class="text-center">1c = AZT-3TC-NVP</th>
			            
			            
			            <th colspan=2 class="text-center">1d = AZT-3TC-EFV</th>
			            
			            <th colspan=2 class="text-center">1e = TDF-3TC-NVP</th>
			            
			            <th colspan=2 class="text-center">1f = TDF-3TC-EFV</th>
			            <th colspan=2 class="text-center">1g = TDF-FTC-NVP</th>
			            
			            
			            <th colspan=2 class="text-center">1h = TDF-FTC-EFV</th>
			            
			            <th colspan=2 class="text-center">1i = ABC-3TC-EFV</th>
			            
			            <th colspan=2 class="text-center">1j = ABC-3TC-NVP</th>
			            
			      </tr>
			      <tr>
			          <th class="text-center">Results</th>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td> 

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			      </tr>
			      <tr>
			          <td class="text-center">NS</td>
			          
			          <td class="text-center"><% firstLineRegimens.one_c.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_c.notSuppressedPercentage)|number:1  %></td>


			          <td class="text-center"><% firstLineRegimens.one_d.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_d.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_e.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_e.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_f.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_f.notSuppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% firstLineRegimens.one_g.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_g.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_h.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_h.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_i.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_i.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_j.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_j.notSuppressedPercentage)|number:1  %></td>
        
			      </tr>
			      <tr>
			          <td class="text-center">S</td>
			          
			          <td class="text-center"><% firstLineRegimens.one_c.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_c.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_d.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_d.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_e.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_e.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_f.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_f.suppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% firstLineRegimens.one_g.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_g.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_h.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_h.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_i.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_i.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.one_j.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.one_j.suppressedPercentage)|number:1  %></td>

			          
			      </tr>
			      <tr>
			          <th  class="text-center">Total</th>
			          
			          	<td colspan=2 class="text-center"><% (firstLineRegimens.one_c.notSuppressed + firstLineRegimens.one_c.suppressed)%></td>
			          

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_d.notSuppressed + firstLineRegimens.one_d.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_e.notSuppressed + firstLineRegimens.one_e.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_f.notSuppressed + firstLineRegimens.one_f.suppressed)%></td>
			          
			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_g.notSuppressed + firstLineRegimens.one_g.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_h.notSuppressed + firstLineRegimens.one_h.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_i.notSuppressed + firstLineRegimens.one_i.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (firstLineRegimens.one_j.notSuppressed + firstLineRegimens.one_j.suppressed)%></td>
			         

			          
			      </tr>
			  </table>
				
			 <br><br><br>
			  <table class="table">
        		 <tr>
        		 	<th colspan=13 class="text-center">1st Line (PAEDS)</th>
        		 </tr>
			      <tr>
			            <th></th>
			            <th colspan=2 class="text-center">4a = d4T-3TC-NVP</th>
			            
			            
			            <th colspan=2 class="text-center">14b = d4T-3TC-EFV</th>
			            
			            <th colspan=2 class="text-center">4c = AZT-3TC-NVP</th>
			            
			            <th colspan=2 class="text-center">4d = AZT-3TC-EFV</th>
			            <th colspan=2 class="text-center">4e = ABC-3TC-NVP</th>
			            
			            
			            <th colspan=2 class="text-center">4f = ABC-3TC-EFV</th>
			            
			            
			            
			      </tr>
			      <tr>
			          <th class="text-center">Results</th>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          
			          
			      </tr>
			      <tr>
			          <td class="text-center">NS</td>
			          
			          <td class="text-center"><% firstLineRegimens.four_a.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_a.notSuppressedPercentage)|number:1  %></td>


			          <td class="text-center"><% firstLineRegimens.four_b.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_b.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_c.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_c.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_d.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_d.notSuppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% firstLineRegimens.four_e.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_e.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_f.notSuppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_f.notSuppressedPercentage)|number:1  %></td>

			          
        
			      </tr>
			      <tr>
			          <td class="text-center">S</td>
			          
			           <td class="text-center"><% firstLineRegimens.four_a.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_a.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_b.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_b.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_c.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_c.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_d.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_d.suppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% firstLineRegimens.four_e.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_e.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% firstLineRegimens.four_f.suppressed %></td>
			          <td class="text-center"><% (firstLineRegimens.four_f.suppressedPercentage)|number:1  %></td>

			        

			          
			      </tr>
			      <tr>
			          <th  class="text-center">Total</th>
			          
			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_a.notSuppressed + firstLineRegimens.four_a.suppressed) %></td>
			          

			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_b.notSuppressed + firstLineRegimens.four_b.suppressed) %></td>


			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_c.notSuppressed + firstLineRegimens.four_c.suppressed) %></td>


			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_d.notSuppressed + firstLineRegimens.four_d.suppressed) %></td>

			          
			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_e.notSuppressed + firstLineRegimens.four_e.suppressed) %></td>


			          <td colspan=2 class="text-center"><% ( firstLineRegimens.four_f.notSuppressed + firstLineRegimens.four_f.suppressed) %></td>


			      

			          
			      </tr>
			  </table>

            </div>
                    <div id="second_line_regimens" class="border-frame">
            	<span>By second line regimens [Adult &amp; Paeds] &amp; Others</span>
        		<table class="table">
        		 <tr>
        		 	<th colspan=17 class="text-center">2nd Line (ADULT)</th>
        		 </tr>
			      <tr>
			            <th></th>
			            <th colspan=2 class="text-center">2b = TDF-3TC-LPV/r</th>
			            
			            
			            <th colspan=2 class="text-center">2c = TDF-FTC-LPV/r</th>
			            
			            <th colspan=2 class="text-center">2e = AZT-3TC-LPV/r</th>
			            
			            <th colspan=2 class="text-center">2f = TDF-FTC-ATV/r</th>
			            <th colspan=2 class="text-center">2g = TDF-3TC-ATV/r</th>
			            
			            
			            <th colspan=2 class="text-center">2h = AZT-3TC-ATV/r</th>
			            
			            <th colspan=2 class="text-center">2i = ABC-3TC-LPV/r</th>
			            
			            <th colspan=2 class="text-center">2j = ABC-3TC-ATV/r</th>
			            
			      </tr>
			      <tr>
			          <th class="text-center">Results</th>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td> 

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			      </tr>
			      <tr>
			          <td class="text-center">NS</td>
			          
			          <td class="text-center"><% secondLineRegimens.two_b.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_b.notSuppressedPercentage)|number:1  %></td>


			          <td class="text-center"><% secondLineRegimens.two_c.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_c.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_e.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_e.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_f.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_f.notSuppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% secondLineRegimens.two_g.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_g.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_h.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_h.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_i.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_i.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_j.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_j.notSuppressedPercentage)|number:1  %></td>
        
			      </tr>
			      <tr>
			          <td class="text-center">S</td>
			          
			          <td class="text-center"><% secondLineRegimens.two_b.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_b.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_c.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_c.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_e.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_e.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_f.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_f.suppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% secondLineRegimens.two_g.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_g.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_h.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_h.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_i.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_i.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.two_j.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.two_j.suppressedPercentage)|number:1  %></td>

			          
			      </tr>
			      <tr>
			          <th  class="text-center">Total</th>
			          
			          	<td colspan=2 class="text-center"><% (secondLineRegimens.two_b.notSuppressed + secondLineRegimens.two_b.suppressed)%></td>
			          

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_c.notSuppressed + secondLineRegimens.two_c.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_e.notSuppressed + secondLineRegimens.two_e.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_f.notSuppressed + secondLineRegimens.two_f.suppressed)%></td>
			          
			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_g.notSuppressed + secondLineRegimens.two_g.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_h.notSuppressed + secondLineRegimens.two_h.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_i.notSuppressed + secondLineRegimens.two_i.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.two_j.notSuppressed + secondLineRegimens.two_j.suppressed)%></td>
			         

			          
			      </tr>
			  </table>
				
			 <br><br><br>

        		<table class="table">
        		 <tr>
        		 	<th colspan=13 class="text-center">2nd Line (PAEDS)</th>
        		 	<th colspan=4 class="text-center">Others</th>
        		 </tr>
			      <tr>
			            <th></th>
			            <th colspan=2 class="text-center">5d = TDF-3TC-LPV/r</th>
			            
			            
			            <th colspan=2 class="text-center">5e = TDF-FTC-LPV/r</th>
			            
			            <th colspan=2 class="text-center">5g = AZT-ABC-LPV/r</th>
			            
			            <th colspan=2 class="text-center">5i = AZT-3TC-ATV/r</th>
			            <th colspan=2 class="text-center">5j = ABC-3TC-LPV/r</th>
			            
			            
			            <th colspan=2 class="text-center">5k = ABC-3TC-ATV/r</th>
			            
			            <th colspan=2 class="text-center">Left Blank</th>
			            
			            <th colspan=2 class="text-center">Other</th>
			            
			      </tr>
			      <tr>
			          <th class="text-center">Results</th>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td> 

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>

			          <td class="text-center">#</td>
			          <td class="text-center">%</td>
			          
			      </tr>
			      <tr>
			          <td class="text-center">NS</td>
			          
			          <td class="text-center"><% secondLineRegimens.five_d.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_d.notSuppressedPercentage)|number:1  %></td>


			          <td class="text-center"><% secondLineRegimens.five_e.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_e.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_g.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_g.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_i.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_i.notSuppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% secondLineRegimens.five_j.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_j.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_k.notSuppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_k.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% otherRegimens.left_blank.notSuppressed %></td>
			          <td class="text-center"><% (otherRegimens.left_blank.notSuppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% otherRegimens.other_regimen.notSuppressed %></td>
			          <td class="text-center"><% (otherRegimens.other_regimen.notSuppressedPercentage)|number:1  %></td>
        
			      </tr>
			      <tr>
			          <td class="text-center">S</td>
			          
			          <td class="text-center"><% secondLineRegimens.five_d.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_d.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_e.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_e.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_g.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_g.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_i.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_i.suppressedPercentage)|number:1  %></td>
			          
			          <td class="text-center"><% secondLineRegimens.five_j.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_j.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% secondLineRegimens.five_k.suppressed %></td>
			          <td class="text-center"><% (secondLineRegimens.five_k.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% otherRegimens.left_blank.suppressed %></td>
			          <td class="text-center"><% (otherRegimens.left_blank.suppressedPercentage)|number:1  %></td>

			          <td class="text-center"><% otherRegimens.other_regimen.suppressed %></td>
			          <td class="text-center"><% (otherRegimens.other_regimen.suppressedPercentage)|number:1  %></td>

			          
			      </tr>
			      <tr>
			          <th  class="text-center">Total</th>
			          
			          	<td colspan=2 class="text-center"><% (secondLineRegimens.five_d.notSuppressed + secondLineRegimens.five_d.suppressed)%></td>
			          

			          <td colspan=2 class="text-center"><% (secondLineRegimens.five_e.notSuppressed + secondLineRegimens.five_e.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.five_g.notSuppressed + secondLineRegimens.five_g.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.five_i.notSuppressed + secondLineRegimens.five_i.suppressed)%></td>
			          
			          <td colspan=2 class="text-center"><% (secondLineRegimens.five_j.notSuppressed + secondLineRegimens.five_j.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (secondLineRegimens.five_k.notSuppressed + secondLineRegimens.five_k.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (otherRegimens.left_blank.notSuppressed + otherRegimens.left_blank.suppressed)%></td>

			          <td colspan=2 class="text-center"><% (otherRegimens.other_regimen.notSuppressed + otherRegimens.other_regimen.suppressed )%></td>
			         

			          
			      </tr>
			  </table>
			</div>
        </div>
         
>>>>>>> regall
