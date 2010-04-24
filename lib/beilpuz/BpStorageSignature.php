<?php
/**
 * @package Beilpuz
 */
/**
 * Signature compiler.
 * Compiles and stores signatures into a separated cache file.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
class BpStorageSignature {
	/**
	 * Suffix for signature cache files.
	 */
	const SIG_CACHE_SUFFIX='#sigc';
	protected $template=null;
	protected static $basePath;
	/**
	 * Static init method will be called on inclusing.
	 */
	public static function init() {
		self::$basePath = dirname(__FILE__);
	}
	/**
	 * Creates a new BpStorageSignature instance
	 * @param BpITemplate $template According template for this compiler.
	 */
	public function __construct(BpITemplate $template) {
		$this->template = $template;
	}
	protected function createParentDir(&$targetFile) {
		// Save compiled
		$parentDir = dirname($targetFile);
		// Create compiled template directory with parents
		if (is_dir($parentDir) === false) {
			mkdir($parentDir, 0775, true);
		}
	}
	/**
	 * Serializes the signatures of this template, searches all used signature handlers and stores them
	 * into the signature cache file.
	 * @throws ErrorException If the cache file could not be written.
	 */
	public function storeSignatures() {
		$output = Beilpuz::PHP_BEGIN;
		$output .= 'include_once(\''.self::$basePath.'/BpSignatureHandler.php\');';
		$included = array();
		foreach ($this->template->s as $sigNum => $sig) {
			if (isset($included[$sig->n])===false) {
				foreach (Beilpuz::$sigClassPathes as $path => $useless) {
					$lib = $path.'/'.BpSignature::CLASS_PREFIX.$sig->n.'.php';
					if (file_exists($lib)) {
						$included[$sig->n] = true;
						$output .= 'include_once(\''.$lib.'\');';
						break;
					}
				}
			}
		}
		$output .= '$this->s=unserialize(\''.str_replace('\'','\\\'',serialize($this->template->s)).'\');'.Beilpuz::PHP_END;
		$fileName = $this->template->compiledFile.self::SIG_CACHE_SUFFIX;
		if (@file_put_contents($fileName, $output) === false) {
			throw new ErrorException('Cant save compiled signatures \''.$fileName.'\'. Please check permissions.');
		}
	}
}
BpStorageSignature::init();
?>