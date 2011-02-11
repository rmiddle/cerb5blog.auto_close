<?php

class Cerb5BlogAutoCloseCron extends CerberusCronPageExtension {
  const EXTENSION_ID = 'cerb5blog.auto_close.cron';

	function run() {
		$db = DevblocksPlatform::getDatabaseService();
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Cerb5Blog.com] Running Auto Close Cron Task.");

		@ini_set('memory_limit','128M');

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_close_days = $this->getParam('close_days', 7);
		@$ac_close_days_term = $this->getParam('close_days_term', 'd');
		$close_time = time();
		$close_time -= CerberusCronPageExtension::getIntervalAsSeconds($duration, $term);
		
		$sql = "SELECT t.id ";
		$sql .= "FROM ticket t ";
		$sql .= sprintf("WHERE t.updated_date < %d  ", $close_time));
		$sql .= "AND t.is_waiting = 1 ";
		$sql .= "GROUP BY t.id ";
		$sql .= "ORDER BY t.id ";
		$logger->info("[Cerb5Blog.com] SQL = " . $sql);
		
		$rs = $db->Execute($sql);
		while($row = mysql_fetch_assoc($rs)) {
			// Loop though the records.
			$id = intval($row['id']);
			
			$context_workers = CerberusContexts::getWorkers(CerberusContexts::CONTEXT_TICKET, $id);
			if(($ac_only_unassigned == 1) && (count($context_workers)>0)) {
				$logger->info("[Cerb5Blog.com] Worker assigned but we are only closing tickets without a worker.");
			} else {
				$logger->info("[Cerb5Blog.com] Closing Ticket # " . $id);
				if (class_exists('DAO_TicketAuditLog',true)):
					// Code that requires time tracker to be enabled.
					$fields = array(
						DAO_TicketAuditLog::TICKET_ID => $id,
						DAO_TicketAuditLog::WORKER_ID => 0,
						DAO_TicketAuditLog::CHANGE_DATE => time(),
						DAO_TicketAuditLog::CHANGE_FIELD => "cerb5blog.auto_close.auto_closed",
						DAO_TicketAuditLog::CHANGE_VALUE => "Auto Closed",
					);
					$log_id = DAO_TicketAuditLog::create($fields);
					unset($fields);
				endif;
		
				$fields[DAO_Ticket::IS_WAITING] = 0;
				$fields[DAO_Ticket::IS_CLOSED] = 1;
				$fields[DAO_Ticket::IS_DELETED] = 0;
				//DAO_Ticket::update($id, $fields);
				unset($fields);
			}
		}
		$logger->info("[Cerb5Blog.com] Finished processing Auto Close Cron Job.");
  }
 
	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_close_days = $this->getParam('close_days', 7);
		@$ac_close_days_term = $this->getParam('close_days_term', 'd');
		$tpl->assign('ac_only_unassigned', $ac_only_unassigned);
		$tpl->assign('ac_close_days', $ac_close_days);
		$tpl->assign('ac_close_days_term', $ac_close_days_term);
 
		$tpl->display($tpl_path . 'cron.tpl');
	}
 
	function saveConfigurationAction() {
		@$ac_only_unassigned = DevblocksPlatform::importGPC($_REQUEST['ac_only_unassigned'],'integer',0);
		@$ac_close_days = DevblocksPlatform::importGPC($_REQUEST['ac_close_days'],'integer',7);
	    @$ac_close_days_term = DevblocksPlatform::importGPC($_REQUEST['ac_close_days_term'],'string','d');
		
		$this->setParam('only_unassigned', $ac_only_unassigned);
		$this->setParam('close_days', $ac_close_days);
		$this->setParam('close_days_term', $ac_close_days_term);
  }
};