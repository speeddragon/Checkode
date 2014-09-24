<?php
	/**
	 * Information regarding a commit. 
	 */
	class TextDiff {
		/** 
		 * @var string 
		 */
		protected $_text;

		/** 
		 * @var string 
		 */
		protected $_commit;

		public function getText() {
			return $this->_text;
		}

		public function setText($text) {
			$this->_text = $text;
		}

		public function addText($text) {
			$this->_text .= $text;
		}	

		public function getCommit() {
			return $this->_commit;
		}

		public function setCommit($commit) {
			$this->_commit = $commit;
		}
	}