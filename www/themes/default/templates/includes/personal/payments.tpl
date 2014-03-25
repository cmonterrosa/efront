	{capture name = "t_my_payments_code"}
{* #cpp#ifndef COMMUNITY *}
		{if $T_CONFIGURATION.enable_balance}
			<div class = "headerTools">
				<span id = "user_balance">{$smarty.const._BALANCE}: {$T_EDITEDUSER->user.balance|formatPrice}</span>
				{if $T_CAN_ADD_PAYMENT}
				<span>
					<img src = "images/16x16/add.png" alt = "{$smarty.const._ADDBALANCE}" title = "{$smarty.const._ADDBALANCE}" />
					<a href = "javascript:void(0)" onclick = "Element.extend(this).next().toggle().next().toggle().next().toggle()">{$smarty.const._ADDBALANCE}</a>
					<input type = "text" id = "user_balance_field" size = "3" style = "display:none"/>
					<img style = "display:none" src = "images/16x16/success.png" alt = "{$smarty.const._OK}" title = "{$smarty.const._OK}" onclick = "addBalance(this, $('user_balance_field').value);" />
					<a style = "display:none;" id = "supervisor_balance">{if $smarty.session.s_type != 'administrator'}({$smarty.const._CURRENTBALANCEINYOURACCOUNT}: {$T_CURRENT_USER->user.balance|formatPrice}){/if}</a>
				</span>
				{/if}
			</div>
		{/if}
<!--ajax:paymentsTable-->
			<table style = "width:100%" class = "sortedTable" size = "{$T_TABLE_SIZE}" sortBy = "0" id = "paymentsTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "{$smarty.server.REQUEST_URI}&">
				<tr class = "topTitle defaultRowHeight">
					<td class = "topTitle" name = "timestamp">{$smarty.const._DATE}</td>
					<td class = "topTitle centerAlign" name = "amount">{$smarty.const._AMOUNT}</td>
					<td class = "topTitle" name = "method">{$smarty.const._METHOD}</td>
					<td class = "topTitle" name = "status">{$smarty.const._STATUS}</td>
				</tr>
				{foreach name = 'users_list' key = 'key' item = 'payment' from = $T_DATA_SOURCE}
				<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"}">
					<td>#filter:timestamp_time-{$payment.timestamp}#</td>
					<td class = "centerAlign">{$payment.amount|formatPrice}</td>
					<td>{$T_PAYMENT_METHODS[$payment.method]}</td>
					<td>{$payment.status}</td>
				</tr>
				{foreachelse}
				<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "6">{$smarty.const._NODATAFOUND}</td></tr>
				{/foreach}
			</table>
<!--/ajax:paymentsTable-->
{* #cpp#endif *}
	{/capture}
	{eF_template_printBlock title = $smarty.const._PAYMENTS data = $smarty.capture.t_my_payments_code image = '32x32/shopping_basket.png' options = $T_PAYMENTS_OPTIONS}
