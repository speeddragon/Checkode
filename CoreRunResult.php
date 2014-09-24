<?php
/**
 * Object that holds all information returned by the API
 */
class CoreRunResult {
	/**
	 * @var Issue[]
	 */
	protected $_issues = array();

	/**
	 * @var FileDiff[]
	 */
	protected $_diffs  = array();

	/**
	 *  @return Issue[]
	 */
	public function getIssues() {
		return $this->_issues;
	}

	/**
	 *  @param $issues Issue[]
	 */
	public function setIssues($issues) {
		$this->_issues = $issues;
	}

	/**
	 *  @return FileDiff[]
	 */
	public function getDiffs() {
		return $this->_diffs;
	}

	/**
	 *  @param $diffs FileDiff[]
	 */
	public function setDiffs($diffs) {
		$this->_diffs = $diffs;
	}

	/**
	 * Merge two objects in one.
	 *
	 * @param $coreRunResult CoreRunResult
	 */
	public function join(CoreRunResult $coreRunResult) {
		$this->_issues = array_merge($this->_issues, $coreRunResult->getIssues());
		$this->_diffs  = array_merge($this->_diffs, $coreRunResult->getDiffs());
	}

	/**
	 * Join File
	 *
	 * Join intelligently each diff text with one file/filename
	 *
	 * @param $coreRunResult CoreRunResult
	 */
	public function joinFile(CoreRunResult $coreRunResult) {
		$this->_issues = array_merge($this->_issues, $coreRunResult->getIssues());

		if (is_array($this->_diffs) && count($this->_diffs)) {	
			foreach($coreRunResult->getDiffs() as $fileDiff2) {
				$added = false;
				foreach($this->_diffs as &$fileDiff) {
					if ( $fileDiff->getFilename() == $fileDiff2->getFilename() ) {
						$fileDiff->addTextDiff($fileDiff2->getTextDiffs());
						$added = true;
					}
				}

				if (!$added) {
					$this->_diffs[] = $fileDiff2;
				}
			} 
		} else if (is_array($coreRunResult->getDiffs()) && count($coreRunResult->getDiffs())) {
			$this->_diffs = array_merge($this->_diffs, $coreRunResult->getDiffs());
		}
	}
}