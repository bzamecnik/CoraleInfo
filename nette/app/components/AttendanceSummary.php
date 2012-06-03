<?php
use Nette\Application\UI,
    Nette\Database\Table\Selection;

class AttendanceSummary extends UI\Control
{
	public function __construct()
	{
		parent::__construct();
	}

	public function render($eventId)
	{
		$this->template->setFile(__DIR__ . '/AttendanceSummary.latte');
		
		$query = <<<EOQ
SELECT a.attend as attend, count(attend) as cnt
FROM corale_member m
JOIN corale_attendance a ON m.id = a.member_id AND a.event_id = ?
JOIN corale_event e ON e.id = a.event_id
WHERE m.active = 1
GROUP BY a.attend;
EOQ;
		$attendanceCounts = $this->presenter->context
			->createMembers()->getConnection()
			->query($query, $eventId)->fetchPairs('attend', 'cnt');
		
		if (!isset($attendanceCounts['0'])) {
			$attendanceCounts['0'] = 0;
		}
		if (!isset($attendanceCounts['1'])) {
			$attendanceCounts['1'] = 0;
		}
		$total = $this->presenter->context->createMembers()
			->where('active', true)->count();
		$this->template->attendanceCounts = array(
			'yes' => $attendanceCounts['1'],
			'no' => $attendanceCounts['0'],
			'other' => $total - ($attendanceCounts['1'] + $attendanceCounts['0']),
			'total' => $total,
			);
		
		$this->template->render();
	}
}