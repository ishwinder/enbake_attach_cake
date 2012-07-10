<?php
App::uses('EnbakeAttachAppController', 'EnbakeAttach.Controller');
/**
 * Attachments Controller
 *
 */
class EnbakeAttachAppController extends Controller {

	public $components = array( 'Auth');

	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
	}

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Auth->allow("*");
	}
}
