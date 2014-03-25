{* #cpp#ifdef ENTERPRISE *}



<script type="text/JavaScript">

var sessionType = "{$smarty.session.s_type}";
var deactivateConst = "{$smarty.const._DEACTIVATE}";
var activateConst = "{$smarty.const._ACTIVATE}";
var editBranch;
{if isset($smarty.get.edit_branch)}
editBranch = "{$smarty.get.edit_branch}";
{/if}
var editSkill;
{if isset($smarty.get.edit_skill)}
editSkill = "{$smarty.get.edit_skill}";
{/if}
var editJobDescription;
{if isset($smarty.get.edit_job_description)}
editJobDescription = "{$smarty.get.edit_job_description}";
{/if}
var detailsConst ='{$smarty.const._DETAILS}';
var deleteConst	 ='{$smarty.const._DELETE}';
var futureAssignmentsWill = '{$smarty.const._FUTUREASSIGNMENTSWILL}';
var futureAssignmentsWillNot = '{$smarty.const._FUTUREASSIGNMENTSWILLNOT}';
var noUsersFound = '{$smarty.const._NOUSERSFOUND}';

{if $smarty.const.MSIE_BROWSER == 1}
	function simulateJobSelects() {ldelim}
		{foreach name = 'users_list' key = 'key' item = 'user' from = $T_EMPLOYEES}
			{if $user.active}
			{literal}
				var select_item = document.getElementById('job_selection_row{/literal}{$user.login}{literal}');
				if (select_item) {
					if (!select_item.selectedIndex) {
						select_item.selIndex = 1;
						select_item.selectedIndex = 1; //always exists - 'No specific job description' in the branch
					}
					emulateDisabledOptions(select_item);
				}
				{/literal}
			{/if}
		{/foreach}
	{rdelim}
{/if}
</script>


{* ---------------------------------------- MODULE DESCRIPTION ------------------------------------ *}

{if $smarty.get.op == 'employees'}
	{include file="includes/hcd/employees.tpl"}
{/if}
{if $smarty.get.op == 'branches'}
	{include file="includes/hcd/branches.tpl"}
{/if}
{if $smarty.get.op == 'skills'}
	{include file="includes/hcd/skills.tpl"}
{/if}
{if $smarty.get.op == 'skill_cat'}
	{include file="includes/hcd/skill_categories.tpl"}
{/if}
{if $smarty.get.op == 'job_descriptions'}
	{include file="includes/hcd/job_descriptions.tpl"}
{/if}
{if $smarty.get.op == 'reports'}
	{include file="includes/hcd/reports.tpl"}
{/if}

{* ****************** CHART ************************** *}
{if $smarty.get.op == 'chart'}
	{include file="includes/hcd/chart.tpl"}
{/if}

{* ****************** PLACEMENTS ************************** *}
{if $smarty.get.op == 'placements'}
{*  **************************************************************
	This is the form that contains the employee's job descriptions
	**************************************************************	*}
	{capture name = 't_employee_jobs'}
		{* Check permissions for allowing user to assign a new job *}
		{if $smarty.session.s_type == "administrator" || ($smarty.session.employee_type == $smarty.const._SUPERVISOR && $T_CTG != 'personal')}
		<table>
			<tr>
				<td><a href="#" onclick="add_new_job_row({$T_PLACEMENTS_SIZE})"><img src="/images/16x16/add.png" title="{$smarty.const._NEWJOBDESCRIPTION}" alt="{$smarty.const._NEWJOBDESCRIPTION}"/ border="0"></a></td><td><a href="#" onclick="add_new_job_row({$T_PLACEMENTS_SIZE})">{$smarty.const._NEWJOBDESCRIPTION}</a></td>
			</tr>
		</table>
		{/if}

		<form id="required_training_form">
			<table border = "0" width = "100%" class = "sortedTable" id="jobsTable">
				<tr class = "topTitle">
					<td class = "topTitle">{$smarty.const._BRANCHNAME}</td>
					<td class = "topTitle">{$smarty.const._JOBDESCRIPTION}</td>
					<td class = "topTitle">{$smarty.const._EMPLOYEEPOSITION}</td>
				</tr>

			{if isset($T_PLACEMENTS)}
				{foreach name = 'users_list' key = 'key' item = 'placement' from = $T_PLACEMENTS}
				<tr>
					<td>{if $placement.supervisor == 1} <a href="{$smarty.session.s_type}.php?ctg=module_hcd&op=branches&edit_branch={$placement.branch_ID}">{$placement.name}</a>{else}{$placement.name}{/if}</td>
					<td>{$placement.description}</td>
					<td>{if $placement.supervisor == 0} {$smarty.const._EMPLOYEE} {else} {$smarty.const._SUPERVISOR} {/if}</td>
				</tr>
				{/foreach}
			{else}
				 <tr id="no_jobs_found">
					<td colspan=4 class = "emptyCategory">{$smarty.const._NOPLACEMENTSASSIGNEDYET}</td>
				 </tr>
			{/if}
			<tr><td>&nbsp;</td></tr>
			</table>
		</form>
	{/capture}

	{eF_template_printBlock title = $smarty.const._JOBDESCRIPTIONS data = $smarty.capture.t_employee_jobs image = '32x32/organization.png'}
{/if}

{* ****************** MAIN HCD PAGE ************************** *}
{if !isset($smarty.get.op)}
	{*assign var = "title" value = '<a class = "titleLink" href ="$smarty.server.PHP_SELF">'|cat:$smarty.const._HCD|cat:'</a>'*}
	{eF_template_printBlock title=$smarty.const._OPTIONS columns=3 links=$T_ADMIN_OPTIONS image='32x32/options.png' help = 'Organization'}
{/if}

{* #cpp#endif *}
