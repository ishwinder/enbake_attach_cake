<?php
App::uses("Source", "EnbakeAttach.Lib");

class S3Source extends Source {
	function __construct() {
		// Possibly pass the keys while initializing the source.
		Configure::load("EnbakeAttach.s3");

		$this->s3 = new S3(Configure::read("S3.access_key"),
				Configure::read("S3.secret_key"));
	}

	function deleteAttachment($attachment) {
		// The URI contains the bucket as well.
		$fileToDelete = substr($attachment['uri'], strpos($attachment['uri'], "/") + 1);

		//Delete the original file.
		$this->deleteFile(Configure::read("S3.bucket"), $fileToDelete);
	}

	private function deleteFile($bucket, $filename) {
		// Remove the object from S3.
		$isDeleted = $this->s3->deleteObject($bucket, $filename);

		return $isDeleted;
	}

	function deleteAspects($attachment, $aspects) {
		if (isset($attachment['aspect_uri']) && !empty($attachment['aspect_uri'])) {
			$bucket = Configure::read("S3.aspect_bucket");
			$fileToDelete = substr($attachment['aspect_uri'],
					strpos($attachment['aspect_uri'], "/") + 1).DS;
			$list = $this->s3->getBucket($bucket, $fileToDelete);

			// TODO: Batching not allowed. Move over to the Amazon SDK for batching
			// support and faster execution hence.
			foreach ($list as $fileToDelete => $options) {
				// Remove the object from S3.
				$isDeleted = $this->deleteFile($bucket, $fileToDelete);
			}
		}
	}
	
}