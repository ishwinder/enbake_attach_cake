<?php
class Source {
	abstract function mime();
	abstract function extension();
}

class FileSource extends Source {
	
}

class URISource extends Source {
	
}