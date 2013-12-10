# Attach 0.1

Attach is a CakePHP 2.0 Plugin, that make your upload a simple task!

Attach contains one behavior that do everything for you, upload your file, resize your image.

## Installation
- Clone from github : in your app directory type `git clone git@github.com:krolow/Attach.git Plugin/Attach`

- Add as a git submodule : in your app directory type `git submodule add git@github.com:krolow/Attach.git Plugin/Attach`
- Download an archive from github and extract it in `app/Plugin/Attach`

* If you require thumbnails for image generation, download the latest copy of Imagine.phar here: https://github.com/avalanche123/Imagine/blob/develop/imagine.phar

## Usage
In a model that needs uploading, replace the class declaration with something similar to the following:


It's important to remember that your model class can have your own fields, and it will have a extra relation with Attachment model with the fields that are upload.

<pre>
	App::uses('AppModel', 'Model');

	class Media extends AppModel {

		public $validate = array(
			'image' => array(
				'extension' => array(
					'rule' => array(
						'extension', array(
							'jpg',
							'jpeg',
							'bmp',
							'gif',
							'png',
							'jpg'
						)
					),
					'message' => 'File extension is not supported',
					'on' => 'create'
				),
				'mime' => array(
					'rule' => array('mime', array(
						'image/jpeg',
						'image/pjpeg',
						'image/bmp',
						'image/x-ms-bmp',
						'image/gif',
						'image/png'
					)),
					'on' => 'create'
				),
				'size' => array(
					'rule' => array('size', 2097152),
					'on' => 'create'
				)
			),
			'swf' => array(
				'extension' => array(
					'rule' => array(
						'extension', array(
							'swf',
						)
					),
					'message' => 'File extension is not supported',
					'on' => 'create'
				),
				'mime' => array(
					'rule' => array('mime', array(
						'application/x-shockwave-flash',
					)),
					'on' => 'create'
				),
				'size' => array(
					'rule' => array('size', 53687091200),
					'on' => 'create'
				)
			),
			'zip' => array(
				'extension' => array(
					'rule' => array(
						'extension', array(
							'zip',
						)
					),
					'message' => 'File extension is not supported',
					'on' => 'create'
				),
				'mime' => array(
					'rule' => array('mime', array(
						'application/zip',
						'multipart/x-zip'
					)),
					'on' => 'create'
				),
				'size' => array(
					'rule' => array('size', 53687091200),
					'on' => 'create'
				)
			),
		);

		public $actsAs = array(
			'Attach.Upload' => array(
				'swf' => array(
				    'dir' => 'webroot{DS}uploads{DS}media{DS}swf'
				),
				'image' => array(
				    'dir' => 'webroot{DS}uploads{DS}media{DS}image',
				    'thumbs' => array(
				        'thumb' => array(
				            'w' => 190,
				            'h' => 158,
				            'crop' => true,
				        ),
				    ),
				),
				'zip' => array(
				    'dir' => 'webroot{DS}uploads{DS}media{DS}zip'
				),
			),
		);
</pre>

You also need to specify the fields in your database like so
<pre>
CREATE TABLE  `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(150) NOT NULL,
  `model` varchar(150) NOT NULL,
  `foreign_key` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `user_id` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
</pre>

Create your upload view, make sure it's a multipart/form-data form, and the filename field is of type 'file':

<pre>
		echo $this->Form->create('Media', array('type' => 'file'));
		echo $this->Form->input('name');
		echo $this->Form->input('image', array('type' => 'file'));
		echo $this->Form->input('swf', array('type' => 'file'));
		echo $this->Form->input('zip', array('type' => 'file'));
		echo $this->Form->input('status');
		echo $this->Form->end(__('Submit'));
</pre>



Attach creates automatic for you the relationship with the model Attachment, for each type that you define:

<pre>
		var_dump($this->Media->AttachmentImage);
		var_dump($this->Media->AttachmentSwf);
		var_dump($this->Media->AttachmentZip);
</pre>

It will be always "Attachment" plus the type!
