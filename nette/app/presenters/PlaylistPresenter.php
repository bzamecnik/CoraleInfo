<?php

use Nette\Application\UI\Form;

class PlaylistPresenter extends BasePresenter
{
	/** @persistent */
	private $eventId;
	
	/** @var Songs */
	private $songQuery;
	
	public function actionUpdateItem($eventId, $songId, $order) {
		$this->ensureLoggedUser();
		
		$this->eventId = $eventId;
		$values = array();
		if ($order) {
			$values['ord'] = $order;
		}
		$key = array('event_id' => $eventId, 'song_id' => $songId);
		$playlistItem = $this->context->createPlaylistItems()->where($key)->fetch();
		if ($playlistItem) {
			$this->context->createPlaylistItems()->where($key)->update($values);
			$this->flashMessage("Písnička v playlistu byla upravena.");
		} else {
			$values['event_id'] = $eventId;
			$values['song_id'] = $songId;
			$this->context->createPlaylistItems()->insert($values);
			$this->flashMessage("Písnička byla přidána do playlistu.");
		}
		$this->redirect('edit', array('eventId' => $eventId));
	}
	
	public function actionDelete($eventId, $songId) {
		$this->ensureLoggedUser();
		
		$key = array('event_id' => $eventId, 'song_id' => $songId);
		$this->context->createPlaylistItems()->where($key)->delete();
		// TODO: hande the situation where the playlist item is badly specified
		// or does not exist
		$this->flashMessage("Písnička byla smazána z playlistu.");
		$this->redirect('edit', array('eventId' => $eventId));
	}
	
	public function actionDefault($eventId) {
		$this->eventId = $eventId;
	}
	
	public function actionEdit($eventId) {
		$this->eventId = $eventId;
		
		$this->ensureLoggedUser();
	}
	
	public function renderDefault() {
		$this->template->eventId = $this->eventId;
		$this->template->playlist = $this->getPlaylist($this->eventId);
	}
	
	public function renderEdit() {
		$this->template->eventId = $this->eventId;
		$this->template->playlist = $this->getPlaylist($this->eventId);
	}
	
	public function getPlaylist($eventId) {
		$query = <<<EOQ
SELECT s.id id, s.title title, p.ord `order`
FROM corale_song s
JOIN corale_playlist_item p ON s.id = p.song_id
JOIN corale_event e ON e.id = p.event_id
WHERE p.event_id = ?
ORDER BY p.ord, s.title
EOQ;
			return $this->context->createPlaylistItems()->getConnection()->query($query, $eventId);
	}

	// TODO: use AJAX
	public function actionAdd($eventId, $query)
	{
		$this->eventId = $eventId;
		$this->songQuery = $query;
		$this["songSearchForm"]->setDefaults(array('query' => $this->songQuery));
	}
	
	public function renderAdd() {
		$q = $this->context->createSongs();
		if ($this->songQuery) {
			$term = "%$this->songQuery%";
			$q->where("title LIKE ? OR author LIKE ? OR description LIKE ?",
				array($term, $term, $term));
		}
		$q->order('title');
		$this->template->songs = $q;
		$this->template->eventId = $this->eventId;
	}
	
	protected function createComponentSongSearchForm()
	{
		$form = new Form();
		$form->addText('query', 'Hledaný text');
		$form->addSubmit('search', 'Hledat');
		return $form;
	}
}
