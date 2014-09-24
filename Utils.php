<?php
	/**
	 * Get list of rules
	 * @return CodeWarning[]
	 */
	function getWarningCode() {
		$codeWarningList = array();

		// Load rules by json file
		if (DEBUG) {
			echo "Loading Rules ... \n";
		}

		// Setup Folder
		$rulesPath = "";

		if (defined('RULES_PATH')) {
			$rulesPath = RULES_PATH;
		}

		$rulesPath = $rulesPath . 'rules/';

		if (!file_exists($rulesPath)) {
			throw new Exception("Rules folder not found: " . $rulesPath);
		}

		// Loading
		foreach (new DirectoryIterator($rulesPath) as $fileInfo) {
		    if($fileInfo->isDot()) 
		    	continue;
			
			if (file_exists($fileInfo->getPathname())) {
				$json = json_decode(file_get_contents($fileInfo->getPathname()));

				if (DEBUG) {
					echo 'Loading ... ' . $json->name . "\n";
				}

				foreach($json->rules as $rule) {
					$codeWarningList[] = new CodeWarning($rule);
				}
			}
		}

		return $codeWarningList;
	}

	/**
	 * Get list of files that should throw a warning when change
	 * NOTE: Not functional yet!
	 * @return FileWarning[]
	 */
	function getFileWarnings() {
		$fileWarnings = array();
		
		// TODO: Change this to read from a file ...
		$fileWarnings[] = new FileWarning("UrlBuilder.java");

		return $fileWarnings;
	}

	/**
	 * Read options from command line
	 * @param $argv string[] Command line input
	 * @return Options
	 */
	function extractOptions($argv) {
		$options = new Options();

		$optns = $argv;

		// Remove the file name
		array_shift($optns);

		$others = array();
		for ($i = 0; $i < count($optns); $i++) {
			switch($optns[$i]) {
				case '--op': {
					$options->setOperation($optns[++$i]);
					break;
				}
				case '--help': {
					$options->setHelp(true);
					break;
				}
				case '--repo': {
					$options->setRepository($optns[++$i]);
					break;
				}
				case '--folder': {
					$options->setFolder($optns[++$i]);
					break;
				}
				case '--show': {
					$options->setShow(true);
					break;
				}
				case '--filter': {
					$options->setFilterFolder($optns[++$i]);
					break;
				}
				case '--url': {
					$options->setUrl($optns[++$i]);
					break;
				}

				default: {
					$others[] = $optns[$i];
				}
			}
		}

		$options->setOthers($others);

		return $options;
	}

	/**
	 * Get the extension of a file
	 * @param $filename 
	 * @return string File extension
	 */
	function extension($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * List of extension of files to check for issues
	 * @return string[] Extensions of files
	 */
	function getValidTextFiles() {
		return array("ftl", "java", "yml", "html", "css", "less", "js", "xml");
	}

	/**
	 * Folders to ignore
	 *
	 * Normaly test folders and code that don't have implication into production environments
	 * @return string[]
	 */
	function getTestFolders() {
		return array("test", "tests");
	}

	function validFileToSearch($filename) {
		$validExtensions = getValidTextFiles();
		$ext = extension($filename);

		if (in_array($ext, $validExtensions)) {
			return true;
		} else {
			return false;
		}
	}

	function isTestFolder($filename) {
		$testFolders = getTestFolders();
		foreach($testFolders as $folder) {
			if (strpos(strtolower($filename), '/' . $folder . '/') !== false) {
				return true;
			}
		}

		return false;
	}

	function searchInText($filename, $codeWarnings) {
		$ext = extension($filename);

		$issuesCount = 0;

		if (validFileToSearch($filename)) {
			$lineCount = 0;
			$file_handle = fopen($filename, "r");
			while (!feof($file_handle)) {
				$lineCount++;
			   	$line = fgets($file_handle);

			   	// Check
			   	foreach($codeWarnings as $codeWarning) {
			   		if ($codeWarning->checkExtension($ext) && strpos(strtolower($line), strtolower($codeWarning->getCode())) !== false) {
			   			$issuesCount++;
			   			echo $filename . ':' . $lineCount . " [".$codeWarning->getCode()."]" . "\n";
			   		}
			   	}
			}
			fclose($file_handle);		
		}

		return $issuesCount;
	}

	function searchInDirectory($startDirectory, $codeWarnings) {
		$issuesCount = 0;

		if (!file_exists($startDirectory)) {
			throw new Exception("Directory not found: " . $startDirectory);
		}

        foreach (new DirectoryIterator($startDirectory) as $fileInfo) 
        {
            if($fileInfo->isDot()) 
            {
            	continue;
           	} 
           	else if($fileInfo->isDir() && !isTestFolder($fileInfo->getPath())) 
            {
                $issuesCount += searchInDirectory($fileInfo->getPath() . '/' . $fileInfo->getFilename(), $codeWarnings);
            }
            else if($fileInfo->isFile()) 
            {
                // Read file
                $issuesCount += searchInText($fileInfo->getPath() . '/' . $fileInfo->getFilename(), $codeWarnings);
            }
        }   

        return $issuesCount;    
	}
?>