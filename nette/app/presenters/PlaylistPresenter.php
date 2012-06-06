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
	
	public function actionUpdate($eventId) {
		$this->ensureLoggedUser();
		
		$postParams = $this->request->getPost();
		if (!array_key_exists('songIds', $postParams)) {
			// TODO
			return;
		}
		$songIds = explode(',', $postParams['songIds']);
		
		Nette\Diagnostics\Debugger::dump($songIds);
		
		$newSongs = array();
		$songOrders = array();
		for ($i = 0; $i < count($songIds); $i++) {
			$songId = intval($songIds[$i]);
			if ($songId > 0) {
				$newSongs[] = $songId;
				$songOrders[$songId] = "$i";
			}
		}
		
		Nette\Diagnostics\Debugger::dump("newSongs:");
		Nette\Diagnostics\Debugger::dump($newSongs);
		Nette\Diagnostics\Debugger::dump("songOrders:");
		Nette\Diagnostics\Debugger::dump($songOrders);
		
		// TODO: synchronize the existing and the new playlist
		// add/delete/update the items only when there is a change
		
		$existingSongsSel = $this->context->createPlaylistItems()->where(array('event_id' => $eventId));
		
		$existingSongs = array();
		$existingSongOrders = array();
		foreach ($existingSongsSel as $song) {
			$songId = $song->song_id;
			$existingSongs[] = $songId;
			$existingSongOrders[$songId] = $song->ord;
		}
		
		// make a copy to be further filtered
		$songsToDelete = $existingSongs;
		
		Nette\Diagnostics\Debugger::dump("existingSongs:");
		Nette\Diagnostics\Debugger::dump($existingSongs);
		Nette\Diagnostics\Debugger::dump("existingSongOrders:");
		Nette\Diagnostics\Debugger::dump($existingSongOrders);
		
		
		foreach ($newSongs as $song) {
			// TODO: this smells with quardratic complexity
			$key = array_search($song, $existingSongs);
			$found = ($key !== FALSE);
			
			$newOrder = $songOrders[$song];
			if ($found) {
				$existingOrder = $existingSongOrders[$existingSongs[$key]];
				if ($newOrder != $existingOrder) {
					echo "updating song id $song order '$existingOrder' -> '$newOrder'<br>";
					$this->context->createPlaylistItems()
						->where(array('event_id' => $eventId, 'song_id' => $song))
						->update(array('ord' => $newOrder));
				}
				unset($songsToDelete[$key]);
			} else {
				echo "inserting song id $song with order $newOrder<br>";
				$this->context->createPlaylistItems()->insert(
					array('event_id' => $eventId, 'song_id' => $song, 'ord' => $newOrder)
				);
			}
		}
		Nette\Diagnostics\Debugger::dump("songsToDelete:");
		Nette\Diagnostics\Debugger::dump($songsToDelete);
		foreach ($songsToDelete as $song) {
			echo "deleting song id $song<br>";
			$this->context->createPlaylistItems()
				->where(array('event_id' => $eventId, 'song_id' => $song))
				->delete();
		}
		
		$this->flashMessage("Playlist byl upraven.");
		//$this->redirect('edit', array('eventId' => $eventId));
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
	
	public function actionEdit($eventId, $query) {
		$this->ensureLoggedUser();
	
		// TODO: show not found page on non-existent event
	
		$this->eventId = $eventId;
		$this->songQuery = $query;
		$this["songSearchForm"]->setDefaults(array('query' => $this->songQuery));
	}
	
	public function renderDefault() {
		$this->template->eventId = $this->eventId;
		$this->template->playlist = $this->getPlaylist($this->eventId);
	}
	
	public function renderEdit() {
		$this->template->eventId = $this->eventId;
		$this->template->playlist = $this->getPlaylist($this->eventId);
		
		$q = $this->context->createSongs();
		if ($this->songQuery) {
			$term = "%$this->songQuery%";
			$q->where("title LIKE ? OR author LIKE ? OR description LIKE ?",
				array($term, $term, $term));
		}
		$q->order('title');
		$this->template->repertoire = $q;
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
	
	protected function createComponentSongSearchForm()
	{
		$form = new Form();
		$form->addText('query', 'Hledaný text');
		$form->addSubmit('search', 'Hledat');
		return $form;
	}
	
	protected function createComponentPlaylistEditor() {
		return new PlaylistEditor();
	}
}
