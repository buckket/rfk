<?php
/**
 * @package Beilpuz
 */
/*  Beilpuz is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Beilpuz Template Rendering Engine main class.
 * This class loads all dependent classes, contains environment variables,
 * configuration settings and cache management methods.
 * @author Mike Reiche
 * @version $Revision: 49 $ $Date: 2009-09-07 17:28:12 +0200 (Mo, 07. Sep 2009) $
 * @package Beilpuz
 * @since 0.1
 */
final class Beilpuz {
	/**
	 * Directory path which contains the templates files.
	 * @var String
	 */
	public static $templates = 'templates';
	/**
	 * Output directory for compiled templates.
	 * @var String
	 */
	public static $compiled = 'compiled';
	/**
	 * Output directory for cache files.
	 * @var String
	 */
	public static $cache = 'cache';
	/**
	 * PHP start tag.
	 * @var String
	 */
	const PHP_BEGIN = '<?php ';
	/**
	 * PHP end tag thats followed by an newline character (\n)
	 * @see http://bugs.php.net/bug.php?id=41281
	 * @var String
	 */
	const PHP_END = "?>\n";
	/**
	 * @ignore
	 */
	public static $SIG_BEGIN = '<bp:';
	/**
	 * @ignore
	 */
	public static $SIG_END = '>';
	/**
	 * @ignore
	 */
	public static $SIG_CLOSE = '</bp:';
	/**
	 * @ignore
	 */
	public static $sigClassPathes = array();
	/**
	 * Identification string for cache file loading.
	 * @var String
	 */
	public static $loadCacheID = '_';
	private static $cacheKeys = null;
	/**
	 * The allowed keys for cache file creation
	 * @var Array
	 */
	public static $allowedCacheKeys = array();
	/**
	 * Enables the caching engine.
	 * @param Array $cacheKeys Parameters for cache file generation (for example: $_GET).
	 */
	public static function enableCachingWithKeys(Array $cacheKeys) {
		include('BpTemplateCacheable.php');
		ksort($cacheKeys);
		$cacheID = array();
		foreach ($cacheKeys as $key => $value) {
			$cacheID[] = $key.$value;
		}
		self::$loadCacheID = '_'.join('_', $cacheID);
		self::$cacheKeys = $cacheKeys;
	}
	/**
	 * Adds a directory to the signature handlers class path.
	 * @param String $path Path to add.
	 */
	public static function addSignatureClassPath($path) {
		self::$sigClassPathes[$path] = true;
	}
	/**
	 * Creates a identification string for saving a cache file, based on Beilpuz::enableCachingWithKeys() and Beilpuz::allowCacheKey();
	 * @return String Cache file identification string or false if cache id is null.
	 */
	public static function saveCacheID() {
		ksort(self::$allowedCacheKeys);
		$cacheID = array();
		foreach (self::$allowedCacheKeys as $key => $useless) {
			if (isset(self::$cacheKeys[$key])) $cacheID[] = $key.self::$cacheKeys[$key];
		}
		return '_'.join('_', $cacheID);
	}
	/**
	 * Prevents storing compiled templates in {@link Beilpuz::$compiled}.
	 * If you enable this feature, all templates will be recompiled on every run.
	 */
	public static function enableTemporary() {
		include('BpTemplateTemporary.php');
	}
	/**
	 * Enables the caching system and allows the overgiven cache key.
	 * @param String $key Cache key to allow.
	 */
	public static function allowCacheKey($key) {
		self::$allowedCacheKeys[$key] = true;
	}
	/**
	 * @ignore
	 */
	public static function loadTemplateClass($className) {
		$exists = class_exists('BpTemplate');
		if ($exists === false && $className==='BpTemplate') {
			include('BpTemplateStorable.php');
			eval('final class BpTemplate extends BpTemplateStorable {}');
			$exists = true;
		}
		if ($exists === true) spl_autoload_unregister(array(Beilpuz,'loadTemplateClass'));
	}
	/**
	 * Changes the delimiter characters for the template compiler.
	 * @param String $start Start delimiter.
	 * @param String $end End delimiter
	 */
	public static function setDelimiters($start, $end = null) {
		if ($end === null) $end = $start;
		self::$SIG_BEGIN = $start . 'bp:';
		self::$SIG_END = $end;
		self::$SIG_CLOSE = $start . '/bp:';
	}
}
spl_autoload_register(array(Beilpuz,'loadTemplateClass'));
?>