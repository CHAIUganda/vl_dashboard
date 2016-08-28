<div>
	<table class="row-border hover table table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<TH class="ng-cloak">Months</TH>
				<th class="ng-cloak" ng-repeat="month in duration_numbers_for_month_list">
					<%month%>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="figure ng-cloak">Samples Received</td>
				<td ng-repeat="samples_received in duration_numbers_for_samples_received" class="figure ng-cloak">
					<%samples_received%>
				</td>
			</tr>
			<tr>
				<td class="figure ng-cloak">Samples Tested</td>
				<td class="figure ng-cloak" ng-repeat="samples_tested in duration_numbers_for_samples_tested">
					<%samples_tested%>
				</td>
			</tr>
			<tr>
				<td class="figure ng-cloak">Patients Tested</td>
				<td class="figure ng-cloak" ng-repeat="patients_tested in duration_numbers_for_patients_tested">
					<%patients_tested%>
				</td>
			</tr>
		</tbody>
	</table>                         
</div>