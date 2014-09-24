<?php
	/*
		Command Line Interface (CLI)
	*/

	define("DEBUG", true);
	
	require_once('API.php');

	include('Colors.php');

	$colors = new Colors();
	
	function printIssue($issue) {
		global $colors;

		echo $colors->getColoredString("::: New issue: " . $issue->getFilename() . ' [ Type: ' . $issue->getCodeWarning()->getCode() . " ]" , "purple", "yellow") . "\n";
	}
	/**
	 * Print issues to console
	 */
	function printIssues($issuesList) {
		foreach($issuesList as $issue) {
			printIssue($issue);
		}
	}

	if (count($argv) > 0) {
		echo "=================================\n";
		echo "===      Check Code v1.0      ===\n";
		echo "=================================\n\n";
	}

	if (count($argv) == 1) {
		echo "Please use: ./gif.sh --help\n";
		return;
	}

	# Core
	$api = new API();

	# Parse options
	$option = extractOptions($argv);
	
	if ($option->getHelp()) {
		/**
		 * Options how to use this script
		 */

		echo "\nOptions: \n\n";
		echo "  --show 				Show more details\n";
		echo "  --op <operation>		Do some operation\n";
		echo "  --repo <path> 		Repository path\n";
		echo "  --url <url>			URL with git hash\n";
		echo "  --folder <path>		Folder path\n";
		echo "  --help 				Show this menu\n";

		echo "\nOperations: \n\n";
		echo "  commit - Search in a single commit\n";
		echo "  changes - Search only in the changes between two commits (--show to list all added lines)\n";
		echo "  files - List files warnings changed between two commits (--show to list all)\n";
		echo "  search - Search in all repository/folder for code warnings.\n";

		echo "\n\nExemple: \n\n";

		echo "git.sh --op changes --repo <repository> <hash1> <hash2>\n";
		echo "git.sh --op files <hash1> <hash2> [--show]\n";
		echo "git.sh --op changes --repo <repository> --url <url>";

		echo "\n";

		return;
	}

	if ($option->getOperation()) {
		switch($option->getOperation()) {

			case "commit": {
				$coreRunResult = $api->commit($option);

				printIssues($coreRunResult->getIssues());

				break;
			}

			/**
			 * Get change list between two commits hash (GIT)
			 */
			case "changes": {
				$coreRunResult = $api->changes($option);
				
				if (!$option->getShow()) {
					printIssues($coreRunResult->getIssues());
				}

				break;
			}

			/**
			 * Get list of modified files between two commits hash (GIT)
			 */
			case "files": {
				$fileList = $api->files($option);
				
				foreach($fileList as $file) {
					echo $file . "\n";
				}

				break;
			}

			/**
			 * Search inside a folder
			 */
			case "search": {
				$issuesList = $api->search($option);

				echo "Finish the search! Total of " . count($issuesList) . " found!\n";

				break;
			}

			/**
			 * Default option
			 */
			default: {
				echo "Option: '" . $option . "' not found!, please use --help\n";
				break;
			}
		}
	} else {
		echo "No operation selected\n";
		return 1;
	}

	return 0;
?>