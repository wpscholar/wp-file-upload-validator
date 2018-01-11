<?php

namespace wpscholar\WordPress;

/**
 * Class FileUploadValidator
 *
 * @package wpscholar\WordPress
 *
 * @property int $error
 * @property string $name
 * @property string $path
 * @property int $size
 * @property string $type
 */
class FileUploadValidator {

	/**
	 * File handle name
	 *
	 * @var string
	 */
	protected $_handle;

	/**
	 * Allowed file extensions
	 *
	 * @var array
	 */
	protected $_allowed_file_extensions = [];

	/**
	 * Allowed file types
	 *
	 * Basically, this is just the first part of the mime type.
	 *
	 * @var array
	 */
	protected $_allowed_file_types = [];

	/**
	 * Allowed mime types
	 *
	 * @var array
	 */
	protected $_allowed_mime_types = [];

	/**
	 * FileUploadValidator constructor.
	 *
	 * @param string $handle
	 */
	public function __construct( $handle ) {
		$this->_handle = $handle;
	}

	/**
	 * Add an allowed file extension
	 *
	 * @param string $file_ext
	 */
	public function addAllowedFileExtension( $file_ext ) {
		$file_extensions = func_get_args();
		foreach ( $file_extensions as $file_ext ) {
			$this->_allowed_file_extensions[] = strtolower( $file_ext );
		}
	}

	/**
	 * Add an allowed file type
	 *
	 * @param string $file_type e.g. audio, video, image, text
	 */
	public function addAllowedFileType( $file_type ) {
		$file_types = func_get_args();
		foreach ( $file_types as $file_type ) {
			$this->_allowed_file_types[] = strtolower( $file_type );
		}
	}

	/**
	 * Add an allowed mime type
	 *
	 * @param string $mime_type
	 */
	public function addAllowedMimeType( $mime_type ) {
		$mime_types = func_get_args();
		foreach ( $mime_types as $mime_type ) {
			$this->_allowed_mime_types[] = strtolower( $mime_type );
		}
	}

	/**
	 * Check if file upload is valid
	 *
	 * @return \WP_Error|true Return true on success or a WP_Error instance on falure.
	 */
	public function isValid() {

		try {

			// Check if files array is set
			if ( ! isset( $_FILES ) ) {
				throw new \RuntimeException( 'Please upload a file.' );
			}

			// Check for file for PHP errors
			if ( isset( $_FILES, $_FILES[ $this->_handle ], $_FILES[ $this->_handle ]['error'] ) ) {
				switch ( $_FILES[ $this->_handle ]['error'] ) {
					case UPLOAD_ERR_OK:
						// If there is no error, just break out
						break;
					case UPLOAD_ERR_INI_SIZE:
						throw new \RuntimeException( 'The uploaded file exceeds the maximum allowed file size.' );
					case UPLOAD_ERR_FORM_SIZE:
						throw new \RuntimeException( 'The uploaded file exceeds the maximum allowed file size.' );
					case UPLOAD_ERR_PARTIAL:
						throw new \RuntimeException( 'The uploaded file was only partially uploaded. Please try again.' );
					case UPLOAD_ERR_NO_FILE:
						throw new \RuntimeException( 'No file was uploaded. Please upload a file.' );
					case UPLOAD_ERR_NO_TMP_DIR:
						throw new \RuntimeException( 'Unable to upload file. Missing a temporary folder. Please contact a site administrator.' );
					case UPLOAD_ERR_CANT_WRITE:
						throw new \RuntimeException( 'Failed to write file to disk. Please have a site administrator check permissions.' );
					case UPLOAD_ERR_EXTENSION:
						throw new \RuntimeException( 'File upload stopped by an extension. Please contact a site administrator.' );
					default:
						throw new \RuntimeException( 'An unknown upload error occurred. Please try again. If the issue persists, contact a site administrator.' );
				}
			}

			// Validate mime type
			if ( ! empty( $this->_allowed_mime_types ) ) {
				if ( ! in_array( strtolower( mime_content_type( $this->path ) ), $this->_allowed_mime_types, true ) ) {
					throw new \RuntimeException( 'Invalid file type.' );
				}
			}

			// Validate file type
			if ( ! empty( $this->_allowed_file_types ) ) {
				$mime_type_parts = explode( '/', mime_content_type( $this->path ) );
				$file_type = strtolower( array_shift( $mime_type_parts ) );
				if ( ! in_array( $file_type, $this->_allowed_file_types, true ) ) {
					throw new \RuntimeException( 'Invalid file type.' );
				}
			}

			// Validate file extension
			if ( ! empty( $this->_allowed_file_extensions ) ) {
				if ( ! in_array( strtolower( pathinfo( $this->name, PATHINFO_EXTENSION ) ), $this->_allowed_file_extensions, true ) ) {
					throw new \RuntimeException( 'Invalid file extension.' );
				}
			}

			return true;

		} catch ( \Exception $e ) {
			return new \WP_Error( 'upload', $e->getMessage() );
		}

	}

	/**
	 * Get error
	 *
	 * @return string
	 */
	protected function _get_error() {
		return $this->_get( [ $this->_handle, 'error' ], 0 );
	}

	/**
	 * Get file name
	 *
	 * @return string
	 */
	protected function _get_name() {
		return $this->_get( [ $this->_handle, 'name' ], '' );
	}

	/**
	 * Get file path
	 *
	 * @return string
	 */
	protected function _get_path() {
		return $this->_get( [ $this->_handle, 'tmp_name' ], '' );
	}

	/**
	 * Get file size
	 *
	 * @return string
	 */
	protected function _get_size() {
		return absint( $this->_get( [ $this->_handle, 'size' ], 0 ) );
	}

	/**
	 * Get file type (mime type)
	 *
	 * @return string
	 */
	protected function _get_type() {
		return $this->_get( [ $this->_handle, 'type' ], '' );
	}

	/**
	 * Get value from an array, with default as fallback
	 *
	 * @param string|array $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	protected function _get( $key, $default = null ) {

		$value = $default;

		if ( isset( $_FILES ) ) {

			if ( is_string( $key ) ) {
				if ( isset( $_FILES[ $key ] ) ) {
					$value = $_FILES[ $key ];
				}
			}

			if ( is_array( $key ) ) {
				$value = $_FILES;
				$segments = $key;
				foreach ( $segments as $segment ) {
					if ( isset( $value[ $segment ] ) ) {
						$value = $value[ $segment ];
					} else {
						$value = $default;
						break;
					}
				}
			}

		}

		return $value;
	}

	/**
	 * Getter function.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		$value = null;
		$method = "_get_{$property}";
		if ( method_exists( $this, $method ) && is_callable( [ $this, $method ] ) ) {
			$value = $this->$method();
		}

		return $value;
	}

}
