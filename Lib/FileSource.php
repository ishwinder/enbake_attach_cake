<?php
App::uses("Source", "EnbakeAttach.Lib");

class FileSource extends Source {
	function deleteAttachment($attachment) {
		$this->deleteFile($attachment['uri']);
	}

	function deleteAspects($attachment, $aspects) {
		if (isset($attachment['aspect_uri']) && !empty($attachment['aspect_uri'])) {
			$files = glob($attachment['aspect_uri'].DS.'*');
			if (is_array($files)) {
				foreach ($files as $fileToDelete) {
					$this->deleteFile($fileToDelete);
				}
			}
		}
	}

	private function deleteFile($fileName) {
		if (file_exists($fileName)) {
			return unlink($fileName);
		}

		return false;
	}
}