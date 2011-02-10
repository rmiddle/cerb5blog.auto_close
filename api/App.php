<?php

class Cerb5BlogLastActionAndAuditLogConfigTab extends Extension_ConfigTab {
	const ID = 'cerb5blog.auto_close.config.tab';

	function showTab() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->cache_lifetime = "0";

		$tpl->display('file:' . $tpl_path . 'config.tpl');
	}

	function saveCerb5BlogAuditLogAction() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->cache_lifetime = "0";

		@$ac_only_unassigned = DevblocksPlatform::importGPC($_REQUEST['ac_only_unassigned'],'integer',0);
		@$ac_close_days = DevblocksPlatform::importGPC($_REQUEST['ac_close_days'],'integer',7);
		
		$settings->set('cerb5blog.auto_close','al_merge_enabled', $ac_only_unassigned);
		$settings->set('cerb5blog.auto_close','only_unassigned', $ac_close_days);

		$tpl->display('file:' . $tpl_path . 'config_success.tpl');
	}
};
