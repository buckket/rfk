<?php
/**
 * @package Beilpuz
 */
include('BpTemplateAbstract.php');
/**
 * Template with the capability to store the compiled data.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @since 0.3
 */
abstract class BpTemplateStorable extends BpTemplateAbstract implements BpITemplate {
	/**
	 * @ignore
	 */
	public $compiledFile;
	/**
	 * Specifies if this templates was compiled or not.
	 * @var Boolean
	 */
	protected $compiled = false;
	/**
	 * According compiler instance.
	 * @var BpCompiler
	 */
	protected $compiler = null;
	/**
	 * Compiles raw template data sets the compiled flag to true.
	 * @param String $content
	 * @ignore
	 */
	public function compile(&$content, BpCompiler $parent=null) {
		$this->compiler = parent::compile($content,$parent);
		$this->compiledFile = Beilpuz::$compiled.'/'.$this->name;
		// Retrieve the signatures from compiler
		if (empty(BpCompiler::$sigCache[$this->name])===false) {
			$this->s = BpCompiler::$sigCache[$this->name];
			// Clear the compiler cache
			unset(BpCompiler::$sigCache[$this->name]);
		}
		// Store the compiled template on filesystem
		$this->compiler->store(BpCompiler::$tplCache[$this->name]);
		unset(BpCompiler::$tplCache[$this->name]);
		$this->compiled = true;
	}
	public function render() {
		if ($this->compiled === false) $this->checkExistence();
		ob_start();
		include($this->compiledFile);
		if ($this->update===true) $this->updateSignatures();
		return ob_get_clean();
	}
	/**
	 * Updates the signatures.
	 */
	protected function updateSignatures() {
		if ($this->compiler===null) {
			include_once('BpStorageSignature.php');
			$this->compiler = new BpStorageSignature($this);
		}
		$this->compiler->storeSignatures();
		$this->update = false;
	}
	/**
	 * @ignore
	 */
	public function __sleep() {
		return array('name');
	}
	/**
	 * Checks the existence of the compiled template file.
	 */
	protected function checkExistence() {
		$templateFile = Beilpuz::$templates.'/'.$this->name;
		$this->compiledFile = Beilpuz::$compiled.'/'.$this->name;
		if (@filemtime($templateFile) > @filemtime($this->compiledFile)) {
			$this->compile(file_get_contents($templateFile));
		} else {
			$this->compiled = true;
		}
	}
	public function getCompiledString() {
		return file_get_contents($this->compiledFile);
	}
 }
?>
