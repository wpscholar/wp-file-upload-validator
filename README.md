# WordPress File Upload Validator

Validate that a file upload has no errors and consists of the correct file extension, file type and/or mime type.

## Installation

```shell
composer require wpscholar/wp-file-upload-validator
```

## Usage 

```php

$file_handle = 'avatar'; // Name of the file input field.

$validator = new wpscholar\WordPress\FileUploadValidator( $file_handle );

$validator->addAllowedFileType( 'image' );
$validator->addAllowedMimeType( 'image/jpeg', 'image/png' );
$validator->addAllowedFileExtension( 'jpg', 'jpeg', 'png' );

$isValid = $validator->isValid(); // Returns "true" or a WP_Error instance containing the error message.

```
