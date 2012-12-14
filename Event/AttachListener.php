<?php
App::uses('CakeEventListener', 'Event');
App::uses("S3", "EnbakeAttach.Vendor");

class AttachListener implements CakeEventListener {

	function __construct() {
		Configure::load("EnbakeAttach.s3");
	}

	public function implementedEvents() {
		return array('Model.Attachment.aspectCreated' => 'transferAspects');
	}

	/*
	 * The listener is used to transfer aspects to their destination.
	 * 
	 * Supports the transfer to S3 only for now.
	 */
	public function transferAspects($event) {
		$this->s3 = new S3(Configure::read('S3.access_key'), Configure::read('S3.secret_key'));

		// Delete existing files if update is happening.
		$attachment = ClassRegistry::init("EnbakeAttach.Attachment")->findById($event->data['model_id']);
		$alias = $event->data['alias'];
		$config = $event->data['config'];

		// If Aspect storage is set to S3. Transfer the aspects.
		if (!empty($attachment) && isset($config[$alias]['aspect_storage'])
				&& $config[$alias]['aspect_storage'] == "EnbakeAttach.S3") {
			$isUploaded = true;
			$filename = pathinfo($attachment["Attachment"]['filename'], PATHINFO_FILENAME);
			$ext = pathinfo($attachment["Attachment"]['filename'], PATHINFO_EXTENSION);
			$filepath = date('Y').DS.date('m').DS.date('d').DS.$attachment['Attachment']['id'];
			$aspect_bucket = Configure::read("S3.aspect_bucket");

			foreach($config[$alias]['aspects'] as $aspect=>$options) {
				$localPath = $attachment["Attachment"]['aspect_uri'].DS.$filename."_{$aspect}".".".$ext;
				$s3Path = $filepath.DS.$filename."_{$aspect}".".".$ext;

				// Put the object on S3
				$isUploaded = $this->s3->putObject(
						$this->s3->inputResource(fopen($localPath, 'rb'), filesize($localPath)),
						$aspect_bucket,
						$s3Path,
						'public-read',
						array(),
						array('Expires' => 'Fri, 30 Oct 2030 14:19:41 GMT', //Far future date
						'Cache-control' => 'public',));

				if (!$isUploaded) {
					// Could not Upload. No point in moving ahead.
					break;
				}
				$uploaded[] = $localPath;
			}

			// Remove the aspects folder.
			if ($isUploaded) {
				$folder = new Folder($attachment["Attachment"]['aspect_uri']);
				$deleted = $folder->delete();
				$attach = array('id'=>$attachment['Attachment']['id'],
									'aspect_uri'=>$aspect_bucket.DS.$filepath,
									'aspect_storage'=>'s3');
				ClassRegistry::init("EnbakeAttach.Attachment")->save($attach);
			}
		}

		return $isUploaded;
	}
}