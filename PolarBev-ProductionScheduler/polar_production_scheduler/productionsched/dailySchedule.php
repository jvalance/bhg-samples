
<!-- style="background-color: #FFF0B5; padding:2px; color: #000080; font-weight:bold" -->
<!-- font-size: 1.2em; color:navy; background-color:white;  -->
<div id="daily"
	class="ui-jqgrid ui-widget ui-widget-content ui-corner-all">
	<form id="dailyForm" name="dailyForm" action="#">
	<table id="dailylist" class="ui-jqgrid-view" style="width: 100%">
		<caption class="ui-jqgrid-titlebar ui-widget-header ui-corner-top ui-helper-clearfix left">
			<span id="dailyCaption"></span>
            <span id="dailyTotal" style="margin-left:15px; padding-left:5px; padding-right:5px;"></span>
<form>
		<button id="toShopButton" onclick="doFirmToShop()" type="button" class="green_button to_shop_button">
		Create Shop Orders
		</button>
</form>
			<div style="font-size: 11px; padding-left:2px; padding-top:4px">Double click rows or headings to expand/collapse details</div>
		</caption>
		<thead>
			<tr class="ui-jqgrid-labels" ondblclick="toggleAllDailyDtlsAuto()" >
				<th class="ui-state-default ui-th-column ui-th-ltr"
					style="vertical-align: middle; height: 10px; width: 10px;">
					<img src="images/expand2.gif" id="dailyHeadExpImg"
							style="margin: auto; display: block; height: 10px;
							       min-height: 1em; vertical-align: middle;"/></th>

				<th width="26" class="ui-state-default ui-th-column ui-th-ltr">
					Seq#
				</th>
				<th width="60" class="ui-state-default ui-th-column ui-th-ltr">
					Item#
				</th>
				<th width="178" class="ui-state-default ui-th-column ui-th-ltr">
					Description
				</th>
				<th width="50" class="ui-state-default ui-th-column ui-th-ltr">
					Qty
				</th>
				<th width="50" class="ui-state-default ui-th-column ui-th-ltr">
					Order#
				</th>
				<th width="35" class="ui-state-default ui-th-column ui-th-ltr">
					Type
				</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	</form>


</div>

