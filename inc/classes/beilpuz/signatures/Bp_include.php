<?php
/**
 * Includes another template.
 * @author Mike Reiche
 * @version $Revision: 46 $ $Date: 2009-09-05 16:07:15 +0200 (Sa, 05. Sep 2009) $
 * @since 0.3
 * @package Beilpuz
 * @subpackage Signatures
 */
class Bp_include extends BpSignatureHandler {
	public static $requiredAttributes = array('template'=>true);
	public static function render(BpSignature $sig, BpITemplate $template) {
		if($sig->a['var'] === true){
			$includeTemplate = new BpTemplate($template->v[$sig->a['template']]);
		}else{
			$includeTemplate = new BpTemplate($sig->a['template']);
		}
		if ($sig->a['shared_values'] === true) {
			$includeTemplate->v = &$template->v;
		}
		return $includeTemplate->render();
	}
}
?>