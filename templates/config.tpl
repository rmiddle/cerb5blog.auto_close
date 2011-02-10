
<table cellpadding="0" cellspacing="0" border="0">
  <tr>
  </tr>
</table>

<form action="{devblocks_url}{/devblocks_url}" method="POST" id="configActivity" name="configActivity" >
<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="cerb5blog.auto_close.config.tab">
<input type="hidden" name="action" value="saveCerb5BlogAutoClose">

{$translate->_('cerb5blog.auto_close.config.tab.close_days')}<br>
<input type="text" name="ac_close_days" value="{$settings->get('cerb5blog.auto_close','close_days')}" size="64"><br>
<br>

{$ac_only_unassigned=$settings->get('cerb5blog.auto_close','only_unassigned')}
{$translate->_('cerb5blog.auto_close.config.tab.only_unassigned')}<br>
<label><input type="radio" name="ac_only_unassigned" value="1" {if $ac_only_unassigned}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="ac_only_unassigned" value="0" {if !$ac_only_unassigned}checked="checked"{/if}> {$translate->_('cerb5blog.auto_close.no')}</label>
<br>
<br>

<button type="button" id="btnSubmit" onclick="genericAjaxPost('configActivity', 'feedback');"><span class="cerb-sprite sprite-check"></span> {$translate->_('common.save_changes')|capitalize}</button>
</form>
<br>
<br>
<div id="feedback" style="background-color:rgb(255,255,255);"></div>
