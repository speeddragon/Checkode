<?php
	/**
	 *
	 */
	class Report {
		/** 
		 * @var string 
		 */
		protected $_filename;
		/** 
		 * @var CodeWarning 
		 */
		protected $_codeWarning;

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
	}
?>