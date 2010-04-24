<?php
/**
 * @package Beilpuz
*/
/**
 * Template interface definition.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
interface BpITemplate {
	/**
	 * Finds and returns a value.
	 * This method finds a assigned value from a template variable notation.
	 * Using this method is more comfortable, but slower than accessing {@link $v} directly.
	 * @param String $key Template variable notation.
	 * @return Mixed Assigned value.
	 */
	public function findValue($key);
	/**
	* Assigns a value or an array to the template.
	* @param String Variable identification key.
	* @param Mixed Variable value.
	*/
	public function assign($key, $value);
	/**
     * Assigs a value as reference to the template.
     * @param String $key Variable identification key
     * @param Mixed $value Variable value.
     */
	public function assignRef($key, &$value);
	/**
	 * Returns the template in compiled form.
	 * @return String Compiled template.
	 */
	public function getCompiledString();
	/**
	 * Compiles the template if this was not done already
	 * and renders all assigned values and signatures.
	 * @return String Rendered template.
	 */
	public function render();
	/**
	 * Converts the Beilpuz value notation to native PHP variable notation.
	 * @param String $valueString Beilpuz value notation.
	 * @return String PHP variable notation.
	 */
	public static function compileValueString(&$valueString, $context=null);
	/**
	 * Compiles and Beilpuz expression to real PHP expression.
	 * @param String $expr Expression to compile.
	 */
	public static function compileExpression($expr,$context=null);
	/**
	 * Compiles a template from a string and overwrites the name of this template file.
	 * @param String $templateCode Raw template code to compile.
	 */
	public function fromString($templateCode);
}
/**
 * General abstract template.
 * This class implements methods for accessing values and compiling a template.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.3
 */
abstract class BpTemplateAbstract {
	/**
	 * Template name or path.
	 * @var String
	 */
	public $name = null;
	/**
	 * Container for assigned values.
	 * @var Array
	 */
	public $v = null;
	/**
	 * Signatures container.
	 * @var Array
	 */
	public $s = null;
	/**
	 * Set this value to true if you want to update the signatures after rendering this template.
	 * This works only for {@link BpTemplateStorable}.
	 * @var Boolean
	 */
	public $update = false;
	/**
	 * Initializes the abstract template and checks if the source file exists.
	 * @param String $templateFile Beilpuz source template file to load.
	 * @throws ErrorException If the template file could not be found or read.
	 * @return BpITemplate Beilpuz template instance.
	 */
	public function __construct($templateFile = null) {
		if ($templateFile !== null) {
			$this->name = $templateFile;
			$templateFile = Beilpuz::$templates.'/'.$this->name;
			if (is_readable($templateFile) === false) {
				throw new ErrorException('Template is not readable: \'' . getcwd().'/'.$templateFile.'\'');
			}
		}
    }
	public function fromString($templateCode) {
		$this->name = basename($_SERVER['PHP_SELF']);
		$this->compile($templateCode);
	}
	/**
	 * Compiles the template.
	 * @param String $content Raw template data to compile.
	 * @param BpCompiler $parent The parent compiler instance. Used for inline templates of direct signatures.
	 * @return BpCompiler instance.
	 */
	public function compile(&$content,BpCompiler $parent=null) {
		// Start compiling template
		include_once('BpCompiler.php');
		$compiler = new BpCompiler($this,$parent);
		$compiler->compileString($content);
		return $compiler;
	}
	public function findValue($key) {
		return eval('return '.self::compileValueString($key).';');
	}
	public static function compileValueString(&$valueString, $context=null) {
		if ($context===null) $context='$this->v';
		$bracketPos = strpos($valueString, '[', 1);
		$dotPos = strpos($valueString, '.', 1);
		if ($bracketPos === $dotPos) {
			return $context.'[\''.$valueString.'\']';
		} else if ($dotPos !== false && ($bracketPos === false || ($bracketPos !== false && $dotPos < $bracketPos))) {
			$pos = $dotPos;
		} else $pos = $bracketPos;
		$mainVariable = substr($valueString,0,$pos);
		$compiledVariableString = $context.'[\''.$mainVariable.'\']';
		$valueString = substr($valueString, $pos, strlen($valueString)-$pos);
		$valueString = str_replace('[','[\'',$valueString);
		$valueString = str_replace(']','\']',$valueString);
		$valueString = str_replace('.','->',$valueString);
		return $compiledVariableString .= $valueString;
    }
	public static function compileExpression($expr,$context=null) {
		$matches = array();
		preg_match_all('/\$(\w+)/',$expr,$matches,PREG_SET_ORDER);
		$replaced = array();
		foreach ($matches as $variable) {
			if (isset($replaced[$variable[1]])===true) continue;
			$replaced[$variable[1]]=true;
			$expr = str_replace($variable[0],self::compileValueString($variable[1],$context), $expr);
		}
		return $expr;
	}
	public function assign($key, $value) {
		$this->v[$key] = $value;
	}
	public function assignRef($key, &$value) {
		$this->v[$key] = $value;
    }
	/**
	 * @ignore
	 */
	protected function co(&$sigOutput) {
		return $sigOutput;
    }
 }
?>