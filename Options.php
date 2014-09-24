<?php
	/**
	 * Options for interact with API
	 */
	class Options {
		protected $_repository;
		protected $_show = false;
		protected $_help = false;
		protected $_operation;
		protected $_folder;
		protected $_others;
		protected $_filterFolder;
		protected $_url;

		public function getRepository() {
			return $this->_repository;
		}

		public function setRepository($repository) {
			$this->_repository = $repository;
		}

		public function getShow() {
			return $this->_show;
		}

		public function setShow($show) {
			$this->_show = $show;
		}

		public function getHelp() {
			return $this->_help;
		}

		public function setHelp($help) {
			$this->_help = $help;
		}

		public function getOperation() {
			return $this->_operation;
		}

		public function setOperation($operation) {
			$this->_operation = $operation;
		}

		public function getFolder() {
			return $this->_folder;
		}

		public function setFolder($folder) {
			$this->_folder = $folder;
		}

		public function getOthers($pos = null){
			if (is_array($this->_others) && isset($pos) && isset($this->_others[$pos])) {
				return $this->_others[$pos];
			} else if (is_array($this->_others) && !isset($pos) && count($this->_others) > 0) {
				return $this->_others[0];
			} else {
				return $this->_others;
			}
		}

		public function setOthers($others) {
			$this->_others = $others;
		}

		public function setFilterFolder($filterFolder) {
			$this->_filterFolder = $filterFolder;
		}

		public function getFilterFolder() {
			return $this->_filterFolder;
		}

		/**
		 * Check if filter is valid
		 */
		public function checkFilter($filename) {
			if ($this->_filterFolder != null && fnmatch($this->_filterFolder, $filename)) {
				return true;
			} else if ($this->_filterFolder == null) {
				return true;
			} else {
				return false;
			}
		}

		public function getUrl() {
			return $this->_url;
		}

		public function setUrl($url) {
			$this->_url = $url;
		}

		/* -------------------------------------------- */

		public function checkRepository() {
			if ($this->getRepository() != null) {
				// Update repository for last code version
				chdir($this->getRepository());
				shell_exec("git fetch --all");
				shell_exec("git reset --hard origin/master");
				
			} else {
				throw new Exception("Please setup the respository!");
			}
		}
	}