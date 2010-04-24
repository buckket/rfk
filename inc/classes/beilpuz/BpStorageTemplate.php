<?php
/**
 * @package Beilpuz
 */
include('BpStorageSignature.php');

/**
 * Compiled template output.
 * Saves the compiled data to the compiled or cached template file.
 * @author Mike Reiche
 * @version $Revision: 50 $ $Date: 2009-09-07 17:29:32 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
class BpStorageTemplate extends BpStorageSignature {
	protected $hasParent=false;
	/**
	 * Writes the compiled data and signatures into files.
	 * @throws ErrorException If the compiled template could not be written.
	 */
	public function store(&$data) {
		$header = '';
		$this->createParentDir($this->template->compiledFile);
		// Inline templates dont compile any signature or library information
		if ($this->hasParent === false && empty($this->template->s) === false) {
			$this->storeSignatures($this->template->s);
			$header = Beilpuz::PHP_BEGIN . 'if($this->s===null)include(\''.$this->template->compiledFile.self::SIG_CACHE_SUFFIX.'\');'.Beilpuz::PHP_END;
		}
		// Saving compiled template
		if (@file_put_contents($this->template->compiledFile, $header.$data) === false) {
			throw new ErrorException('Cant save compiled template \''.$this->template->compiledFileFile.'\'. Please check permissions.');
		}
	}
	/**
	 * Cuts the signature include header from the compiled template file.
	 * @return Compiled template data without header.
	 */
	public function cutHeader() {
		$templateCode = file_get_contents($this->template->compiledFile);
		if (strpos($templateCode,Beilpuz::PHP_BEGIN.'if($this->s')===0) {
			$readOffset = strpos($templateCode, Beilpuz::PHP_END)+strlen(Beilpuz::PHP_END);
			$templateCode = mb_strcut($templateCode, $readOffset);
		}
		return $templateCode;
	}
}
?>
