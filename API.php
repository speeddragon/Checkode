<?php
require_once ('Utils.php');

require_once ('Issue.php');

require_once ('CodeWarning.php');
require_once ('FileWarning.php');
require_once ('Options.php');
require_once ('FileDiff.php');
require_once ('CoreRunResult.php');
require_once ('TextDiff.php');

ini_set("auto_detect_line_endings", true);

class API {
	/**
	 * Get Git Hash by URL
	 *
	 * Parse an html page to extract all git hash to analyze
	 *
	 * @param $url string Page URL
	 * @return string[] List of git hash
	 */
	private function getHashWithURL($url) {
		$html = file_get_contents($url);

		// GIT LAB PATH
		$commits = explode("https://gitlab/", $html);
		array_shift($commits);

		$hashList = array();

		foreach ($commits as $commit) {
			$hash = explode("/commit/", $commit);
			$hash = substr($hash[1], 0, strpos($hash[1], '"'));

			$hashList[] = $hash;
		}

		return $hashList;
	}

	/**
	 * Populate issues with aditional information
	 *
	 * @param $coreRunResult CoreRunResult
	 * @param $option Options User options
	 *
	 * @return Issue[] List of issues
	 */
	private function populateIssueWithInformation(CoreRunResult&$coreRunResult, $option, $commitHash) {
		
		// ISSUES
		$issues = $coreRunResult->getIssues();

		foreach ($issues as &$issue) {
			$issue->setCommitId($commitHash);

			$fullPath = $option->getRepository().'/'.$issue->getFilename();
			$lines    = file($fullPath, FILE_IGNORE_NEW_LINES);

			$lineCount = 1;
			foreach ($lines as $line) {
				if ($line == $issue->getLine()) {
					$issue->setLineNumber($lineCount);
					break;
				}

				$lineCount++;
			}
		}

		$coreRunResult->setIssues($issues);

		// FILE DIFFS
		$diffs = $coreRunResult->getDiffs();

		foreach ($diffs as $diff) {
			$diff->setCommitId($commitHash);
		}

		$coreRunResult->setDiffs($diffs);

	}

	/**
	 * Search for issues in one commit
	 *
	 * @param $option Options User options
	 *
	 * @return Issue[] List of issues founded
	 */
	public function commit(Options $option) {
		// Load rules
		$codeWarning = getWarningCode();

		// Check repository
		$option->checkRepository();

		// Get commit hash
		$commitHash = $option->getOthers(0);

		if (!isset($commitHash)) {
			throw new Exception("Please provide a commit hash!");
		}

		if (!ctype_alnum($commitHash)) {
			throw new Exception("Invalid hash provided.");
		}

		$codeChangeList = shell_exec("git show ".$commitHash);
		$fileCodeList   = explode("+++ b/", $codeChangeList);

		// Remove unwanted first part
		array_shift($fileCodeList);

		$coreRunResult = new CoreRunResult();

		$coreRunResult = $this->coreRun($option, $fileCodeList, $codeWarning);
		$this->populateIssueWithInformation($coreRunResult, $option, $commitHash);

		return $coreRunResult;
	}

	/**
	 * Search for issues between code changed
	 *
	 * @param $option Options User options
	 *
	 * @return Issue[] List of issues founded
	 */
	public function changes(Options $option) {
		// Load Rules
		$codeWarning = getWarningCode();

		// Check repository
		$option->checkRepository();

		$commitHashList = $option->getOthers();
		if ($commitHashList != null && count($commitHashList) == 0) {
			throw new Exception("Please provide commit hashes!");
		} else {
			# Check for the URL
			$url = $option->getUrl();
			if ($url != null) {
				$commitHashList = $this->getHashWithURL($url);

				if (DEBUG) {
					// TODO: Output in an object
					echo 'Number of hash: '.count($commitHashList)."\n";
				}

			} else {
				throw new Exception("Please provide some hashes!");
			}
		}

		$coreRunResultFinal = new CoreRunResult();
		foreach ($commitHashList as $commitHash) {

			if (!ctype_alnum($commitHash)) {
				throw new Exception("Invalid hash provided.");
			}

			$codeChangeList = shell_exec("git show ".$commitHash);
			$fileCodeList   = explode("+++ b/", $codeChangeList);

			// Remove unwanted first part
			array_shift($fileCodeList);

			$coreRunResult = $this->coreRun($option, $fileCodeList, $codeWarning);
			$this->populateIssueWithInformation($coreRunResult, $option, $commitHash);

			$coreRunResultFinal->joinFile($coreRunResult);
		}

		return $coreRunResultFinal;
	}

	// TODO: Check and finish the implementation
	public function files(Options $option) {
		$fileList = array();

		$option->checkRepository();

		$commitHash1 = $option->getOthers(0);
		$commitHash2 = $option->getOthers(1);

		if (!isset($commitHash1) || !isset($commitHash2)) {
			throw new Exception("Please provide two commit hashes!");
		}

		if (!ctype_alnum($commitHash1) || !ctype_alnum($commitHash2)) {
			throw new Exception("Please provide two valid commit hashes!");
		}

		//TODO: Grep and pipe aren't Windows friendly
		$fileChangeList = shell_exec("git diff ".$commitHash1." ".$commitHash2);
		$fileChangeList = explode("+++ b/", $fileChangeList);

		// Remove unwanted first part
		array_shift($fileChangeList);

		$fileWarnings = getFileWarnings();

		# Alert if file is found
		/*foreach($files as $file) {
		foreach($fileWarnings as $fileWarning) {
		if (strpos($file, $fileWarning->getPath()) !== false) {
		//echo $fileWarning->getPath() . " found in the change list!\n";
		$fileList[] = $file;
		}
		}
		}*/

		# More options
		if ($option->getShow()) {
			# Ignore test folders
			foreach ($fileChangeList as $file) {
				$file = explode("\n", $file);
				$file = trim($file[0]);

				if (!isTestFolder($file)) {
					//echo $file . "\n";
					$fileList[] = $file;
				}
			}
		}

		return $fileList;
	}

	public function search(Options $option) {
		$startDirectory = $option->getFolder();

		if ($startDirectory == null) {
			$startDirectory = $option->getRepository();
		}

		if ($startDirectory != null) {
			$codeWarning = getWarningCode();

			//echo "Searching inside the folder: " . $startDirectory . "\n";
			// TODO: Return number of issues found
			$issuesList = searchInDirectory($startDirectory, $codeWarning);

			return $issuesList;
		} else {
			throw new Exception("Nor folder or git repository was provided to search.");
		}
	}

	/* ------------------------------------------------------------------------------------------------------------- */

	/**
	 * Core part of checking issues and matching rules
	 *
	 * @param $option Options User defined options
	 * @param $fileCodeList string[] List of code and filename provided by git shell command
	 * @param $codeWarning CodeWarning[] List of issues to check
	 *
	 * @return Issue[] List of issues found
	 */
	private function coreRun($option, $fileCodeList, $codeWarning) {

		if (!isset($option)) {
			throw new Exception("Invalid option");
		}

		$coreRunResult = new CoreRunResult();

		# Check for warning/alarm code
		$validExtensions = getValidTextFiles();

		// List of Issues (Possbile return)
		$issuesList = array();

		// List with diff (when SHOW option is enabled)
		$diffList = array();

		foreach ($fileCodeList as $fileCode) {
			# Get filename
			$filename = explode("\n", $fileCode);
			$filename = trim($filename[0]);
			$ext      = extension($filename);

			if (validFileToSearch($filename) && !isTestFolder($filename)) {
				$fileDiff = new FileDiff();

				if ($option->getShow() && $option->checkFilter($filename)) {
					# Show filename
					$fileDiff->setFilename($filename);
				}

				// TODO: Do not check for test folders
				$fileCode = explode("diff --git", $fileCode);

				$lines = explode("\n", $fileCode[0]);
				
				// Exclude line for filenames;
				array_shift($lines);

				$textDiff = new TextDiff();
				$textDiff->setText(implode("\n", $lines));

				$fileDiff->addTextDiff($textDiff);

				# Only check added lines
				foreach ($lines as $line) {
					if (substr($line, 0, 1) == "+" && substr($line, 0, 3) != "+++") {
						# Ignore (ignored: css,less)
						if ($option->getShow() &&
							$option->checkFilter($filename) &&
							!in_array($ext, array('css', 'less'))) {
							# Print changed line, without the + (added)
							//echo substr($line, 1, strlen($line))."\n";
						}

						# Check all types of warnings for each line
						foreach ($codeWarning AS $code) {
							// Check if diff have warning code
							if ($code->isDisabled() == false && in_array($ext, $validExtensions)) {
								if ($code->checkExtension($ext) && $code->analyzeLine($line)) {

									$issue = new Issue();
									$issue->setCodeWarning($code);
									$issue->setFilename($filename);
									$issue->setLine(substr($line, 1, strlen($line)));

									$issuesList[] = $issue;

									# Option only available with CLI
									if ($option->getShow() && $option->checkFilter($filename)) {
										//printIssue($issue);
									}
								}
							}
						}
					}
				}

				$diffList[] = $fileDiff;
			}
		}

		$coreRunResult->setDiffs($diffList);
		$coreRunResult->setIssues($issuesList);

		return $coreRunResult;
	}
}
?>