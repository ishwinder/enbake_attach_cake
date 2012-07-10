<?php
App::uses('Attachment', 'Attach.Model');
App::uses('HttpSocket', 'Network/Http');

$tmp_file_path = "/tmp/sh";

class UploadBehavior extends ModelBehavior {
/**
 * Holds known mime type mappings. Copied from CakeResponse.php. Change it if
 * some of the mimetypes are not listed.
 *
 * @var array
 */
	protected $_mimeTypes = array(
		'ai' => 'application/postscript',
		'bcpio' => 'application/x-bcpio',
		'bin' => 'application/octet-stream',
		'ccad' => 'application/clariscad',
		'cdf' => 'application/x-netcdf',
		'class' => 'application/octet-stream',
		'cpio' => 'application/x-cpio',
		'cpt' => 'application/mac-compactpro',
		'csh' => 'application/x-csh',
		'csv' => array('text/csv', 'application/vnd.ms-excel', 'text/plain'),
		'dcr' => 'application/x-director',
		'dir' => 'application/x-director',
		'dms' => 'application/octet-stream',
		'doc' => 'application/msword',
		'drw' => 'application/drafting',
		'dvi' => 'application/x-dvi',
		'dwg' => 'application/acad',
		'dxf' => 'application/dxf',
		'dxr' => 'application/x-director',
		'eot' => 'application/vnd.ms-fontobject',
		'eps' => 'application/postscript',
		'exe' => 'application/octet-stream',
		'ez' => 'application/andrew-inset',
		'flv' => 'video/x-flv',
		'gtar' => 'application/x-gtar',
		'gz' => 'application/x-gzip',
		'bz2' => 'application/x-bzip',
		'7z' => 'application/x-7z-compressed',
		'hdf' => 'application/x-hdf',
		'hqx' => 'application/mac-binhex40',
		'ico' => 'image/vnd.microsoft.icon',
		'ips' => 'application/x-ipscript',
		'ipx' => 'application/x-ipix',
		'js' => 'text/javascript',
		'latex' => 'application/x-latex',
		'lha' => 'application/octet-stream',
		'lsp' => 'application/x-lisp',
		'lzh' => 'application/octet-stream',
		'man' => 'application/x-troff-man',
		'me' => 'application/x-troff-me',
		'mif' => 'application/vnd.mif',
		'ms' => 'application/x-troff-ms',
		'nc' => 'application/x-netcdf',
		'oda' => 'application/oda',
		'otf' => 'font/otf',
		'pdf' => 'application/pdf',
		'pgn' => 'application/x-chess-pgn',
		'pot' => 'application/mspowerpoint',
		'pps' => 'application/mspowerpoint',
		'ppt' => 'application/mspowerpoint',
		'ppz' => 'application/mspowerpoint',
		'pre' => 'application/x-freelance',
		'prt' => 'application/pro_eng',
		'ps' => 'application/postscript',
		'roff' => 'application/x-troff',
		'scm' => 'application/x-lotusscreencam',
		'set' => 'application/set',
		'sh' => 'application/x-sh',
		'shar' => 'application/x-shar',
		'sit' => 'application/x-stuffit',
		'skd' => 'application/x-koan',
		'skm' => 'application/x-koan',
		'skp' => 'application/x-koan',
		'skt' => 'application/x-koan',
		'smi' => 'application/smil',
		'smil' => 'application/smil',
		'sol' => 'application/solids',
		'spl' => 'application/x-futuresplash',
		'src' => 'application/x-wais-source',
		'step' => 'application/STEP',
		'stl' => 'application/SLA',
		'stp' => 'application/STEP',
		'sv4cpio' => 'application/x-sv4cpio',
		'sv4crc' => 'application/x-sv4crc',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		'swf' => 'application/x-shockwave-flash',
		't' => 'application/x-troff',
		'tar' => 'application/x-tar',
		'tcl' => 'application/x-tcl',
		'tex' => 'application/x-tex',
		'texi' => 'application/x-texinfo',
		'texinfo' => 'application/x-texinfo',
		'tr' => 'application/x-troff',
		'tsp' => 'application/dsptype',
		'ttf' => 'font/ttf',
		'unv' => 'application/i-deas',
		'ustar' => 'application/x-ustar',
		'vcd' => 'application/x-cdlink',
		'vda' => 'application/vda',
		'xlc' => 'application/vnd.ms-excel',
		'xll' => 'application/vnd.ms-excel',
		'xlm' => 'application/vnd.ms-excel',
		'xls' => 'application/vnd.ms-excel',
		'xlw' => 'application/vnd.ms-excel',
		'zip' => 'application/zip',
		'aif' => 'audio/x-aiff',
		'aifc' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'au' => 'audio/basic',
		'kar' => 'audio/midi',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'mp2' => 'audio/mpeg',
		'mp3' => 'audio/mpeg',
		'mpga' => 'audio/mpeg',
		'ogg' => 'audio/ogg',
		'oga' => 'audio/ogg',
		'spx' => 'audio/ogg',
		'ra' => 'audio/x-realaudio',
		'ram' => 'audio/x-pn-realaudio',
		'rm' => 'audio/x-pn-realaudio',
		'rpm' => 'audio/x-pn-realaudio-plugin',
		'snd' => 'audio/basic',
		'tsi' => 'audio/TSP-audio',
		'wav' => 'audio/x-wav',
		'aac' => 'audio/aac',
		'asc' => 'text/plain',
		'c' => 'text/plain',
		'cc' => 'text/plain',
		'css' => 'text/css',
		'etx' => 'text/x-setext',
		'f' => 'text/plain',
		'f90' => 'text/plain',
		'h' => 'text/plain',
		'hh' => 'text/plain',
		'html' => array('text/html', '*/*'),
		'htm' => array('text/html', '*/*'),
		'ics' => 'text/calendar',
		'm' => 'text/plain',
		'rtf' => 'text/rtf',
		'rtx' => 'text/richtext',
		'sgm' => 'text/sgml',
		'sgml' => 'text/sgml',
		'tsv' => 'text/tab-separated-values',
		'tpl' => 'text/template',
		'txt' => 'text/plain',
		'text' => 'text/plain',
		'xml' => array('application/xml', 'text/xml'),
		'avi' => 'video/x-msvideo',
		'fli' => 'video/x-fli',
		'mov' => 'video/quicktime',
		'movie' => 'video/x-sgi-movie',
		'mpe' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'qt' => 'video/quicktime',
		'viv' => 'video/vnd.vivo',
		'vivo' => 'video/vnd.vivo',
		'ogv' => 'video/ogg',
		'webm' => 'video/webm',
		'mp4' => 'video/mp4',
		'gif' => 'image/gif',
		'ief' => 'image/ief',
		// 'jpe' => 'image/jpeg', Imagine wont recognize jpe
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'pbm' => 'image/x-portable-bitmap',
		'pgm' => 'image/x-portable-graymap',
		'png' => 'image/png',
		'pnm' => 'image/x-portable-anymap',
		'ppm' => 'image/x-portable-pixmap',
		'ras' => 'image/cmu-raster',
		'rgb' => 'image/x-rgb',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'xbm' => 'image/x-xbitmap',
		'xpm' => 'image/x-xpixmap',
		'xwd' => 'image/x-xwindowdump',
		'ice' => 'x-conference/x-cooltalk',
		'iges' => 'model/iges',
		'igs' => 'model/iges',
		'mesh' => 'model/mesh',
		'msh' => 'model/mesh',
		'silo' => 'model/mesh',
		'vrml' => 'model/vrml',
		'wrl' => 'model/vrml',
		'mime' => 'www/mime',
		'pdb' => 'chemical/x-pdb',
		'xyz' => 'chemical/x-pdb',
		'javascript' => 'text/javascript',
		'json' => 'application/json',
		'form' => 'application/x-www-form-urlencoded',
		'file' => 'multipart/form-data',
		'xhtml'	=> array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
		'xhtml-mobile'	=> 'application/vnd.wap.xhtml+xml',
		'rss' => 'application/rss+xml',
		'atom' => 'application/atom+xml',
		'amf' => 'application/x-amf',
		'wap' => array('text/vnd.wap.wml', 'text/vnd.wap.wmlscript', 'image/vnd.wap.wbmp'),
		'wml' => 'text/vnd.wap.wml',
		'wmlscript' => 'text/vnd.wap.wmlscript',
		'wbmp' => 'image/vnd.wap.wbmp',
	);

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
		foreach ($this->types[$model->alias] as $type) {
			foreach ($model->data[$model->alias][$type] as $check) {
				if (isset($check['name']) && !empty($check['name'])) {
					if(!in_array($this->getFileExtension($check['name']), $extensions)) {
						return false;
					}
				}
			}
		}

		return true;
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

	/*
	 * Process before validation. Initialize the source.
	 */
	public function beforeValidate($model) {
		foreach ($this->types[$model->alias] as $type) {
			foreach ($model->data[$model->alias][$type] as $index => $check) {
				if (isset($check['uri']) && !empty($check['uri'])) {
					$response = $this->response($check['uri']);
					$model->data[$model->alias][$type][$index] = $response;
				}
			}
		}
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

		Debugger::log($uploadData);
		if (strlen($uploadData['tmp_name']) > 0) {
			Debugger::log("File is uploaded");
			$file = $this->generateName($model, $type, $index);
			$attach = $this->_saveAttachment($model, $type, $file, $uploadData);
	
			@unlink($uploadData['tmp_name']);
		}
		else {
			Debugger::log("File is not uploaded");
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

	/*
	 * response
	 * 
	 * Gets the response from a particular URI and returns the result.
	 */
	private function response($uri) {
		$response = array();
		$s = new HttpSocket();
		$file_name = String::uuid();
		$tmp_file_path = TMP.$file_name;

		$f = fopen($tmp_file_path, 'w');
		$s->setContentResource($f);
		$httpResponse = $s->get($uri, array(), array('redirect' => true));
		fclose($f);

		if($httpResponse->isOk()) {
			$type = $httpResponse->getHeader('Content-Type');
			$extension = array_search($type, $this->_mimeTypes);
			Debugger::log($extension);
			$file_name = $file_name.".".array_search($type, $this->_mimeTypes);
			$response = array('type' => $type,
								'size' => $httpResponse->getHeader('Content-Length'),
								'tmp_name'=> $tmp_file_path,
								'name' => $file_name);
		}

		return $response;
	}
}
?>
