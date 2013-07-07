<?php
App::uses('Folder', 'Utility');
App::uses("S3", "EnbakeAttach.Vendor");

class Attach {
	/*
	 * create a temporary file of given attachment and return the path.
	 */
	function getFullFilePath($attachment) {
		$path = '';

		if($attachment['Attachment']['storage'] == 'file') {
			$path = $attachment['Attachment']['uri'];
		}
		if($attachment['Attachment']['storage'] == 's3') {
			$tmp_path = "/tmp/sh/";
			$folder = new Folder($tmp_path, true, 0744);
			$path = $tmp_path.$attachment['Attachment']['filename'];

			$bucket = strstr($attachment['Attachment']['uri'], DS, true);
			$s3_path = substr($attachment['Attachment']['uri'], strpos($attachment['Attachment']['uri'], DS) + 1);
			$s3 = new S3(Configure::read('S3.access_key'), Configure::read('S3.secret_key'));

			$response = $s3->getObject($bucket, $s3_path, $path);
		}

		return $path;
	}

	/*
	 * Gets the full aspect path.
	 */
	function getFullAspectPath($attachment, $aspect) {
		$path = '';

		if($attachment['Attachment']['aspect_storage'] == 'file') {
			$path = $attachment['Attachment']['aspect_uri']; 
		}
		if($attachment['Attachment']['aspect_storage'] == 's3') {
			$tmp_path = "/tmp/sh/";
			$folder = new Folder($tmp_path, true, 0744);
			$path = $tmp_path.$attachment['Attachment']['filename'];

			$bucket = strstr($attachment['Attachment']['uri'], DS, true);
			$s3_path = substr($attachment['Attachment']['uri'], strpos($attachment['Attachment']['uri'], DS) + 1);
			$s3 = new S3(Configure::read('S3.access_key'), Configure::read('S3.secret_key'));

			$response = $s3->getObject($bucket, $s3_path, $path);
		}

		return $path;
	}
	
	/*
	 * gets the name of the aspect
	 */
	function getAspectName($attachment, $aspect) {
		$name = '';

		if($attachment['Attachment']['aspect_storage'] == 'file') {
			$name = $attachment['Attachment']['filename'];

			$name = pathinfo($name, PATHINFO_FILENAME)."_".$aspect.".".pathinfo($name, PATHINFO_EXTENSION);
		}

		return $name;
	}

	/*
	 * This is a patch function because the Cake's Media views can't serve from
	 * the URI directly.
	 */
	function getAspectURL($attachment, $aspect) {
		if ($attachment['aspect_storage'] == 'file') {
			$url = Router::url(array("plugin"=>"enbake_attach", "controller"=>"attachments",
					"action"=>"aspect", $attachment['id'], $aspect), true);
		}
		else {
			$name = $attachment['filename'];
			$name = pathinfo($name, PATHINFO_FILENAME)."_".$aspect.".".pathinfo($name, PATHINFO_EXTENSION);
			$filename = "http://".$attachment['aspect_uri'].DS."{$name}";
			$url = Router::url($filename);
		}

		return $url;
	}
}
