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

	private function checkBucketExists() {
		if (!$this->s3->getBucket(Configure::read("S3.bucket"))) {
			throw new Exception("Please check the AWS configuration");
		}
	}
}