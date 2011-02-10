<?php

class Cerb5BlogAutoCloseCron extends CerberusCronPageExtension {
  const EXTENSION_ID = 'cerb5blog.auto_close.cron';

	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Cerb5Blog.com] Running Auto Close Cron Task.");

		@ini_set('memory_limit','128M');

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_close_days = $this->getParam('close_days', 7);
		
		$sql = "SELECT t.id, ";
		$sql .= "t.subject, t.created_date, t.updated_date, t.is_closed, ";
		$sql .= "t.is_waiting, t.team_id, t.category_id ";
		$sql .= "FROM ticket t ";
		$sql .= sprintf("WHERE t.updated_date > %d  ", strtotime("+".$ac_close_days." days"));
		$sql .= "AND t.is_waiting = 1 ";
		$sql .= "GROUP BY t.id ";
		$sql .= "ORDER BY t.id ";
		
		$rs = $db->Execute($sql) or $db->ErrorMsg(); 
		while($row = mysql_fetch_assoc($rs)) {
			// Loop though the records.
			$id = intval($row['id']);
			
			$context_workers = CerberusContexts::getWorkers(CerberusContexts::CONTEXT_TICKET, $id);
			if(($ac_only_unassigned == 1) && (count($context_workers)>0)) {
				// Do something.
			} else {
				$logger->info("[Cerb5Blog.com] Closing Ticket # " . $id);
				if (DevblocksPlatform::isPluginEnabled('cerberusweb.auditlog')) {
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
				}
		
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