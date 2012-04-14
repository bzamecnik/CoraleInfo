<?php

use Nette\Application\UI\Form;

class AttendancePresenter extends BasePresenter
{
	/** @persistent attendance id */
	private $id;
	/** @persistent */
	private $eventId;
	/** @persistent */
	private $memberId;

	/** @var Events */
	private $event;
	/** @var Events */
	private $events;
	/** @var Members */
	private $member;
	/** @var Members */
	private $members;
	/** @var Attendances */
	private $attendance;
	/** @var Attendances */
	private $attendances;
	
	// TODO: try to merge actionCreate() and actionEdit()
	public function actionCreate($eventId, $memberId) {
		$this->event = $this->context->createEvents()->get($eventId);
		$this->member = $this->context->createMembers()->get($memberId);
		if (!$this->event or !$this->member) {
			$this->flashMessage('Nelze nastavit účast, neboť zadaná událost nebo osoba neexistuje.', 'error');
			$this->eventId = null;
			$this->memberId = null;
			$this->redirect('default');
		}
		$attendance = $this->context->createAttendances()
			->where(array('event_id' => $eventId, 'member_id' => $memberId))
			->fetch();
		if ($attendance) {
			$this->redirect('edit', $attendance->id);
		}
		$this->eventId = $eventId;
		$this->memberId = $memberId;
	}
	
	public function actionEdit($id) {
		$this->attendance = $this->context->createAttendances()->get($id);
		if (!$this->attendance) {
			$this->flashMessage('Nelze nastavit účast, neboť zadaná událost nebo osoba neexistuje.', 'error');
			$this->id = null;
			$this->eventId = null;
			$this->memberId = null;
			$this->redirect('default');
		}
		// TODO: load those two entities only in render*()
		$this->event = $this->context->createEvents()->get($this->attendance->event_id);
		$this->member = $this->context->createMembers()->get($this->attendance->member_id);
		$this->id = $id;
		$this->eventId = $this->event->id;
		$this->memberId = $this->member->id;
		$this["attendanceForm"]->setDefaults($this->attendance);
	}

	public function renderDefault() {
		// TODO: show some default components
		// - list of current events - visible future attendable
		//	 - my attendance - unfilled, filled
		// - probably use renderEvents()
		
		$this->events = $this->context->createEvents()
			->where(array('attendance_active' => true))
			->where('date_start > ?', date("Y-m-d H:i:s"))
			->order('date_start ASC');
		$this->template->events = $this->events;
		//$this->template->attendances = $this->events
		//	->related('corale_attendance')->where('member_id', 51); // TODO fill the member_id
	}
	
	public function renderCreate() {
		$this->template->event = $this->event;
		$this->template->member = $this->member;
	}
	
	public function renderEdit() {
		$this->template->event = $this->event;
		$this->template->member = $this->member;
	}

	// show attendance for a given event
	// - list attendace for all active members and guests
	public function renderEvent($id) {
		$this->event = $this->context->createEvents()->get($id);
		$this->template->event = $this->event;

		if (($this->event === FALSE) || !$this->event->attendance_active) {
			$this->setView('notFound');
			return;
		}

		// TODO: rewrite to the NotORM notation if possible 
		$query = <<<EOQ
SELECT m.id member_id, m.first_name, m.last_name, a.id attendance_id, a.attend, a.note attend_note
FROM corale_member m
LEFT JOIN corale_attendance a
ON m.id = a.member_id AND a.event_id = ?
LEFT JOIN corale_event e ON e.id = a.event_id 
WHERE m.active = 1
ORDER BY m.last_name, m.first_name
EOQ;
		$this->members = $this->context->createMembers()->getConnection()->query($query, $id);
		$this->template->members = $this->members;
		
		$query = <<<EOQ
SELECT a.attend as attend, count(attend) as cnt
FROM corale_member m
JOIN corale_attendance a ON m.id = a.member_id AND a.event_id = ?
JOIN corale_event e ON e.id = a.event_id
WHERE m.active = 1
GROUP BY a.attend;
EOQ;
		$attendanceCounts = $this->context->createMembers()->getConnection()->query($query, $id)->fetchPairs('attend', 'cnt');
		
		if (!isset($attendanceCounts['0'])) {
			$attendanceCounts['0'] = 0;
		}
		if (!$attendanceCounts['1']) {
			$attendanceCounts['1'] = 0;
		}
		$total = $this->context->createMembers()->where('active', true)->count();
		$this->template->attendanceCounts = array(
			'yes' => $attendanceCounts['1'],
			'no' => $attendanceCounts['0'],
			'other' => $total - ($attendanceCounts['1'] + $attendanceCounts['0']),
			'total' => $total,
			);
	}

	public function renderEvents() {
		// TODO: show list of attendable events
		// - show only future attendable events
		// - filter by filled attendance by given user - yes/no/null
		// - group/filter by years (not only future events)
	}

	public function renderMatrix($eventIds) {
		// TODO: show an event-user attendance matrix
		// - columns = events
		// - rows = users
		// - $eventIds - a list of events to show
		//	 - NULL = show all future attendable events
	}
	
	protected function createComponentAttendanceForm()
	{
		$form = new Form();
		$form->addSelect('attend', 'Účast',
			array('1' => 'Účastním se', '0' => 'Neúčastním se'))
			->setPrompt('- Vyberte -');
		$form->addTextArea('note', 'Poznámka');
		
		$form->addSubmit('edit', 'Nastavit');
		$form->onSuccess[] = callback($this, 'processAttendanceForm');
		
		$presenter = $this;
		$form->addSubmit('cancel', 'Zrušit')
			->setValidationScope(FALSE)
			->onClick[] = function () use ($presenter) {
				// TODO: redirect back
				$presenter->redirect('default');
			};
		return $form;
	}
	
	public function createComponentAttendanceButton()
	{
		return new AttendanceButton();
	}

	
	public function processAttendanceForm(Form $form)
	{
		if ($this->id AND !$this->attendance) {
			// record existence check in case of editing
			throw new BadRequestException;
		}
		$values = $form->values;
		$values['event_id'] = $this->eventId;
		$values['member_id'] = $this->memberId;
		if ($this->id) {
			$query = $this->context->createAttendances()
				->where(array('id' => $this->id));
			if (!empty($values['attend']) || !empty($values['note'])) {
				$query->update($values);
			} else {
				$query->delete($values);
			}
		} else {
			$this->context->createAttendances()->insert($values);
		}
		$this->flashMessage('Účast byla nastavena.', 'success');
		$this->redirect('event', array('id' => $this->event->id));
	}
}
?>