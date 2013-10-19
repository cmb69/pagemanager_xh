<?php

/**
 * Back-End of Pagemanager_XH.
 *
 * Copyright (c) 2011-2013 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('PAGEMANAGER_VERSION', '@PAGEMANAGER_VERSION@');

/**
 * Initializes the <var>$pagemanager_h</var> array with the unmodified page
 * headings and the <var>$pagemanager_no_rename</var> array to flag
 * whether the page may be renamed.
 *
 * @return void
 *
 * @global array The content of the pages.
 * @global array The configuration of the core.
 * @global array The unmodified page headings.
 * @global array Whether the page may be renamed.
 *
 * @todo Cater for content modifications by other plugins,
 * 	 unless we're in edit mode.
 */
function Pagemanager_getHeadings()
{
    global $c, $cf, $pagemanager_h, $pagemanager_no_rename;

    $stop = $cf['menu']['levels'];
    $empty = 0;
    foreach ($c as $i => $page) {
        preg_match('~<h([1-' . $stop . ']).*?>(.*?)</h~isu', $page, $matches);
	$heading = trim(strip_tags($matches[2]));
	if ($heading === '') {
	    $pagemanager_h[$i] = $tx['toc']['empty'] . ' ' . ++$empty;
	} else {
	    $pagemanager_h[$i] = $heading;
	}
	$pagemanager_no_rename[$i] = preg_match('/.*?<.*?/isu', $matches[2]);
    }
}

/**
 * Returns a rendered template.
 *
 * @param string $_template A template name.
 * @param array  $_bag      Template variables.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 */
function Pagemanager_render($_template, $_bag)
{
    global $pth, $cf;

    $_template = "{$pth['folder']['plugins']}pagemanager/views/$_template.php";
    $_xhtml = $cf['xhtml']['endtags'];
    unset($pth, $cf);
    extract($_bag);
    ob_start();
    include $_template;
    $o = ob_get_clean();
    if (!$_xhtml) {
	str_replace('/>', '>', $o);
    }
    return $o;
}

/**
 * Returns the system checks.
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 * @global array The localization of the plugins.
 *
 * @todo Add check for CMSimple_XH 1.6+.
 */
function Pagemanager_systemChecks()
{
    global $pth, $tx, $plugin_tx;

    $ptx = $plugin_tx['pagemanager'];
    $phpVersion = '4.3.0';
    $checks = array();
    $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
	= version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
    foreach (array('pcre', 'xml') as $ext) {
	$checks[sprintf($ptx['syscheck_extension'], $ext)]
	    = extension_loaded($ext) ? 'ok' : 'fail';
    }
    $checks[$ptx['syscheck_magic_quotes']]
	= !get_magic_quotes_runtime() ? 'ok' : 'fail';
    $checks[$ptx['syscheck_jquery']]
	= file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php') ? 'ok' : 'fail';
    $checks[$ptx['syscheck_utf8']]
	= file_exists($pth['folder']['plugins'].'utf8/utf8.php') ? 'ok' : 'fail';
    $checks[$ptx['syscheck_encoding']]
	= strtoupper($tx['meta']['codepage']) == 'UTF-8' ? 'ok' : 'warn';
    $folders = array();
    foreach (array('config/', 'css/', 'languages/') as $folder) {
	$folders[] = $pth['folder']['plugins'] . 'pagemanager/' . $folder;
    }
    foreach ($folders as $folder) {
	$checks[sprintf($ptx['syscheck_writable'], $folder)]
	    = is_writable($folder) ? 'ok' : 'warn';
    }
    return $checks;
}

/**
 * Returns plugin version information.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the plugins.
 */
function Pagemanager_version()
{
    global $pth, $plugin_tx;

    $ptx = $plugin_tx['pagemanager'];
    $titles = array('syscheck' => $ptx['syscheck_title'], 'about' => $ptx['about']);
    $checks = Pagemanager_systemChecks();
    $stateIcons = array();
    foreach (array('ok', 'warn', 'fail') as $state) {
	$stateIcons[$state] = "{$pth['folder']['plugins']}pagemanager/images/$state.png";
    }
    $version = PAGEMANAGER_VERSION;
    $icon = "{$pth['folder']['plugins']}pagemanager/pagemanager.png";
    $bag = compact('titles', 'checks', 'stateIcons', 'version', 'icon');
    return Pagemanager_render('info', $bag);
}

/**
 * Returns the toolbar.
 *
 * @param  string $save_js    The js code for onclick.
 * @return string	      The (x)html.
 */
function pagemanager_toolbar($save_js) {
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
	$img = $imgdir.$tool.($tool != 'separator' || !$horizontal ? '' : '_v').'.png';
	$class = $tool == 'separator' ? 'separator' : 'tool';
	$res .= ($tool != 'separator' ? '<a '.$link.' class="pl_tooltip"'.($tool == 'save' ? ' style="display: none"' : '').'>' : '')
		.tag('img class="'.$class.'" src="'.$img.'"'
		    .($tool != 'help' ? ' onclick="pagemanager_do(\''.$tool.'\'); return false;"' : ''))
		.($tool != 'separator'
		    ? '<span>'.($tool == 'save' ? utf8_ucfirst($tx['action']['save'])
			    : $plugin_tx['pagemanager']['op_'.$tool]).'</span></a>'
		    : '')
		.($horizontal ? '' : tag('br'))."\n";
    }
    $res .= '</div>'."\n";
    return $res;
}

/**
 * Returns an escaped plugin configuration option.
 * Helper for @see{pagemanager_instanciateJS}.
 *
 * @param array $matches The matches of a preg_replace.
 *
 * @return string
 *
 * @since 1pl14
 */
function Pagemanager_replaceConfig($matches)
{
    global $plugin_cf;

    $cf = $plugin_cf['pagemanager'][$matches[1]];
    $cf = addcslashes($cf, "\0'\"\\\f\n\r\t\v");
    return $cf;
}

/**
 * Returns an escaped plugin language string.
 * Helper for @see{pagemanager_instanciateJS}.
 *
 * @param array $matches The matches of a preg_replace.
 *
 * @return string
 *
 * @since 1pl14
 */
function Pagemanager_replaceLang($matches)
{
    global $plugin_tx;

    $tx = $plugin_tx['pagemanager'][$matches[1]];
    $tx = addcslashes($tx, "\0'\"\\\f\n\r\t\v");
    return $tx;
}

/**
 * Instanciate the pagemanager.js template.
 *
 * @return string  	      The (x)html.
 */
function pagemanager_instanciateJS() {
    global $pth, $plugin_cf, $plugin_tx, $cf, $tx;

    $js = rf($pth['folder']['plugins'].'pagemanager/pagemanager.js');

    $js = preg_replace_callback('/<<<PC_(.*)>>>/', 'Pagemanager_replaceConfig', $js);
    $js = preg_replace_callback('/<<<PT_(.*)>>>/', 'Pagemanager_replaceLang', $js);
    $js = str_replace('<<<MENU_LEVELS>>>', $cf['menu']['levels'], $js);
    $js = str_replace('<<<TOC_DUPL>>>', $tx['toc']['dupl'], $js);
    $js = str_replace('<<<IMAGE_EXT>>>', '.png', $js);
    $js = str_replace('<<<IMAGE_DIR>>>', $pth['folder']['plugins'].'pagemanager/images/', $js);

    return '<!-- initialize jstree -->'."\n"
	    .'<script type="text/javascript">'."\n"
	    .'/* <![CDATA[ */'.$js.'/* ]]> */'."\n"
	    .'</script>'."\n";
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
    include_jQueryUI();
    include_jQueryPlugin('jsTree', $pth['folder']['plugins']
	    .'pagemanager/jstree/jquery.jstree.min.js');

    Pagemanager_getHeadings();

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
    $bo .= '<form id="pagemanager-form" action="'.$sn.'?&amp;pagemanager&amp;edit'
	.$xhpages.'" method="post" accept-charset="UTF-8">'."\n";
    $bo .= strtolower($plugin_cf['pagemanager']['toolbar_show']) == 'true'
	    ? pagemanager_toolbar($save_js) : '';

    // output the treeview of the page structure
    // uses ugly hack to clean up irregular page structure
    $irregular = FALSE;
    $pd = $pd_router->find_page(0);

    $bo .= '<!-- page structure -->'."\n"
	    .'<div id="pagemanager" ondblclick="jQuery(\'#pagemanager\').jstree(\'toggle_node\');">'."\n"
    	    .'<ul>'."\n".'<li id="pagemanager-0" title="'.$pagemanager_h[0].'"'
	    .' pdattr="'.($pd[$plugin_cf['pagemanager']['pagedata_attribute']] == ''
		? '1' : $pd[$plugin_cf['pagemanager']['pagedata_attribute']]).'"'
	    .($pagemanager_no_rename[0] ? ' class="pagemanager-no-rename"' : '')
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
	$bo .= '<li id="pagemanager-'.$i.'"'
		.' title="'.$pagemanager_h[$i].'"'
		.' pdattr="'.($pd[$plugin_cf['pagemanager']['pagedata_attribute']] == ''
		    ? '1' : $pd[$plugin_cf['pagemanager']['pagedata_attribute']]).'"'
		.($pagemanager_no_rename[$i] ? ' class="pagemanager-no-rename"' : '')
		.'><a href="#">'.$pagemanager_h[$i].'</a>';
    }
    $bo .= '</ul></div>'."\n";

    if ($irregular)
	$o .= $swo;

    $o .= $bo;

    $o .= pagemanager_instanciateJS();

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

    $o .= '<div id="pagemanager-confirmation" title="'
	    .$plugin_tx['pagemanager']['message_confirm']
	    .'"></div>'."\n"
	    .'<div id="pagemanager-alert" title="'
	    .$plugin_tx['pagemanager']['message_information'].'"></div>'."\n";
}


/**
 * Handles start elements of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_start_element_handler($parser, $name, $attribs) {
    global $o, $pagemanager_state;
    if ($name == 'ITEM') {
	$pagemanager_state['level']++;
	$pagemanager_state['id'] = $attribs['ID'] == ''
		? '' : preg_replace('/(copy_)?pagemanager-([0-9]*)/', '$2', $attribs['ID']);
	$pagemanager_state['title'] = htmlspecialchars($attribs['TITLE'], ENT_NOQUOTES, 'UTF-8');
	$pagemanager_state['pdattr'] = $attribs['PDATTR'];
    }
}


/**
 * Handles end elements of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_end_element_handler($parser, $name) {
    global $pagemanager_state;
    if ($name == 'ITEM')
	$pagemanager_state['level']--;
}


/**
 * Handles character data of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_cdata_handler($parser, $data) {
    global $c, $h, $cf, $pagemanager_c, $pagemanager_state, $pagemanager_pd,
	    $pd_router, $plugin_cf;
    $data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
    if (isset($c[$pagemanager_state['id']])) {
	$cnt = $c[$pagemanager_state['id']];
	$cnt = preg_replace('/<h[1-'.$cf['menu']['levels'].']([^>]*)>'
		.'((<[^>]*>)*)[^<]*((<[^>]*>)*)<\/h[1-'.$cf['menu']['levels'].']([^>]*)>/i',
		'<h'.$pagemanager_state['level'].'$1>${2}'.addcslashes($pagemanager_state['title'], '$\\').'$4'
		.'</h'.$pagemanager_state['level'].'$6>', $cnt, 1);
	$pagemanager_c[] = $cnt;
    } else {
	$pagemanager_c[] = '<h'.$pagemanager_state['level'].'>'.$pagemanager_state['title']
	    .'</h'.$pagemanager_state['level'].'>';
    }

    if ($pagemanager_state['id'] == '') {
	$pd = $pd_router->new_page(array());
    } else {
	$pd = $pd_router->find_page($pagemanager_state['id']);
    }
    $pd['url'] = uenc($pagemanager_state['title']);
    $pd[$plugin_cf['pagemanager']['pagedata_attribute']] = $pagemanager_state['pdattr'];
    $pagemanager_pd[] = $pd;
}


/**
 * Saves the content.
 * Returns whether that succeeded.
 *
 * @return bool
 */
function pagemanager_save($xml) {
    global $c, $pth, $tx, $pd_router, $pagemanager_state, $pagemanager_c, $pagemanager_pd;
    if (is_writable($pth['file']['content'])) {
	$parser = xml_parser_create('UTF-8');
	xml_set_element_handler($parser, 'pagemanager_start_element_handler',
		'pagemanager_end_element_handler');
	xml_set_character_data_handler($parser, 'pagemanager_cdata_handler');
	$pagemanager_state['level'] = 0;
	$pagemanager_c = array();
	$pagemanager_pd = array();
	xml_parse($parser, $xml, TRUE);
	$c = $pagemanager_c;
	return $pd_router->model->refresh($pagemanager_pd);
    } else {
	e('cntwriteto', 'content', $pth['file']['content']);
	return false;
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


/**
 * Plugin administration
 */
if (isset($pagemanager)) {
    include_once $pth['folder']['plugins'] . 'utf8/utf8.php';
    include_once UTF8 . '/ucfirst.php';

    initvar('admin');
    initvar('action');

    $o .= print_plugin_admin('on');

    switch ($admin) {
	case '':
	    if ($action == 'plugin_save') {
		if (pagemanager_save(stsl($_POST['xml']))) {
		    if (!headers_sent()) {
			header('Location: ' . CMSIMPLE_URL
				.(isset($_GET['pagemanager-xhpages'])
				? '?&normal&xhpages'
				: '?&pagemanager&normal&admin=plugin_main'));
		    }
		    exit();
		}
	    } else {
		$o .= Pagemanager_version();
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
