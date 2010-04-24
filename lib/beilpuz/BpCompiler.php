<?php
/**
 * @package Beilpuz
 */
include_once('BpSignatureHandler.php');
include('BpStorageTemplate.php');
/**
 * Template Compiler.
 * Stores the compiled templates in a global array for later access.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.2
 */
final class BpCompiler extends BpStorageTemplate {
/**
 * Signature with inline template.
 */
	const SIG_TYPE_OPEN = 2;
	/**
	 * Closing signature tag.
	 */
	const SIG_TYPE_CLOSE = 3;
	/**
	 * Tag without inner template.
	 */
	const SIG_TYPE_SELF = 1;
	/**
	 * Template value only.
	 */
	const SIG_TYPE_VALUE = 0;
	/**
	 * Quote char for signature attributes
	 */
	const QUOTE_CHAR = '"';
	/**
	 * Contains all compiled templates.
	 * @var Array
	 */
	public static $tplCache = array();
	/**
	 * Contains all compiled signatures.
	 * @var Array
	 */
	public static $sigCache = array();
	/**
	 * Container for BpSignature instances.
	 * @var Array
	 */
	protected $sigs = null;
	/**
	 * Container for unique signature numbers.
	 * @var Array
	 */
	protected $sigKeys = null;
	/**
	 * Count of unique signatures.
	 * @var Integer
	 */
	protected $uniqueSigs;
	/**
	 * Count of inline templates.
	 * @var Integer
	 */
	protected $inlineTpls;
	private static $SIG_BEGIN_LEN= 0;
	private static $SIG_END_LEN = 0;
	private static $SIG_CLOSE_LEN = 0;
	private static $lastErrorMsg = '';
	
	public static function init() {
		self::$SIG_BEGIN_LEN = strlen(Beilpuz::$SIG_BEGIN);
		self::$SIG_END_LEN = strlen(Beilpuz::$SIG_END);
		self::$SIG_CLOSE_LEN = strlen(Beilpuz::$SIG_CLOSE);
		Beilpuz::addSignatureClassPath(self::$basePath.'/signatures');
		spl_autoload_register(array(BpCompiler,'loadSignatureHandler'));
	}

	public static function loadSignatureHandler($className) {
		// Load signature handler class
		if (strpos($className,'Bp_')!==false) {
			$className.='.php';
			foreach (Beilpuz::$sigClassPathes as $path => $useless) {
				$lib = $path.'/'.$className;
				if (file_exists($lib)) {
					include($lib);
					break;
				}
			}
		}
	}

	/**
	 * Creates a new BpCompiler instance
	 * @param BpTemplate $template According template.
	 * @param BpCompiler $parentCompiler Parent compiler.
	 */
	public function __construct(BpITemplate $template, BpCompiler $parent=null) {
		parent::__construct($template);
		if ($parent!==null) {
			$this->hasParent = true;
			$this->uniqueSigs = &$parent->uniqueSigs;
			$this->inlineTpls = &$parent->inlineTpls;
			$this->sigs = &$parent->sigs;
			$this->sigKeys = &$parent->sigKeys;
		} else {
			$this->sigs = array();
			$this->sigKeys = array();
			$this->uniqueSigs = $this->inlineTpls = 0;
		}
		// Save the signatures for global access in BpTemplate.render()
		self::$sigCache[$template->name] = &$this->sigs;
	}

	/**
	 * Compiles raw template data.
	 * @param String $rawTemplateData Raw template data string to compile.
	 * @throws BpCompilerException On any compiler error.
	 */
	public function compileString(&$rawTemplateData) {
		$pos = 0;	// Current parser position
		$start = 0;	// Start position of a found signature string
		$end = 0;	// End position of a found signature string
		$sigString = '';	// Found signature string
		$sigKey = '';	// Unique signature key
		$sigType = 0;	// Type of a signature
		$sigLevel = 0;	// Current level of an signature
		$closingSigString = '';	// Closing string of a found signature
		$closingSigStringLen = 0;	// Length of the closing signature string
		$rawInlineDataStartpos = 0;	// Start position of the inline template data
		$rawInlineDataEndpos = 0;	// End position of the inline template data
		$rawInlineData = '';	// Raw template data of an inline template
		$compiledData = '';	// Compiled template data string
		$templateSizeBytes = strlen($rawTemplateData);	// Current template size in bytes
		$tpl = null;	// The inline template object
		$directCompiler = null;	// Compiler for direct rendering signatures
		$compiledSig = '';
		$direct = false;
		$cacheable = false;
		while (1) {
			$pos = $end;
			$start = self::getNextSignature($rawTemplateData, $sigString, $end);
			if ($end === false) $this->compileError($end, $rawTemplateData);
			else if ($start === false) {
				// Keine Signatur mehr gefunden, beende Parservorgang.
					$compiledData .= substr($rawTemplateData, $pos, ($templateSizeBytes-$pos));
					break;
				}
			// Speichere Template-Inhalt bis zur Signatur in den Zwischenspeicher
			$compiledData .= substr($rawTemplateData, $pos, ($start-$pos));
			$sigType = self::signatureType($sigString);
			// Value found
			if ($sigType === self::SIG_TYPE_VALUE) {
				$sigString = mb_strcut ($sigString, 1, strlen($sigString)-1);
				$compiledData .=  Beilpuz::PHP_BEGIN.'echo '.BpTemplate::compileValueString($sigString).Beilpuz::PHP_END;
			// Signature found
			} else {
				$sig = new BpSignature(null);
				if (self::parseSignature($sig, $sigString) === false) $this->compileError($end, $rawTemplateData);
				$className = BpSignature::CLASS_PREFIX.$sig->n;
				if ((eval('return '.$className.'::$directAllowed;')===true && (isset($sig->a['direct']) && $sig->a['direct']===true))
				|| eval('return '.$className.'::$directOnly;')===true) $direct = true;
				else $direct = false;

				// Find inline template
				if ($sigType == self::SIG_TYPE_OPEN) {
					$sigLevel = 0;
					$closingSigString = Beilpuz::$SIG_CLOSE.$sig->n;
					$closingSigStringLen = strlen($closingSigString);
					$rawInlineDataStartpos = $end;
					$rawInlineDataEndpos = 0;
					while (1) {
					// Save the beginning index for second search
						$begin = $end;
						// First search: any next signature.
						$start = self::getNextSignature($rawTemplateData, $sigString, $end, $sig->n);
						// Second search: the next closing signature.
						$rawInlineDataEndpos = strpos($rawTemplateData, $closingSigString, $begin);
						if ($start !== false && ($start < $rawInlineDataEndpos)) {
							$sigType = self::signatureType($sigString);
							// Wenn eine öffnende Signatur mit selben Namen gefunden wurde, überspringe sie
							if ($sigType == self::SIG_TYPE_OPEN) {
								$sigLevel++;
							}
						}
						if ($rawInlineDataEndpos >= 0 && $sigLevel > 0) {
							$sigLevel--;
							// Überspringe Signaturenende
							$end = $rawInlineDataEndpos + $closingSigStringLen;
						} else if ($rawInlineDataEndpos === false) {
								self::$lastErrorMsg = 'Signature \'' . $sig->n . '\' not closed.';
								$this->compileError($end, $rawTemplateData);
							} else {
								break;
							}
					}

					$rawInlineData = substr($rawTemplateData, $rawInlineDataStartpos, ($rawInlineDataEndpos-$rawInlineDataStartpos));
					$tpl = new BpTemplate();
					$tpl->name = $this->template->name.'#s'.($this->inlineTpls++);
					if ($direct===true) $tpl->compile($rawInlineData, $this);
					else $tpl->compile($rawInlineData);
					$sig->tpl = $tpl;
					// Überspringe Signaturenende
					$end = $rawInlineDataEndpos + $closingSigStringLen;
					// Finde abschließendes >
					$end = strpos($rawTemplateData, Beilpuz::$SIG_END, $end)+self::$SIG_END_LEN;
				}
				
				// If signature wants to be called directly
				if ($direct===true) $compiledData .= call_user_func(array($className,'direct'),$sig,$this->template);
				else {
					$cacheable = false;
					if (isset($sig->a['cacheable'])) {
						$cacheable = $sig->a['cacheable'];
						unset($sig->a['cacheable']);
					}
					/**
					 * Make the signature unique to avoid duplicates and useless overhead in the compiled file.
					 * @todo Make the signature instance unique.
					 */
					$sigNum = $this->makeUnique($sig);
					$compiledSig = $className.'::render($this->s['.$sigNum.'],$this)';

					// Write compiled signature
					$compiledData .= Beilpuz::PHP_BEGIN.'echo ';
					if ($cacheable===true) {
						$compiledData .= '$this->co('.$compiledSig.','.$sigNum.');';
					} else {
						$compiledData .= $compiledSig.';';
					}
					$compiledData .= Beilpuz::PHP_END;
				}
			}
		}
		self::$tplCache[$this->template->name] = $compiledData;
	}

	/**
	 * Returns the number of the unique signature.
	 * @param BpSignature $sig Signature to check.
	 * @return Integer Number of the unique signature.
	 */
	private function makeUnique(BpSignature $sig) {
		$keyString = $sig->n;
		if (is_array($sig->a)) {
			// Sort all attributes by key name
			ksort($sig->a);
			foreach ($sig->a as $key => $value) {
				$keyString .= $key . '=' . $value;
			}
		}
		/**
		 * @todo To use the template name is not correct, use the templates content instead.
		 */
		if ($sig->tpl !== null) {
			$keyString .= $sig->tpl->name;
		}
		$sigKey = md5($keyString);
		// Add signature if not exists
		if (isset($this->sigKeys[$sigKey]) === false) {
			$sigNum = ($this->uniqueSigs++);
			$this->sigKeys[$sigKey] = $sigNum;
			$this->sigs[$sigNum] = $sig;
		}
		return $this->sigKeys[$sigKey];
	}
	/**
	 * Finds the next signature in the template data.
	 * @param String $content Raw template data.
	 * @param String $sigString Target to save the found signature string.
	 * @param Integer $index Start index for the search. Replaced by the end index of the found signature.
	 * @param String $sigName Signature name to find (optional).
	 * @return Integer Start index of the found signature.
	 */
	private static function getNextSignature(&$content, &$sigString, &$index, $sigName='') {
		$begin = strpos($content, Beilpuz::$SIG_BEGIN.$sigName, $index);
		if ($begin === false) return false;
		$startpos = $begin;	// Startposition der Signatur
		$index = self::unquotedStringPos($content, Beilpuz::$SIG_END, $begin);
		// Kein Signaturenende gefunden -> Fehler
		if ($index === false) return false;
		$begin += self::$SIG_BEGIN_LEN;
		$sigString = substr($content, $begin, ($index-$begin));
		$index += self::$SIG_END_LEN;	// Endpunkt der Signatur
		return $startpos;
	}

	/**
	 * Parses the attributes and properties of the signature.
	 * @param BpSignature $sig Signature to fill.
	 * @param signature_string Signature string to parse (like '<bp:signature option="value"/>')
	 * @return Boolean True if its a valid signature.
	 */
	private static function parseSignature(BpSignature $sig, &$sig_string) {
		$pos = 0;
		$equal_pos = 0;	// Position eines Istgleich (=)
		$attrib = '';
		$value = '';
		$value_start = 0;
		$value_end = 0;

		$pos = strpos($sig_string, ' ');
		// Keine weiteren Optionen
		if ($pos === false) {
			$sig->n = $sig_string;
			return true;
		}
		$sig->n = substr($sig_string, 0, $pos);
		$pos++;
		// Required attributes
		$required = eval('return '.BpSignature::CLASS_PREFIX.$sig->n.'::$requiredAttributes;');
		while (1) {
			$equal_pos = strpos($sig_string,'=',$pos);
			if ($equal_pos === false) break;
			$attrib = ltrim(substr($sig_string,$pos,($equal_pos-$pos)));
			$value_start = strpos($sig_string, self::QUOTE_CHAR, $equal_pos);
			if ($value_start === false) return false;
			$value_start++;
			$value_end = strpos($sig_string, self::QUOTE_CHAR, $value_start);
			if ($value_end === false) return false;
			$value = substr($sig_string,$value_start, ($value_end-$value_start));
			switch (strtolower($value)) {
				case 'yes':
				case 'true': $value = true; break;
				default: if (is_numeric($value)) $value = floatval($value); break;
			}
			$sig->a[$attrib] = $value;
			// Remove satisfied attribute
			unset($required[$attrib]);
			$pos = $value_end+1;
		}
		// If required attributes not satisfied
		if (empty($required) === false) {
			self::$lastErrorMsg = 'Required attribute ('.join(', ',array_keys($required)).') not defined in signature ' . $sig->n;
			return false;
		}
		return true;
	}

	/**
	 * Finds an end of a string without quote character.
	 * @param String $string Source string.
	 * @param String $search_string String to search for.
	 * @param Integer $begin Beginn offset.
	 * @return Integer Offset position or false if not found.
	 */
	private static function unquotedStringPos(&$string, $search_string, $begin = 0) {
		$end_pos = 0;
		$quote_pos = 0;
		$quoted = false;
		//$quote_begin = $begin;
		while (1) {
			$end_pos = strpos($string, $search_string, $begin);
			$quote_pos = strpos($string, self::QUOTE_CHAR, $begin);
			if (($quote_pos === false || $end_pos === false)) {
				break;
			} else if (($quote_pos > $end_pos) && $quoted == false) {
					break;
				} else if ($quote_pos < $end_pos) {
						if ($quoted == false) $quoted = true;
						else $quoted = false;
					} else if (($quote_pos > $end_pos) || $quoted == true) {
							$quoted = false;
						}
			$begin = $quote_pos+1;
		}
		if ($end_pos === false) return false;
		else return $end_pos;
	}

	/**
	 * Detects the type of an overgiven signature string.
	 * If SIG_TYPE_SELF was found, the end character will be removed.
	 * @param String $sig_string The signature string from a template.
	 * @return Integer Signature type constant.
	 */
	public static function signatureType(&$sig_string) {
		$type = self::SIG_TYPE_OPEN;	// Standard typ
		$len = strlen($sig_string)-1;
		if (substr($sig_string, $len, 1) === '/') {
			$sig_string = mb_strcut ($sig_string, 0, $len);
			# Self
			$type = self::SIG_TYPE_SELF;
		}
		if (strpos($sig_string, '$') === 0) {
			$type = self::SIG_TYPE_VALUE;
		}
		return $type;
	}

	/**
	 * Parses the template content and finds values and signatures.
	 * It also creates a compiled version of the template.
	 */
	private function compileError($offset, &$rawTemplateData) {
		$linepos = 0;
		$linecount = 0;
		while ($linepos < $offset) {
			$linepos = strpos($rawTemplateData, "\n", $linepos);
			if ($linepos === false) break;
			else {
				$linecount++;
				$linepos++;
			}
		}
		$msg = 'Template \'' . $this->template->name.'\' (near line ' . $linecount .')';
		if (empty(self::$lastErrorMsg)) self::$lastErrorMsg='Parser error';
		$msg .= ': '.self::$lastErrorMsg;
		throw new ErrorException($msg);
	}
}
BpCompiler::init();
?>