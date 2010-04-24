<?php
/**
 * @package Beilpuz
 */
include('BpSignature.php');
/**
 * Abstract signature handler class.
 * @author Mike Reiche
 * @version $Revision: 47 $ $Date: 2009-09-05 16:34:45 +0200 (Sa, 05. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
abstract class BpSignatureHandler {
	/**
	 * Defines required attributes by this signature handler
	 * @var Array
	 */
	public static $requiredAttributes = array();
	/**
	 * Specifies, if this handler implements direct rendering.
	 * @var Boolean
	 */
	public static $directAllowed = false;
	/**
	 * Specifies, if this handler can only be called directly.
	 * @var Boolean
	 */
	public static $directOnly = false;
	/**
	 * Renders a signature.
	 * @param BpSignature $sig Signature to render.
	 * @param BpTemplate $tpl Template which contains this signature.
	 * @return String Rendered signature.
	 */
	public static function render(BpSignature $sig,BpTemplate $tpl) {
		return $sig->n . '::render() not implemented';
	}
	/**
	 * Renders a signature directly.
	 * This method will be called while compiling the template and
	 * can return native PHP code.
	 * @param BpSignature $sig Signature to render.
	 * @param BpTemplate $tpl Template which contains this signature.
	 * @return String PHP code string.
	 */
	public static function direct(BpSignature $sig,BpTemplate $tpl) {
		return $sig->n . '::direct() not implemented';
	}
}
?>