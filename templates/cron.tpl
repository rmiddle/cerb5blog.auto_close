<H3>{$translate->_('cerb5blog.auto_close.title')}</H3>
<br>

{$translate->_('cerb5blog.auto_close.config.tab.close_days')}<br>
<input type="text" name="ac_close_days" maxlength="5" size="3" value="{$ac_close_days}">
<select name="ac_close_days_term">
	<option value="m" {if $ac_close_days_term=='m'}selected{/if}>minute(s)
	<option value="h" {if $ac_close_days_term=='h'}selected{/if}>hour(s)
	<option value="d" {if $ac_close_days_term=='d'}selected{/if}>day(s)
</select><br>

{$translate->_('cerb5blog.auto_close.config.tab.only_unassigned')}<br>
<label><input type="radio" name="ac_only_unassigned" value="1" {if $ac_only_unassigned}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="ac_only_unassigned" value="0" {if !$ac_only_unassigned}checked="checked"{/if}> {$translate->_('cerb5blog.auto_close.no')}</label>
<br>
<br>
