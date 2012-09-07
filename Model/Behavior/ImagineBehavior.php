<?php
App::uses('Folder', 'Utility');

class ImagineBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$this->config[$model->alias] = $config;
	}

	public function afterSave($model, $created) {
		$file = $model->data[$model->alias]['cgi_data']['tmp_name'];

		$imagine = $this->getImagine();
		$image = $imagine->open($file);

		foreach ($this->config[$model->alias]['aspects'] as $key => $values) {
			$folder = new Folder($this->__getAspectPath($model, $key), true, 0744);
			$aspect_full_path = $this->__getAspectFullPath($model, $key);

			$this->__generateAspect(array(
				'name' => $aspect_full_path,
				'w' => $values['w'],
				'h' => $values['h'],
			), $image, $values['crop']);
		}

		// Save the aspect paths to render from.
		$path = $this->__getAspectPath($model);

		$model->save(array("aspect_storage"=>"file", 'aspect_uri'=>$path),
				array("validate"=>false, "callbacks"=>false));
	}

	private function getImagine() {
		if (!interface_exists('Imagine\Image\ImageInterface')) {
			if (is_file(VENDORS . "Imagine".DS."imagine.phar")) {
				require_once 'phar://' . VENDORS . 'Imagine'.DS.'imagine.phar';
			} else {
				throw new CakeException(sprintf('You should add in your vendors folder %s, the imagine.phar,
				you can download here: https://github.com/avalanche123/Imagine', VENDORS));
			}
		}

		return new \Imagine\Gd\Imagine();
	}

	/*
	 * __getAspectFullPath
	 * 
	 * Gets the full path for the aspect including filename.
	 */
	private function __getAspectFullPath($model, $aspect) {
		$filename = pathinfo($model->data[$model->alias]['filename'], PATHINFO_FILENAME);
		$aspect_name = $filename."_".$aspect.".".pathinfo($model->data[$model->alias]['filename'], PATHINFO_EXTENSION);

		return $this->__getAspectPath($model).DS.$aspect_name;
	}

	/*
	 * __getAspectPath
	 * 
	 * Gets the path for the aspect sans filename.
	 */
	private function __getAspectPath($model) {
		return APP.'webroot'.DS.'aspects'.DS.$model->data[$model->alias]['id'];
	}

	/*
	 * __generateAspect
	 * 
	 * Generate the given aspect from the image.
	 */
	private function __generateAspect($aspect, $image, $crop = false) {
		if ($crop == true) {
			$mode =  Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
		} else {
			$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
		}

		$thumbnail = $image->thumbnail(new Imagine\Image\Box($aspect['w'], $aspect['h']), $mode);
		$thumbnail->save($aspect['name']);
	}
}