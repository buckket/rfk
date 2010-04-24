<?php
/**
 * Renders the inner template if the condition is true.
 * If you need a more complex condition structure, its recommended to write your own special signature.
 * @author Mike Reiche
 * @version $Revision: 46 $ $Date: 2009-09-05 16:07:15 +0200 (Sa, 05. Sep 2009) $
 * @since 0.3
 * @package Beilpuz
 * @subpackage Signatures
 */
class Bp_if extends BpSignatureHandler {
/**
 * @ignore
 */
	protected static $switches = array();
	public static $directAllowed = true;
	/**
	 * @ignore
	 */
	protected static function isActive(BpITemplate $tpl) {
		return self::$switches[$tpl->name];
	}
	/**
	 * @ignore
	 */
	protected static function activate(BpITemplate $tpl) {
		self::$switches[$tpl->name] = true;
	}
	public static function direct(BpSignature $sig, BpITemplate $template) {
		if ($sig->tpl === null) return;
		$render = Beilpuz::PHP_BEGIN.'if (';
		if (isset($sig->a['expr'])===true) {
			$render .= BpTemplate::compileExpression($sig->a['expr']);
		} else if (isset($sig->a['value'])===true) {
				$render .= BpTemplate::compileValueString($sig->value);
				if (isset($sig->a['is'])) {
					$compare_value = $sig->a['is'];
					if (is_string($compare_value)===true) $compare_value = '\'' . $compare_value . '\'';
					$render .= '=='.$compare_value;
				}
			} else {
				return;
			}
		$render .= '){'.Beilpuz::PHP_END;
		$render .= $sig->tpl->getCompiledString();
		$render .= Beilpuz::PHP_BEGIN.'}'.Beilpuz::PHP_END;
		return $render;
	}
	public static function render(BpSignature $sig, BpITemplate $template) {
		if ($sig->tpl === null) return;
		$renderTemplate = false;
		$sig->tpl->v= &$template->v;
		if (isset($sig->a['expr'])) {
			$expr=BpTemplate::compileExpression($sig->a['expr'],'$template->v');
			$renderTemplate = eval('return ('.$expr.');');
		} else if (isset($sig->a['value'])===true) {
				$field_value = $template->findValue($sig->a['value']);
				if (isset($sig->a['is'])) {
					$compare_value = $sig->a['is'];
					if ($field_value == $compare_value) $renderTemplate = true;
				} else if ($field_value) {
						$renderTemplate = true;
					}
			} else {
				return;
			}
		if ($renderTemplate == true) {
			self::activate($template);
			return $sig->tpl->render();
		}
	}
}
class Bp_elseif extends Bp_if {
	public static function render(BpSignature $sig, BpITemplate $template) {
		if (self::isActive($template)===true) return;
		return parent::render($sig, $template);
	}
}
class Bp_else extends Bp_if {
	public static function direct(BpSignature $sig, BpITemplate $template) {
		if ($sig->tpl === null) return;
		return Beilpuz::PHP_BEGIN.'}else{'.Beilpuz::PHP_END.$sig->tpl->getCompiledString().Beilpuz::PHP_BEGIN.'}'.Beilpuz::PHP_END;
	}
	public static function render(BpSignature $sig, BpITemplate $template) {
		if ($sig->tpl === null || self::isActive($template)===true) return;
		return $sig->tpl->render();
	}
}
?>