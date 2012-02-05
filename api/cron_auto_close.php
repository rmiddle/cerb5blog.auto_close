<?php
class Cerb5BlogAutoCloseCron extends CerberusCronPageExtension {
    const EXTENSION_ID = 'cerb5blog.auto_close.cron';

	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$db = DevblocksPlatform::getDatabaseService();
        $translate = DevblocksPlatform::getTranslationService();
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Cerb5Blog.com] Running Auto Close Cron Task.");

		@ini_set('memory_limit','128M');

		@$ac_only_unassigned = $this->getParam('only_unassigned', 0);
		@$ac_open_or_close = $this->getParam('open_or_close', 1);
		@$ac_close_days = $this->getParam('close_days', 7);
		@$ac_close_days_term = $this->getParam('close_days_term', 'd');
		$close_time = time();
		$close_time -= CerberusCronPageExtension::getIntervalAsSeconds($ac_close_days, $ac_close_days_term);
		
		$sql = "SELECT t.id ";
		$sql .= "FROM ticket t ";
		$sql .= sprintf("WHERE t.updated_date < %d  ", $close_time);
		$sql .= "AND t.is_waiting = 1 ";
		$sql .= "AND t.is_closed = 0 ";
		$sql .= "GROUP BY t.id ";
		$sql .= "ORDER BY t.id ";
		$logger->info("[Cerb5Blog.com] SQL = " . $sql);
		
		$rs = $db->Execute($sql);
		while($row = mysql_fetch_assoc($rs)) {
			// Loop though the records.
			$id = intval($row['id']);
			
            $context_workers = CerberusContexts::getWatchers(CerberusContexts::CONTEXT_TICKET, $id);
			if(($ac_only_unassigned == 1) && (count($context_workers)>0)) {
				$logger->info("[Cerb5Blog.com] Worker assigned but we are only closing tickets without a worker.");
			} else {
                if($ac_open_or_close) {
                    $logger->info("[Cerb5Blog.com] " . $translate->_('cerb5blog.auto_close.cron.close') . " " . $id);
                    $close_message = $translate->_('cerb5blog.auto_close.cron.auto_close');
                    $status_to = "Closed";
                    $activity_point = 'ticket.status.closed';	
                } else {
                    $logger->info("[Cerb5Blog.com] " . $translate->_('cerb5blog.auto_close.cron.open') . " " . $id);
                    $close_message = $translate->_('cerb5blog.auto_close.cron.auto_open');
                    $status_to = "Open";
                    $activity_point = 'ticket.status.open';	
                }
				$entry = array(
					//{{actor}} changed ticket {{target}} to status {{status}}
					'message' => 'activities.ticket.status',
					'variables' => array(
						'target' => sprintf("[%s] %s", $ticket->mask, $ticket->subject),
                        'actor' => '(auto)',
                        'status' => $status_to,
					),
					'urls' => array(
                        'target' => ('c=display&mask=' . $ticket->mask),
					)
				);
                if ($worker->id) {
                    $worker_id = $worker->id;
                } else {
                    $worker_id = 0;
                }
                DAO_ContextActivityLog::create(array(
                    DAO_ContextActivityLog::ACTIVITY_POINT => $activity_point,
                    DAO_ContextActivityLog::CREATED => time(),
                    DAO_ContextActivityLog::ACTOR_CONTEXT => '',
                    DAO_ContextActivityLog::ACTOR_CONTEXT_ID => 0,
                    DAO_ContextActivityLog::TARGET_CONTEXT => 'cerberusweb.contexts.ticket',
                    DAO_ContextActivityLog::TARGET_CONTEXT_ID => $ticket_id,
                    DAO_ContextActivityLog::ENTRY_JSON => json_encode($entry),
                ));
                if($ac_open_or_close) {
                    $fields[DAO_Ticket::IS_CLOSED] = 1;
                } else {
                    $fields[DAO_Ticket::IS_CLOSED] = 0;
                }	
                $fields[DAO_Ticket::IS_WAITING] = 0;
                $fields[DAO_Ticket::IS_DELETED] = 0;
                DAO_Ticket::update($id, $fields);
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
		@$ac_open_or_close = $this->getParam('open_or_close', 1);
		@$ac_close_days = $this->getParam('close_days', 7);
		@$ac_close_days_term = $this->getParam('close_days_term', 'd');
		$tpl->assign('ac_only_unassigned', $ac_only_unassigned);
		$tpl->assign('ac_open_or_close', $ac_open_or_close);
		$tpl->assign('ac_close_days', $ac_close_days);
		$tpl->assign('ac_close_days_term', $ac_close_days_term);
 
		$tpl->display($tpl_path . 'cron.tpl');
	}
 
	function saveConfigurationAction() {
		@$ac_only_unassigned = DevblocksPlatform::importGPC($_REQUEST['ac_only_unassigned'],'integer',0);
		@$ac_open_or_close = DevblocksPlatform::importGPC($_REQUEST['ac_open_or_close'],'integer',1);
		@$ac_close_days = DevblocksPlatform::importGPC($_REQUEST['ac_close_days'],'integer',7);
	    @$ac_close_days_term = DevblocksPlatform::importGPC($_REQUEST['ac_close_days_term'],'string','d');
		
		$this->setParam('only_unassigned', $ac_only_unassigned);
		$this->setParam('open_or_close', $ac_open_or_close);
		$this->setParam('close_days', $ac_close_days);
		$this->setParam('close_days_term', $ac_close_days_term);
  }
};
