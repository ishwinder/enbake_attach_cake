<?php
App::uses("S3", "EnbakeAttach.Vendor");

class S3Behavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		Configure::load("EnbakeAttach.s3");

		// Initialize behavior's default settings
		$default = array(
				'access_key'      => Configure::read('S3.access_key'),
				'secret_key'      => Configure::read('S3.secret_key'),
				'request_headers' => array(
						'Expires'       => 'Fri, 30 Oct 2030 14:19:41 GMT', //Far future date
						'Cache-control' => 'public',),
				'meta_headers'    => array(),
				'acl'             => 'public-read',
				'bucket'          => Configure::read('S3.bucket'),);

		$settings = am($default, is_array($config)? $config: array());

		$this->config[$model->alias] = $settings;
		$this->s3 = new S3($this->config[$model->alias]['access_key'],
					$this->config[$model->alias]['secret_key']);
	}

	public function beforeSave($model) {
		if ($model->data[$model->alias]['id']) {
			// Updating, delete existing files.
			$this->deleteAllFiles($model);
		}

		$cgi_data = $model->data[$model->alias]['cgi_data'];
		$file = $model->data[$model->alias]['filepath'].DS.$model->data[$model->alias]['filename'];

		// Put the object on S3
		$isUploaded = $this->s3->putObject(
				$this->s3->inputResource(fopen($cgi_data['tmp_name'], 'rb'), filesize($cgi_data['tmp_name'])),
				$this->config[$model->alias]['bucket'],
				$file,
				$this->config[$model->alias]['acl'],
				$this->config[$model->alias]['meta_headers'],
				$this->config[$model->alias]['request_headers']);

		if (!$isUploaded) {
			// Could not Upload. No point in moving ahead.
			throw new Exception("An Error Occured while uploading. We are working to resolve this issue");
		}

		$model->data[$model->alias]['uri'] = "{$this->config[$model->alias]['bucket']}/{$file}";
		$model->data[$model->alias]['storage'] = "s3";

		return true;
	}

	/*
	 * Delete all the existing files. Used mostly during an update.
	 */
	public function deleteAllFiles($model) {
		$attachment = $model->findById($model->data[$model->alias]['id']);

		// The URI contains the bucket as well.
		$fileToDelete = substr($attachment[$model->alias]['uri'],
				strpos($attachment[$model->alias]['uri'], "/") + 1);

		//delete the original file.
		$this->deleteFile($model, $fileToDelete);

		// check if exists thumbs to be deleted too. Aspects
		// are always locally stored.
		if (isset($attachment[$model->alias]['aspect_uri'])
				&& !empty($attachment[$model->alias]['aspect_uri'])) {
			$files = glob($attachment[$model->alias]['aspect_uri'].DS.'*');
			if (is_array($files)) {
				foreach ($files as $fileToDelete) {
					$this->deleteLocalFile($fileToDelete);
				}
			}
		}
	}

	public function deleteFile($model, $filename) {
		// Remove the object from S3.
		$isUploaded = $this->s3->deleteObject($this->config[$model->alias]['bucket'],
				$filename);

		return $isUploaded;
	}

	public function deleteLocalFile($filename) {
		if (file_exists($filename)) {
			return unlink($filename);
		}

		return false;
	}

	private function checkBucketExists() {
		if (!$this->s3->getBucket(Configure::read("S3.bucket"))) {
			throw new Exception("Please check the AWS configuration");
		}
	}
}