<?php
/**
 * @package Beilpuz
 */
include('BpTemplateStorable.php');
/**
 * Template which can create cache files.
 * The class will be activated if you call {@Beilpuz::enableCachingWithKeys()) BEFORE instancing a new BpTemplate instance.
 * @author Mike Reiche
 * @version $Revision: 50 $ $Date: 2009-09-07 17:29:32 +0200 (Mo, 07. Sep 2009) $
 * @since 0.3
 */
final class BpTemplate extends BpTemplateStorable {
	private $sigCache;
	private $exists = false;
	public function render() {
		if ($this->compiled === false) {
			$this->compiledFile = Beilpuz::$cache.'/'.$this->name.Beilpuz::$loadCacheID;
			if (@filemtime($this->compiledFile) > @filemtime(Beilpuz::$templates.'/'.$this->name)) {
				$this->compiled = $this->exists = true;
			} else {
				$this->sigCache = array();
				$this->checkExistence();
			}
		}
		ob_start();
		include($this->compiledFile);
		if ($this->exists === false &&
			empty(Beilpuz::$allowedCacheKeys)===false &&
			empty($this->sigCache) === false) $this->createCacheFile();
		else if ($this->update===true) $this->updateSignatures();
		return ob_get_clean();
	}
	/**
	 * Collects the template's signature handler output.
	 * @param String $sigOutput Signature handler output.
	 * @param Integer $sigNum Signature index number in this template.
	 * @return String Signature handler output.
	 */
	protected function co(&$sigOutput,$sigNum) {
		// Dont catch output if caching has been disabled
		$this->sigCache[$sigNum] = $sigOutput;
		return $sigOutput;
    }
	/**
	 * Saves the collected output to a cache file.
	 * @throws ErrorException If the cache file could not be written.
	 */
	private function createCacheFile() {
		// Create compiler instance if not exists already
		if ($this->compiler===null) {
			include_once('BpStorageTemplate.php');
			$this->compiler = new BpStorageTemplate($this);
		}
		$cacheOutput = $this->compiler->cutHeader();
		// Replace all cachable signatures by their output
		foreach ($this->sigCache as $sigNum => $sigOutput) {
			$needle = Beilpuz::PHP_BEGIN.'echo $this->co('.BpSignature::CLASS_PREFIX.$this->s[$sigNum]->n.'::render($this->s['.$sigNum.'],$this),'.$sigNum.');'.Beilpuz::PHP_END;
			$cacheOutput = str_replace($needle, $sigOutput, $cacheOutput);
			$needle = Beilpuz::PHP_BEGIN.'echo '.BpSignature::CLASS_PREFIX.$this->s[$sigNum]->n.'::render($this->s['.$sigNum;
			// If the same signature (but uncached) couldnt be found, remove the signature finally.
			if (strpos($cacheOutput, $needle) === false) {
				unset($this->s[$sigNum]);
            }
			// Clear cache output
			$this->sigCache[$sigNum] = null;
		}
		$this->compiledFile = Beilpuz::$cache.'/'.$this->name.Beilpuz::saveCacheID();
		$this->compiler->store($cacheOutput);
    }
 }
?>
