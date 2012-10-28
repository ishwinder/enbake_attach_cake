<?php
App::uses('Folder', 'Utility');

class FileBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		// Initialize behavior's default settings
		$default = array('dir' => 'webroot');

		$settings = am($default, is_array($config)? $config: array());
		$this->config[$model->alias] = $settings;
	}

	public function beforeSave($model, $created) {
		// Set afresh.
		$cgi_data = $model->data[$model->alias]['cgi_data'];
		$upload_folder = $this->getUploadFolder($model, $model->data[$model->alias]['filepath']);
		$file = $upload_folder.$model->data[$model->alias]['filename'];
		$folder = new Folder($upload_folder, true, 0744);

		//move file
		copy($cgi_data['tmp_name'], $file);

		// set the URI.
		$model->data[$model->alias]['uri'] = "{$file}";
		$model->data[$model->alias]['storage'] = "file";

		return true;
	}

	/**
	 * Return the upload folder that was set
	 *
	 * @return string Path for the upload folder
	 * @access public
	 */
	public function getUploadFolder($model, $dir) {
		return APP . str_replace('{DS}', DS, $this->config[$model->alias]['dir'].DS.$dir) . DS;
	}

	public function isWritable($dir) {
		if (is_dir($dir) && is_writable($dir)) {
			return true;
		}

		throw new Exception('Folder is not writable: ' .  $dir);
	}
}