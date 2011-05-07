<?php

require_once(dirname(__FILE__).'/h2o/h2o.php');

/**
 * templateclass
 * @author teddydestodes
 * @todo implement old features
 */
class Template {

    /**
     * templatepath
     * @var string
     */
    var $path;

    /**
     * name of the template
     * @var string
     */
    var $template;

    /**
     * array of headerinformation
     * @var unknown_type
     */
    var $header;

    /**
     * array for templatedata
     * @var unknown_type
     */
    var $data;

    public function __construct() {

    }

    /**
     * adds data to the template
     * @param string $key
     * @param mixed $data
     */
    public function addData($key, $data) {
        $this->data[$key] = $data;
    }

    /**
     * sets the template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * sets the searchpath for the templateengine
     * @param string $path
     */
    public function setTemplatePath($path) {
        $this->path = $path;
    }

    /**
     * compiles and returns the template
     * @return string
     */
    public function compileHTML() {
        $h2osettings = array('searchpath' => $this->path);
        $h2o = new H2o($this->template,$h2osettings);
        return $h2o->render($this->data);
    }

    /**
     * compiles and outputs the template
     */
    public function printPage() {
        echo $this->compileHTML();
    }
}