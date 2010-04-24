<?php
/**
 * Renders the inner template in a loop.
 * Assigns the current array element and its index to its inner template.
 * @author Mike Reiche
 * @version $Revision: 46 $ $Date: 2009-09-05 16:07:15 +0200 (Sa, 05. Sep 2009) $
 * @package Beilpuz
 * @subpackage Signatures
 * @since 0.3
 */
class Bp_foreach extends BpSignatureHandler {
	public static $requiredAttributes = array('in'=>true,'as'=>true);
	public static $directAllowed = true;
	public static function direct(BpSignature $sig, BpITemplate $template) {
		$render = Beilpuz::PHP_BEGIN.'foreach ('.BpTemplate::compileValueString($sig->a['in']).' as '.(isset($sig->a['index']) ? '$this->v[\''.$sig->a['index'].'\'] => ':'').'$this->v[\''.$sig->a['as'].'\']){'.Beilpuz::PHP_END;
		$render .= $sig->tpl->getCompiledString();
		$render .= Beilpuz::PHP_BEGIN.'}'.Beilpuz::PHP_END;
		return $render;
	}
	public static function render(BpSignature $sig, BpITemplate $template) {
		$array = $template->findValue($sig->a['in']);
		$addIndex = false;
		if (isset($sig->a['index'])) $addIndex = true;
		$render = '';
		$as = $sig->a['as'];
		foreach ($array as $key=>$value) {
			$sig->tpl->v[$as] = $value;
			if ($addIndex) $sig->tpl->v[$sig->a['index']] = $key;
			$render .= $sig->tpl->render();
		}
		return $render;
	}
}
?>