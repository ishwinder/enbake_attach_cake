<?php
App::uses('EnbakeAttachAppController', 'EnbakeAttach.Controller');
App::uses('Attach', 'EnbakeAttach.Lib');
/**
 * Attachments Controller
 *
 */
class AttachmentsController extends EnbakeAttachAppController {
	public $uses = array('EnbakeAttach.Attachment');

/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;

	// Get the thumbnail of a given attachment
	function view($id = null) {
		App::uses('AttachmentHelper', 'View/Helper');

		$this->viewClass = 'Media';

		$this->Attachment->id = $id;
		if (!$this->Attachment->exists()) {
			throw new NotFoundException(__('Invalid Attachment'));
		}

		$attachment = $this->Attachment->read(null, $id);

		$path = Attach::getFullFilePath($attachment);

		// Render the file.
		$params = array(
			'id' => $attachment['Attachment']['filename'],
			'name' => basename($attachment['Attachment']['filename']),
			'extension' => pathinfo($attachment['Attachment']['filename'], PATHINFO_EXTENSION),
			'path' => pathinfo($path, PATHINFO_DIRNAME).DS);

		$this->set($params);
	}

	// Get the aspect of a given attachment
	function aspect($id, $aspect) {
		App::uses('AttachmentHelper', 'View/Helper');

		$this->viewClass = 'Media';

		$this->Attachment->id = $id;
		if (!$this->Attachment->exists()) {
			throw new NotFoundException(__('Invalid Attachment'));
		}

		$attachment = $this->Attachment->read(null, $id);

		$path = Attach::getFullAspectPath($attachment, $aspect);
		$name = Attach::getAspectName($attachment, $aspect);

		// Render the file.
		$params = array(
			'id' => $name,
			'name' => $name,
			'extension' => pathinfo($attachment['Attachment']['filename'], PATHINFO_EXTENSION),
			'path' => $path.DS);

		$this->set($params);
	}

}
