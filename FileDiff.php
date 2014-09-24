<?php
	/**
	 * Aggregator of many text diff per file
	 */
	class FileDiff {
		/**
		 * @var string
		 */
		protected $_filename;
	
		/**
		 * @var TextDiff[]
		 */
		protected $_textDiffs = array();

		public function setFilename($filename) {
			$this->_filename = $filename;
		}

		public function getFilename() {
			return $this->_filename;
		}

		public function getTextDiffs() {
			return $this->_textDiffs;
		}

		public function setTextDiffs($textDiffs) {
			$this->_textDiffs = $textDiffs;
		}

		public function addTextDiff($textDiff) {
			if (is_array($textDiff)) {
				foreach($textDiff as $item) {
					$this->_textDiffs[] = $item;
				}
			} else {
				$this->_textDiffs[] = $textDiff;
			}
		}

		public function setCommitId($commitId) {
			foreach($this->_textDiffs as &$textDiff) {
				$textDiff->setCommit($commitId);
			}
		}
	}