<table class="table table-bordered table-condensed table-striped summary-tab">
	<tr>
		<th class="ng-cloak">Months</th>
		<th class="ng-cloak" ng-repeat="dn in duration_numbers">
			<% dn._id | d_format %>
		</th>
	</tr>
	<tr>
		<td class="tb-label">Samples Received</td>
		<td ng-repeat="dn in duration_numbers" class="figure ng-cloak">
			<% dn.samples_received %>
		</td>
	</tr>
	<tr>
		<td class="tb-label">Patients for Samples received</td>
		<td class="figure ng-cloak" ng-repeat="dn in duration_numbers">
			<% dn.patients_received %>
		</td>
	</tr>
	<tr>
		<td class="tb-label">Samples Tested</td>
		<td class="figure ng-cloak" ng-repeat="dn in duration_numbers">
			<% dn.total_results %>
		</td>
	</tr>
	<tr>
		<td class="tb-label">Valid Tests</td>
		<td class="figure ng-cloak" ng-repeat="dn in duration_numbers">
			<% dn.valid_results %>
		</td>
	</tr>
	<tr>
		<td class="tb-label">Suppression Rate</td>
		<td class="figure ng-cloak" ng-repeat="dn in duration_numbers">
			<% (dn.suppressed/dn.valid_results)*100 | number:1 %>%
		</td>
	</tr>
	<tr>
		<td class="tb-label">Rejection Rate</td>
		<td class="figure ng-cloak" ng-repeat="dn in duration_numbers">
			<% (dn.rejected_samples/dn.samples_received)*100 | number:1 %>%
		</td>
	</tr>
</table>  