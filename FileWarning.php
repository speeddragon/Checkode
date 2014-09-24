<?php
	class FileWarning {
		protected $_path;

		public function __construct($path) {
			$this->_path = $path;
		}

		public function getPath() {
			return $this->_path;
		}
	}