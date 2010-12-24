<?php
session_start();

require_once(dirname(__FILE__).'/common.inc.php');
require_once(dirname(__FILE__).'/common-functions.inc.php');
require_once(dirname(__FILE__).'/user.php');
require_once(dirname(__FILE__).'/h2o/h2o.php');
require_once(dirname(__FILE__).'/bbcode/stringparser_bbcode.class.php');

//set Paths for Beilpuz (somewhat un(crappy)documented feature)
$root = dirname(dirname(__FILE__));
$h2osettings = array('searchpath' => $root.'/var/templates/'.$_config['template'].'new',
                     'cache'      => false);
//the User
$user = new USER();
//global arrays for messages
$_MSG['err'] = array();
$_MSG['warn'] = array();
$_MSG['msg'] = array();


//init bbcode
// Zeilenumbrüche verschiedener Betriebsysteme vereinheitlichen
function convertlinebreaks ($text) {
    return preg_replace ("/\015\012|\015|\012/", "\n", $text);
}

// Alles bis auf Neuezeile-Zeichen entfernen
function bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
    if (!isset ($attributes['default'])) {
        $url = $content;
        $text = htmlspecialchars ($content);
    } else {
        $url = $attributes['default'];
        $text = $content;
    }
    if ($action == 'validate') {
        if (substr ($url, 0, 5) == 'data:' || substr ($url, 0, 5) == 'file:'
          || substr ($url, 0, 11) == 'javascript:' || substr ($url, 0, 4) == 'jar:') {
            return false;
        }
        return true;
    }
    return '<a href="'.htmlspecialchars ($url).'">'.$text.'</a>';
}

// Funktion zum Einbinden von Bildern
function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        if (substr ($content, 0, 5) == 'data:' || substr ($content, 0, 5) == 'file:'
          || substr ($content, 0, 11) == 'javascript:' || substr ($content, 0, 4) == 'jar:') {
            return false;
        }
        return true;
    }
    return '<img src="'.htmlspecialchars($content).'" alt="">';
}

$bbcode = new StringParser_BBCode ();
$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks');

$bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'htmlspecialchars');
$bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'nl2br');
$bbcode->addParser ('list', 'bbcode_stripcontents');

$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->addCode ('s', 'simple_replace', null, array ('start_tag' => '<s>', 'end_tag' => '</s>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<u>', 'end_tag' => '</u>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
                  'link', array ('listitem', 'block', 'inline'), array ('link'));
$bbcode->addCode ('link', 'callback_replace_single', 'do_bbcode_url', array (),
                  'link', array ('listitem', 'block', 'inline'), array ('link'));
$bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (),
                  'image', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->addCode ('bild', 'usecontent', 'do_bbcode_img', array (),
                  'image', array ('listitem', 'block', 'inline', 'link'), array ());
$bbcode->setOccurrenceType ('img', 'image');
$bbcode->setOccurrenceType ('bild', 'image');
$bbcode->setMaxOccurrences ('image', 2);
$bbcode->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
                  'list', array ('block', 'listitem'), array ());
$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                  'listitem', array ('list'), array ());
$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
$bbcode->setCodeFlag ('*', 'paragraphs', true);
$bbcode->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setRootParagraphHandling (true);
?>
