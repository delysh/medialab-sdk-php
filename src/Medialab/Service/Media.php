<?php

namespace Medialab\Service;

/**
 * Class Media
 * @package medialab-sdk-php
 *
 * Shortcuts to media-related API methods.
 */
class Media extends MedialabService {

	/**
	 * @var array
	 */
	private $upload_id;

	function __construct(\Medialab\Config\ConfigInterface $config) {
		parent::__construct($config);
	}

	/**
	 * Get information about the folder
	 * @param string $folder_id
	 * @return array
	 */
	public function getFolderInfo(string $folder_id) {
		return $this->execute("folders/{$folder_id}", 'GET');
	}

	/**
	 * Get folder contents
	 * @param string $folder_id
	 * @param boolean $list_children
	 * @param boolean $list_files
	 * @return array
	 */
	public function getFolderContents(string $folder_id = "0", bool $list_children = true, bool $list_files = true) {
		return $this->execute("folders/{$folder_id}/contents", 'GET', [
			'query' => [
				'folders' => (int) $list_children,
				'files' => (int) $list_files,
			]
		]);
	}

	/**
	 * Get static share links for all files in a folder
	 * (e.g. embed iframe, direct link to source, etc)
	 * @param string $folder_id
	 * @return array
	 */
	public function getFolderShare(string $folder_id) {
		return $this->execute("folders/{$folder_id}/share", 'GET');
	}

	/**
	 * Add a folder
	 * @param string $folder_id parent folder id, or 0 for root
	 * @param string $name
	 * @return array
	 */
	public function addFolder(string $folder_id, string $name) {
		return $this->execute("folders/{$folder_id}", 'POST', [
			'form_params' => [
				'name' => $name,
			]
		]);
	}

	/**
	 * Edit a folder
	 * @param string $folder_id parent folder id, or 0 for root
	 * @param string $name
	 * @return array
	 */
	public function editFolder(string $folder_id, string $name) {
		return $this->execute("folders/{$folder_id}", 'PUT', [
			'form_params' => ['name' => $name]
		]);
	}

	/**
	 * Get information about a file
	 * @param string $file_id
	 * @return array
	 */
	public function getFileInfo(string $file_id) {
		return $this->execute("files/{$file_id}", 'GET');
	}

	/**
	 * Get static share links for a file (e.g. embed iframe, direct link to source, etc)
	 * @param string $file_id
	 * @return array
	 */
	public function getFileShare(string $file_id) {
		return $this->execute("files/{$file_id}/share", 'GET');
	}

	/**
	 * Start the upload process by requesting an upload id
	 */
	public function startUpload() {
		$this->upload_id = $this->execute('upload/id', 'POST');
		return $this->upload_id;
	}

	/**
	 * Upload a file
	 * @param string $folder_id target folder
	 * @param string $path absolute path to file
	 * @param string $filename to change filename
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function uploadFile(string $folder_id, string $path, string $filename = null) {
		if(!file_exists($path)) {
			throw new \InvalidArgumentException('Invalid path to file provided');
		}
		if(empty($this->upload_id)) {
			$this->startUpload();
		}
		$data = [
			'name'     => 'file',
			'contents' => fopen($path, 'r')
		];
		if($filename !== null) {
			$data['filename'] = $filename;
		}

		return $this->execute(
			"upload/file/{$this->upload_id['ulid']}/{$folder_id}", 'POST', array(
				'multipart' => [$data]
		));
	}

	/**
	 * Finish the upload process
	 */
	public function finishUpload() {
		$result = $this->execute('upload/id/' . $this->upload_id['ulid'], 'DELETE');
		$this->upload_id = null;
		return $result;
	}
}