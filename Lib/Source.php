<?php
abstract class Source {
	/*
	 * Takes in the attachment to delete
	 */
	abstract function deleteAttachment($attachment);
	/*
	 * Remove Aspects of a given attachment Source.
	 */
	abstract function deleteAspects($attachment, $aspects);
}