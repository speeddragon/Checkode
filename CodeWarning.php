<?php
	/**
	 * Class with rules information
	 */
	class CodeWarning {
		/** 
		 * @var string 
		 */
		protected $_extension;

		/** 
		 * @var string 
		 */
		protected $_expression;

		/** 
		 * @var string 
		 */
		protected $_information;
		
		/** 
		 * @var string 
		 */
		protected $_solution;

		/** 
		 * @var int 
		 */
		protected $_warningLevel;
		
		/** 
		 * @var bool
		 */
		protected $_ignore;

		/** 
		 * @var bool 
		 */
		protected $_disable = false;

		public function __construct($rule) {
			if (is_object($rule)) {
				$this->_extension = $rule->extension;
				$this->_expression = $rule->expression;
				$this->_information = $rule->information;
				$this->_solution = $rule->solution;
				$this->_warningLevel = $rule->warning_level;
				
				// Not mandatory
				if (isset($rule->ignore)) {
					$this->_ignore = $rule->ignore;
				}

				// Not mandatory
				if (isset($rule->disable)) {
					$this->_disable = $rule->disable;
				} else {
					$this->_disable = false;
				}
			}
		}

		public function getExpression() {
			return $this->_expression;
		}

		public function getExtension() {
			return $this->_extension;
		}

		public function getInformation() {
			return $this->_information;
		}

		public function getSolution() {
			return $this->_solution;
		}

		public function setSolution($solution) {
			$this->_solution = $solution;
		}

		public function getWarningLevel() {
			return $this->_warningLevel;
		}

		public function isDisabled() {
			return $this->_disable === true;
		}

		/**
		 * Check if the extension is a match for the rule
		 *
		 * @param string $extension
		 * @return bool
		 */
		public function checkExtension($extension) {
			if (is_array($this->getExtension()) && in_array($extension, $this->getExtension())) {
				return true;
			} else if (is_string($this->getExtension()) && $this->getExtension() == $extension) {
				return true;
			} else if ($this->getExtension() == false) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Analyze line code
		 *
		 * @param $line string
		 * @return bool
		 */
		public function analyzeLine($line) {

			// fnmatch(): Filename exceeds the maximum allowed length of 1024
			$lineSplit = array();
			$lineCut = $line;
			$lineStart = 0;

			// Cut in chucks of 1000 bytes
			do {
				$lineSplit[] = substr($lineCut, $lineStart, 1000);
				$lineStart += $lineStart + 1000; 
				$lineCut = substr($lineCut, $lineStart);
			} while (strlen($lineCut) > 1000); 
			
			foreach($lineSplit as $readLine) {
				if (fnmatch('*' . $this->_expression . '*', $readLine)) {
					// Remove false positives
					if (is_array($this->_ignore)) {
						foreach($this->_ignore as $exp) {
							if (fnmatch('*' . $exp . '*', $readLine)) {
								return false;
							}
						}
					}

					return true;
				}
			}

			return false;
		}
	}