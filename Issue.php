<?php
	class Issue {
		/**
		 * @var string
		 */
		protected $_filename;
		
		/**
		 * @var int
		 */
		protected $_lineNumber;

		/**
		 * @var string
		 */
		protected $_line;

		/**
		 * @var CodeWarning
		 */
		protected $_codeWarning;

		/**
		 * @var string
		 */
		protected $_commitId;

		public function setCodeWarning($codeWarning) {
			$this->_codeWarning = $codeWarning;
		}

		public function getCodeWarning() {
			return $this->_codeWarning;
		}

		public function getFilename() {
			return $this->_filename;
		}

		public function setFilename($filename) {
			$this->_filename = $filename;
		}

		public function setCommitId($commitId) {
			$this->_commitId = $commitId;
		}

		public function getCommitId() {
			return $this->_commitId;
		}

		public function setLineNumber($lineNumber) {
			$this->_lineNumber = $lineNumber;
		}

		public function getLineNumber() {
			return $this->_lineNumber;
		}

		public function setLine($line) {
			$this->_line = $line;
		}

		public function getLine() {
			return $this->_line;
		}
	}
?>