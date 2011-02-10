<H3>{$translate->_('cerb5blog.auto_close.title')}</H3>
<br>

{$translate->_('cerb5blog.auto_close.config.tab.close_days')}<br>
<input type="text" name="ac_close_days" value="{$ac_close_days}" size="64"><br>
<br>

{$translate->_('cerb5blog.auto_close.config.tab.only_unassigned')}<br>
<label><input type="radio" name="ac_only_unassigned" value="1" {if $ac_only_unassigned}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="ac_only_unassigned" value="0" {if !$ac_only_unassigned}checked="checked"{/if}> {$translate->_('cerb5blog.auto_close.no')}</label>
<br>
<br>
