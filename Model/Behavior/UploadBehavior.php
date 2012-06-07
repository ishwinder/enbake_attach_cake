<?php
App::uses('Attachment', 'Attach.Model');

class UploadBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$this->config[$model->alias] = $config;
		$this->types[$model->alias] = array_keys($this->config[$model->alias]);

		foreach ($this->types[$model->alias] as $index => $type) {
			$this->setRelation($model, $this->types[$model->alias][$index]);
		}
	}

	public function setRelation(Model $model, $type) {
		$type = Inflector::camelize($type);
		$relation = 'hasOne';

		//case is defined multiple is a hasMany
		if (isset($this->config[$model->alias][$type]['multiple'])
				&& $this->config[$model->alias][$type]['multiple'] == true) {
			$relation = 'hasMany';
		}

		$relationName = 'Attachment'.$type;

		$model->{$relation}[$relationName] = array(
			'className' => 'Attachment',
			'foreignKey' => 'foreign_key',
			'dependent' => true,
			'conditions' => array(
				'Attachment' . $type . '.model' => $model->alias,
				'Attachment' . $type . '.type' => strtolower($type)),
			'fields' => '',
			'order' => ''
		);

		$actAs = isset($this->config[$model->alias][$type]['storage']) ?
				$this->config[$model->alias][$type]['storage'] : "EnbakeAttach.File";

		$model->{$relationName}->Behaviors->load("{$actAs}", $this->config[$model->alias][$type]);

		 if (isset($this->config[$model->alias][$type]['aspects'])) {
		 	$actAs = isset($this->config[$model->alias][$type]['processor']) ?
				$this->config[$model->alias][$type]['processor'] : "EnbakeAttach.Imagine";

			$model->{$relationName}->Behaviors->load("{$actAs}", $this->config[$model->alias][$type]);
		} 
	}

	/**
	 * Check if the file extension it's correct
	 *
	 * @param array $check Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access public
	 */
	public function extension($model, $check, $extensions) {
		$check = array_shift($check);
		if (isset($check['name'])) {
			return in_array($this->getFileExtension($check['name']), $extensions);
		}

		return false;
	}

	/**
	 * Check if the mime type it's correct
	 *
	 * @param array $check Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access public
	 */
	public function mime($model, $check, $mimes) {
		$check = array_shift($check);

		if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
			$info = $this->getFileMime($model, $check['tmp_name']);

			return in_array($info, $mimes);
		}

		return false;
	}

	public function size($model, $check, $size) {
		$check = array_shift($check);

		return $size >= $check['size'];
	}

    /**
     * Check if the image fits within given dimensions
     *
     * @param array $check Array of data from the file that is been checked
     * @param int $width Maximum width in pixels
     * @param int $height Maximum height in pixels
     * @return bool Return true if image fits withing given dimensions
     * @access public
     */
	public function maxDimensions($model, $check, $width, $height) {
		$check = array_shift($check);

		if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
			$info = getimagesize($check['tmp_name']);

			return ($info && $info[0] <= $width && $info[1] <= $height);
		}

		return false;
	}

	public function getFileMime($model, $file) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$info = finfo_file($finfo, $file);

		return $info;
	}

	/**
	 * Check if the mime type it's correct
	 *
	 * @param array $value Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access protected
	 */
	public function getFileExtension($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	public function afterSave(Model $model, $created) {
		foreach ($this->types[$model->alias] as $type) {
			//set multiple as false by standard
			$multiple = false;

			if (isset($this->config[$model->alias][$type]['multiple'])
				&& $this->config[$model->alias][$type]['multiple'] === true) {
				$multiple = true;
				$check = is_array($model->data[$model->alias][$type]);
			} else {
				$check = isset($model->data[$model->alias][$type]['tmp_name'])
					&& !empty($model->data[$model->alias][$type]['tmp_name']);
			}

			//case has the file update :)
			if ($check) {
				if ($multiple) {
					foreach ($model->data[$model->alias][$type] as $index => $value) {
						$this->saveFile($model, $type, $index);
					}
				} else {
					$this->saveFile($model, $type);
				}
			}
		}
	}

	public function beforeDelete($model, $cascade = true) {
		if ($cascade = true) {
			foreach ($this->types[$model->alias] as $type) {
				$className = 'Attachment'. Inflector::camelize($type);

				$attachments = $model->{$className}->find('all', array(
					'conditions' => array(
						'model' => $model->name,
						'foreign_key' => $model->id,
					),
				));

				foreach ($attachments as $attach) {
					$this->deleteAllFiles($model, $attach);
				}
			}
		}

		return $cascade;
	}

	public function saveFile(Model $model, $type, $index = null) {
		$uploadData = $model->data[$model->alias][$type];

		if (!is_null($index)) {
			$uploadData = $uploadData[$index];
		}

		if (is_uploaded_file($uploadData['tmp_name'])) {
			$file = $this->generateName($model, $type, $index);
			$attach = $this->_saveAttachment($model, $type, $file, $uploadData);
	
			@unlink($uploadData['tmp_name']);
		}
	}

	protected function _saveAttachment(Model $model, $type, $filename, $uploadData) {
		$className = 'Attachment'. Inflector::camelize($type);

		/* $attachment = $model->{$className}->find('first', array(
			'conditions' => array(
				'foreign_key' => $model->id,
				'model' => $model->alias,
				'type' => $type,
				'filename' => basename($filename),
			),
		));*/

		$data = array(
			$className => array(
				'model' => $model->alias,
				'foreign_key' => $model->id,
				'filename' => basename($filename),
				'filepath' => dirname($filename),
				'type' => $type,
				'mime_type' => $this->getFileMime($model, $uploadData['tmp_name']),
				'cgi_data' => $uploadData,
			),
		);

		//if ($attachment) {
		if (isset($model->data[$model->alias][$type]['id'])) {
			// $this->deleteAllFiles($model, $attachment);
			// $data[$className]['id'] = $attachment[$className]['id'];
			$data[$className]['id'] = $model->data[$model->alias][$type]['id'];
		} else {
			$model->{$className}->create();
		}

		$model->data += $model->{$className}->save($data);
	}

	public function generateName(Model $model, $type, $index = null) {
		if (is_null($index)) {
			$extension = $this->getFileExtension($model->data[$model->alias][$type]['name']);
		} else {
			$extension = $this->getFileExtension($model->data[$model->alias][$type][$index]['name']);
		}

		return date('Y').DS.date('m').DS.date('d').DS.String::uuid().'.'.$extension;
	}
}
?>
