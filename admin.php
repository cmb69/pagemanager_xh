<?php

/**
 * Back-End of Pagemanager_XH.
 *
 * Copyright (c) 2011-2013 Christoph M. Becker (see license.txt)
 */


// utf-8-marker: äöüß


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('PAGEMANAGER_VERSION', '2beta1');


define('PAGEMANAGER_URL', 'http'
   . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '')
   . '://' . $_SERVER['SERVER_NAME']
   . ($_SERVER['SERVER_PORT'] < 1024 ? '' : ':' . $_SERVER['SERVER_PORT'])
   . preg_replace('/index.php$/', '', $_SERVER['SCRIPT_NAME']));


/**
 * Reads content.htm and sets $pagemanager_h.
 *
 * The function was copied from CMSimple_XH 1.4's cms.php and modified
 * to just set the global $pagemanager_h with the unmodified page titles
 * and $pagemanager_no_rename wether the heading is partially formatted.
 *
 * @return void
 */
function pagemanager_rfc() {
    global $pth, $tx, $cf, $pagemanager_h, $pagemanager_no_rename;

    $c = array();
    $pagemanager_h = array();
    $u = array();
    $l = array();
    $empty = 0;
    $duplicate = 0;

    $content = file_get_contents($pth['file']['content']);
    $stop = $cf['menu']['levels'];
    $split_token = '#@CMSIMPLE_SPLIT@#';


    $content = preg_split('~</body>~i', $content);
    $content = preg_replace('~<h[1-' . $stop . ']~i', $split_token . '$0', $content[0]);
    $content = explode($split_token, $content);
    array_shift($content);

    foreach ($content as $page) {
        $c[] = $page;
        preg_match('~<h([1-' . $stop . ']).*>(.*)</h~isU', $page, $temp);
        $l[] = $temp[1];
        $temp_h[] = trim(strip_tags($temp[2]));
	$pagemanager_no_rename[] = preg_match('/.*?<.*?/isU', $temp[2]);
    }

    $cl = count($c);
    $s = -1;

    if ($cl == 0) {
        $c[] = '<h1>' . $tx['toc']['newpage'] . '</h1>';
        $pagemanager_h[] = trim(strip_tags($tx['toc']['newpage']));
	$pagemanager_no_rename[] = preg_match('/.*?<.*?/isU', $tx['toc']['newpage']);
        $l[] = 1;
        $s = 0;
        return;
    }

    foreach ($temp_h as $i => $pagemanager_heading) {
        if ($pagemanager_heading == '') {
            $empty++;
            $pagemanager_heading = $tx['toc']['empty'] . ' ' . $empty;
        }
	$pagemanager_h[$i] = $pagemanager_heading;
    }
}


/**
 * Returns a view.
 *
 * @global array  Paths of system files and folders.
 * @param string $template  The name of the view.
 * @param array $bag  The data for the view.
 * @return string
 */
function Pagemanager_view($template, $bag)
{
    global $pth;

    ob_start();
    extract($bag);
    include "{$pth['folder']['plugins']}pagemanager/views/$template.htm";
    return ob_get_clean();
}


/**
 * Returns the plugin's "About" view.
 *
 * @return string  The (X)HTML.
 */
function Pagemanager_aboutView()
{
    return Pagemanager_view('about', array('version' => PAGEMANAGER_VERSION));
}


/**
 * Returns the toolbar.
 *
 * @param  string $image_ext  The image extension (.gif or .png).
 * @param  string $save_js    The js code for onclick.
 * @return string	      The (x)html.
 */
function pagemanager_toolbar($image_ext, $save_js) {
    global $pth, $plugin_cf, $plugin_tx, $tx;

    $imgdir = $pth['folder']['plugins'].'pagemanager/images/';
    $horizontal = strtolower($plugin_cf['pagemanager']['toolbar_vertical']) != 'true';
    $res = '<div id="pagemanager-toolbar" class="'.($horizontal ? 'horizontal' : 'vertical').'">'."\n";
    $toolbar = array('save', 'separator', 'expand', 'collapse', 'separator', 'create',
	    'create_after', 'rename', 'delete', 'separator', 'cut', 'copy',
	    'paste', 'paste_after', 'separator', 'help');
    foreach ($toolbar as $tool) {
	$link = ($tool != 'help' ? 'href="#"'
		: 'href="'.$pth['file']['plugin_help'].'" target="_blank"');
	$img = $imgdir.$tool.($tool != 'separator' || !$horizontal ? '' : '_v').$image_ext;
	$class = $tool == 'separator' ? 'separator' : 'tool';
	$help = $tool == 'save'
	    ? utf8_ucfirst($tx['action']['save'])
	    : $plugin_tx['pagemanager']['op_'.$tool];
	$alt = $tool == 'help' ? $tx['editmenu']['help'] : $help;
	$res .= ($tool != 'separator' ? '<a '.$link.' class="pl_tooltip"'.($tool == 'save' ? ' style="display: none"' : '').'>' : '')
		.tag('img class="'.$class.'" src="'.$img.'"'
		    .($tool != 'help' ? ' onclick="pagemanager_do(\''.$tool.'\'); return false;"' : '') . ' alt=" ' . $alt . '"')
		.($tool != 'separator'
		    ? '<span>'.$help.'</span></a>'
		    : '')
		.($horizontal ? '' : tag('br'))."\n";
    }
    $res .= '</div>'."\n";
    return $res;
}


/**
 * Instanciate the pagemanager.js template.
 *
 * @param  string $image_ext  The image extension (.gif or .png).
 * @return string  	      The (x)html.
 */
function pagemanager_instanciateJS($image_ext) {
    global $pth, $plugin_cf, $plugin_tx, $cf, $tx;

    $pcf = $plugin_cf['pagemanager'];
    $texts = array();
    foreach ($pcf as $key => $val) {
	$texts[$key] = $val;
    }
    foreach ($plugin_tx['pagemanager'] as $key => $val) {
	$texts[$key] = $val;
    }
    $texts['menu_levels'] = intval($cf['menu']['levels']);
    $texts['toc_dupl'] = $tx['toc']['dupl'];
    $texts['image_ext'] = $image_ext;
    $texts['image_dir'] = $pth['folder']['plugins'] . 'pagemanager/images/';

    $json = json_encode($texts);

    return '<script type="text/javascript">/* <![CDATA[ */var PAGEMANAGER = '
	. $json . ';/* ]]> */</script>'
	. '<script type="text/javascript" src="'
	. $pth['folder']['plugins'] . 'pagemanager/pagemanager.js"></script>';
}


/**
 * Emits the page administration (X)HTML.
 *
 * @return void
 */
function pagemanager_edit() {
    global $hjs, $pth, $o, $sn, $h, $l, $plugin, $plugin_cf, $tx, $plugin_tx,
	$u, $pagemanager_h, $pagemanager_no_rename, $pd_router;

    include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryPlugin('jsTree', $pth['folder']['plugins']
	    .'pagemanager/jstree/jquery.jstree.min.js');

    $image_ext = (file_exists($pth['folder']['plugins'].'pagemanager/images/help.png'))
	    ? '.png' : '.gif';

    pagemanager_rfc();

    $bo = '';

    $swo = '<div id="pagemanager-structure-warning" class="cmsimplecore_warning"><p>'
	    .$plugin_tx['pagemanager']['error_structure_warning']
	    .'</p><p><a href="#" onclick="pagemanager_confirmStructureWarning();return false">'
	    .$plugin_tx['pagemanager']['error_structure_confirmation']
	    .'</a></div>'."\n";


    $save_js = 'jQuery(\'#pagemanager-xml\')[0].value ='
	    .' jQuery(\'#pagemanager\').jstree(\'get_xml\', \'nest\', -1,
		new Array(\'id\', \'title\', \'pdattr\'))';
    $xhpages = isset($_GET['xhpages']) ? '&amp;pagemanager-xhpages' : '';
    $bo .= '<form id="pagemanager-form" action="'.$sn.'?&amp;pagemanager&amp;edit'.$xhpages.'" method="post">'."\n";
    $bo .= strtolower($plugin_cf['pagemanager']['toolbar_show']) == 'true'
	    ? pagemanager_toolbar($image_ext, $save_js) : '';

    // output the treeview of the page structure
    // uses ugly hack to clean up irregular page structure
    $irregular = FALSE;
    $pd = $pd_router->find_page(0);

    $classes = array();
    if ($pagemanager_no_rename[0]) {
	$classes[] = 'pagemanager-no-rename';
    }
    if ($pd[$plugin_cf['pagemanager']['pagedata_attribute']] != '0') {
	$classes[] = 'pagemanager_pdattr';
    }
    $bo .= '<!-- page structure -->'."\n"
	    .'<div id="pagemanager" ondblclick="jQuery(\'#pagemanager\').jstree(\'toggle_node\');">'."\n"
    	    .'<ul>'."\n".'<li id="pagemanager-0" title="'.$pagemanager_h[0].'"'
	    .' class="' . implode(' ', $classes) . '"'
	    .'><a href="#">'.$pagemanager_h[0].'</a>';
    $stack = array();
    for ($i = 1; $i < count($h); $i++) {
	$ldiff = $l[$i] - $l[$i-1];
	if ($ldiff <= 0) { // same level or decreasing
	    $bo .= '</li>'."\n";
	    if ($ldiff != 0 && count($stack) > 0) {
		$jdiff = array_pop($stack);
		if ($jdiff + $ldiff > 0) {
		    array_push($stack, $jdiff + $ldiff);
		    $ldiff = 0;
		} else {
		    $ldiff += $jdiff - 1;
		}
	    }
	    for ($j = $ldiff; $j < 0; $j++)
		$bo .= '</ul></li>'."\n";
	} else { // level increasing
	    if ($ldiff > 1) {
		array_push($stack, $ldiff);
		$irregular = TRUE;
	    }
	    $bo .= "\n".'<ul>'."\n";
	}
	$pd = $pd_router->find_page($i);
	$classes = array();
	if ($pagemanager_no_rename[$i]) {
	    $classes[] = 'pagemanager-no-rename';
	}
	if ($pd[$plugin_cf['pagemanager']['pagedata_attribute']] != '0') {
	    $classes[] = 'pagemanager_pdattr';
	}
	$bo .= '<li id="pagemanager-'.$i.'"'
		.' title="'.$pagemanager_h[$i].'"'
		.' class="' . implode(' ', $classes) . '"'
		.'><a href="#">'.$pagemanager_h[$i].'</a>';
    }
    $bo .= '</ul></div>'."\n";

    if ($irregular)
	$o .= $swo;

    $o .= $bo;

    $o .= pagemanager_instanciateJS($image_ext);

    // HACK?: send 'edit' as query param to prevent the last if clause in
    //		rfc() to insert #CMSimple hide#
    $o .= tag('input type="hidden" name="admin" value=""')."\n"
	    .tag('input type="hidden" name="action" value="plugin_save"')."\n"
	    .tag('input type="hidden" name="xml" id="pagemanager-xml" value=""')."\n"
	    .tag('input id="pagemanager-submit" type="submit" class="submit" value="'
		.utf8_ucfirst($tx['action']['save']).'"'
		.' onclick="'.$save_js.'"'
		.' style="display: none"')."\n"
	    .'</form>'."\n"
	    .'<div id="pagemanager-footer">&nbsp;</div>'."\n";
}


/**
 * Parsing of jsTree's XML result.
 *
 * @package Pagemanager
 */
class Pagemanager_Parser
{
    /**
     * The current menu level.
     *
     * @var int
     */
    var $level;

    /**
     * The current page index.
     *
     * @var int
     */
    var $id;

    /**
     * The current page title.
     *
     * @var string
     */
    var $title;

    /**
     * The current value of the pagedata attribute.
     *
     * @var string
     */
    var $pdattr;

    /**
     * The current page number.
     *
     * @var int
     */
    var $num;

    /**
     * The contents.
     *
     * @var array
     */
    var $c = array();

    /**
     * The page data.
     *
     * @var array
     */
    var $pd = array();

    /**
     * Handles XML start tags.
     *
     * @param resource $parser  The XML parser.
     * @param string $name  The name of the element in upper case.
     * @param array $attribs  Dictionary of attributes.
     * @return void
     */
    function handleStartTag($parser, $name, $attribs)
    {
	if ($name == 'ITEM') {
	    $this->level++;
	    $this->id = $attribs['ID'] == ''
		? ''
		: preg_replace('/(copy_)?pagemanager-([0-9]+)/', '$2', $attribs['ID']);
	    $this->title = htmlspecialchars($attribs['TITLE'], ENT_NOQUOTES, 'UTF-8');
	    $this->pdattr = strpos($attribs['CLASS'], 'pagemanager_pdattr') !== false ? '1' : '0';
	    $this->num++;
	}
    }

    /**
     * Handles XML end tags.
     *
     * @param resource $parser  The XML parser.
     * @param string $name  The name of the element in upper case.
     * @return void
     */
    function handleEndTag($parser, $name)
    {
	if ($name == 'ITEM') {
	    $this->level--;
	}
    }

    /**
     * Handles XML character data.
     *
     * @global array  The contents.
     * @global array  The configuration of the core.
     * @global object  The pagedata router.
     * @global array  The configuration of the plugins.
     * @param resource $parser  The XML parser.
     * @param string $data  The character data.
     * @return void
     */
    function handleCData($parser, $data)
    {
	global $c, $cf, $pd_router, $plugin_cf;

	if (isset($c[$this->id])) {
	    $cnt = $c[$this->id];
	    $cnt = preg_replace('/<h[1-' . $cf['menu']['levels'] . ']([^>]*)>'
				. '((<[^>]*>)*)[^<]*((<[^>]*>)*)'
				. '<\/h[1-' . $cf['menu']['levels'] . ']([^>]*)>/i',
				'<h' . $this->level . '$1>${2}' . $this->title . '$4'
				. '</h' . $this->level . '$6>', $cnt, 1);
	} else {
	    $cnt = '<h' . $this->level . '>' . $this->title
		. '</h' . $this->level . '>';
	}
	$this->c[] = rmnl($cnt . "\n");

	if ($this->id == '') {
	    $pd = $pd_router->new_page(array());
	} else {
	    $pd = $pd_router->find_page($this->id);
	}
	$pd['url'] = uenc($this->title);
	$pd[$plugin_cf['pagemanager']['pagedata_attribute']] = $this->pdattr;
	$this->pd[] = $pd;
    }

    /**
     * Parses the XML and returns the new contents and pagedata array.
     *
     * @param string $xml  The XML.
     * @return array
     */
    function parse($xml)
    {
	$this->c = array();
	$this->pd = array();
	$parser = xml_parser_create('UTF-8');
	xml_set_element_handler($parser, array($this, 'handleStartTag'),
				array($this, 'handleEndTag'));
	xml_set_character_data_handler($parser, array($this, 'handleCData'));
	$this->level = 0;
	$this->num = -1;
	$this->c[] = "<html><head><title>Content</title></head><body>\n";
	xml_parse($parser, $xml, true);
	$this->c[] = "</body></html>\n";

	return array($this->c, $this->pd);
    }
}


/**
 * Saves content and pagedata.
 *
 * @return void.
 */
function Pagemanager_save($xml)
{
    global $pth, $pd_router;

    $parser = new Pagemanager_Parser();
    list($c, $pd) = $parser->parse($xml);
    if (($fh = fopen($pth['file']['content'], 'w')) !== false &&
	fwrite($fh, implode('', $c)) !== false)
    {
	$pd_router->model->refresh($pd);
	$qs = isset($_GET['pagemanager-xhpages'])
	    ? '?&normal&xhpages'
	    : '?&pagemanager&normal&admin=plugin_main';
	header('Location: ' . PAGEMANAGER_URL . $qs);
	exit();
    } else {
	e('cntsave', 'content', $pth['file']['content']);
    }
}


/**
 * Hook into new edit menu of CMSimple_XH 1.5
 */
if ($f === 'xhpages' && isset($cf['pagemanager']['external'])
    && in_array($cf['pagemanager']['external'], array('', 'pagemanager')))
{
    include_once $pth['folder']['plugins'] . 'utf8/utf8.php';
    include_once UTF8 . '/ucfirst.php';
    pagemanager_edit();
}


/*
 * Handle the plugin administration.
 */
if (isset($pagemanager) && $pagemanager == 'true') {
    // check requirements (RELEASE-TODO)
    define('PAGEMANAGER_PHP_VERSION', '4.3.0');
    if (version_compare(PHP_VERSION, PAGEMANAGER_PHP_VERSION) < 0)
	$e .= '<li>'.sprintf($plugin_tx['pagemanager']['error_phpversion'], PAGEMANAGER_PHP_VERSION).'</li>'."\n";
    foreach (array('pcre', 'xml') as $ext) {
	if (!extension_loaded($ext))
	    $e .= '<li>'.sprintf($plugin_tx['pagemanager']['error_extension'], $ext).'</li>'."\n";
    }
    if (!file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php'))
	$e .= '<li>'.$plugin_tx['pagemanager']['error_jquery'].'</li>'."\n";
    if (!file_exists($pth['folder']['plugins'].'utf8/utf8.php'))
	$e .= '<li>'.$plugin_tx['pagemanager']['error_utf8'].'</li>'."\n";
    if (strtolower($tx['meta']['codepage']) != 'utf-8') {
	$e .= '<li>'.$plugin_tx['pagemanager']['error_encoding'].'</li>'."\n";
    }

    include_once $pth['folder']['plugins'] . 'utf8/utf8.php';
    include_once UTF8 . '/ucfirst.php';

    $o .= print_plugin_admin('on');

    switch ($admin) {
    case '':
	if ($action == 'plugin_save') {
	    Pagemanager_save(stsl($_POST['xml']));
	} else {
	    $o .= Pagemanager_aboutView();
	}
	break;
    case 'plugin_main':
	pagemanager_edit();
	break;
    default:
	$o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
