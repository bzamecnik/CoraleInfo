<?php
use Nette\Application\UI,
    Nette\Database\Table\Selection;

use Nette\Diagnostics\Debugger;

class AttendanceButton extends UI\Control
{
	public function __construct()
	{
		parent::__construct();
	}

	public function render($eventId, $memberId)
	{
		$attendance = $this->presenter->context->createAttendances()
			->where(array('event_id' => $eventId, 'member_id' => $memberId))->fetch();
	
		$this->template->setFile(__DIR__ . '/AttendanceButton.latte');
		$this->template->attendance = $attendance;
		$this->template->eventId = $eventId;
		$this->template->memberId = $memberId;
		$attend = ($attendance !== FALSE) ? $attendance->attend : null;
		$this->template->attend = $attend;
		$this->template->attendText = ($attend !== NULL) ? ($attend ? 'yes' : 'no') : 'null';
		
		$this->template->attendButtonTexts = array('yes'=> 'Ano', 'no'=> 'Ne', 'null'=> '');
		$this->template->attendActionCaptions = array('yes'=> 'Účastním se', 'no'=> 'Neúčastním se', 'null'=> 'Nevyplněno');
		$this->template->attendButtonStyles = array('yes'=> 'btn-success', 'no'=> 'btn-danger', 'null'=> '');
		$this->template->attendIcons = array('yes'=> 'icon-ok', 'no'=> 'icon-remove', 'null'=> 'icon-question-sign');
		
		$this->template->render();
	}
	
	public function handleSetAttendance($eventId, $memberId, $attend = NULL) {
		$values = array('attend' => $attend);
		Debugger::log("eventId: $eventId, memberId: $memberId", Debugger::INFO);
		$key = array('event_id' => $eventId, 'member_id' => $memberId);
		$attendance = $this->presenter->context->createAttendances()->where($key)->fetch();
		if ($attendance) {
			$query = $this->presenter->context->createAttendances()->where($key)->update($values);
		} else {
			$values['event_id'] = $eventId;
			$values['member_id'] = $memberId;
			$this->presenter->context->createAttendances()->insert($values);
		}
		if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
		} else {
				$this->presenter->invalidateControl();
		}
	}
}
?>