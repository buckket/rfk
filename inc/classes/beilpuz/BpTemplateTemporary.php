<?php
/**
 * @package Beilpuz
 */
include('BpTemplateAbstract.php');
include('BpCompiler.php');
/**
 * Template for runtime use only.
 * This class cannot store the compiled template on filesystem. You should enable this
 * class by using the {@link Beilpuz::enableTemporary()} method BEFORE instancing a new {@link BpTemplate}.
 * Use this class when you dont need precompiled templates for less runtime.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
final class BpTemplate extends BpTemplateAbstract implements BpITemplate {
	public function render() {
		// Compile the first and last time
		if (isset(BpCompiler::$tplCache[$this->name])===false) {
			$this->compile(file_get_contents(Beilpuz::$templates.'/'.$this->name));
			// Retrieve the signatures from compiler
			$this->s = &BpCompiler::$sigCache[$this->name];
		}
		ob_start();
		if (@eval (Beilpuz::PHP_END.BpCompiler::$tplCache[$this->name].Beilpuz::PHP_BEGIN)===false) {
			throw new ErrorException('Could not parse template \''.getcwd().'/'.Beilpuz::$templates.'/'.$this->name);
		}
		return ob_get_clean();
	}
	public function getCompiledString() {
		return BpCompiler::$tplCache[$this->name];
    }
}
?>