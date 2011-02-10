<?php

class Cerb5BlogAutoCloseCron extends CerberusCronPageExtension {
  const EXTENSION_ID = 'cerb5blog.auto_close.cron';

	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Cerb5Blog.com] Running Auto Close Cron Task.");

		@ini_set('memory_limit','128M');

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_close_days = $this->getParam('close_days', 7);
 
		$logger->info("[Cerb5Blog.com] Finished processing Auto Close Cron Job.");
  }
 
	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_close_days = $this->getParam('close_days', 7);
		$tpl->assign('only_unassigned', $ac_only_unassigned);
		$tpl->assign('close_days', $ac_close_days);
 
		$tpl->display($tpl_path . 'cron.tpl');
	}
 
	function saveConfigurationAction() {
		@$ac_only_unassigned = DevblocksPlatform::importGPC($_REQUEST['ac_only_unassigned'],'integer',0);
		@$ac_close_days = DevblocksPlatform::importGPC($_REQUEST['ac_close_days'],'integer',7);
		$this->setParam('only_unassigned', $ac_only_unassigned);
		$this->setParam('close_days', $ac_close_days);
  }
};

endif;
