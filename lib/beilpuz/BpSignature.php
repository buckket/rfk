<?php
/**
 * @package Beilpuz
 */
/**
 * Signatures used in templates.
 * This is a simple container class which implements methods for acessing attributes.
 * @author Mike Reiche
 * @version $Revision: 45 $ $Date: 2009-09-05 15:09:44 +0200 (Sa, 05. Sep 2009) $
 * @package Beilpuz
 * @since 0.1
 */
final class BpSignature {
	/**
	 * Signature name.
	 * @var String
	 */
	public $n=null;
	/**
	 * Contained inline template.
	 * @var BpTemplate
	 */
	public $tpl=null;
	/**
	 * Contained attributes.
	 * @var Array
	 */
	public $a=null;
	/**
	 * Prefix for signature handler classes.
	 * @var String
	 */
	const CLASS_PREFIX='Bp_';
	/**
	 * Creates a new BpSignature instance.
	 * @param String $name Signature name.
	 */
	public function __construct($name) {
		$this->n = $name;
	}
	/**
	* Returns the value of an attribute.
	* @param String $key The attribute's name.
	* @return Mixed Attribute's value.
	*/
	public function __get($key) {
		return $this->a[$key];
	}
	/**
	* Sets an attribute's value.
	* @param String $key Name of the attribute.
	* @param Mixed $value New value of the attribute. If the value is null, the attribute will be removed.
	*/
	public function __set($key, $value) {
		if ($value === null) $this->__unset($key);
		else $this->a[$key] = $value;
	}
	/**
	 * Removes the attribute.
	 * @param String $key Attribute's name to remove.
	 */
	public function __unset($key) {
		unset($this->a[$key]);
		if (empty($this->a)) $this->a=null;
	}
	/**
	* Checks if an attribute exists.
	* @param String $key The attribute's name.
	* @return Boolean True if its defined.
	*/
	public function __isset($key) {
		return isset($this->a[$key]);
	}
	/**
	 * Calls the according {@link BpSignatureHandler::render()} method.
	 * @param BpTemplate Template context.
	 * @return String Return value of the handler.
	 */
	public function call($template=null) {
		$className = self::CLASS_PREFIX.$this->n;
		return call_user_func(array($className, 'render'), $this, $template);
	}
}
?>