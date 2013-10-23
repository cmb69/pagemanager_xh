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
    $_xhtml = (bool) $cf['xhtml']['endtags'];
    unset($pth, $cf);
    extract($_bag);
    ob_start();
    include $_template;
    $o = ob_get_clean();
    if (!$_xhtml) {
	$o = str_replace('/>', '>', $o);
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
 * Returns the view of a single tool.
 *
 * @param string $tool A tool name.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the plugins.
 * @global array The localization of the core.
 * @global array The localization of the plugins.
 */
function Pagemanager_tool($tool)
{
    global $pth, $plugin_cf, $tx, $plugin_tx;

    $imgdir = $pth['folder']['plugins'] . 'pagemanager/images/';
    $horizontal = !$plugin_cf['pagemanager']['toolbar_vertical'];
    $img = $imgdir . $tool . '.png';
    $id = "pagemanager-$tool";
    $o = '';
    $style = $tool === 'save' ? ' style="display: none"' : '';
    $onclick = 'PAGEMANAGER.tool(\''.$tool.'\')';
    $onclick = $tool !== 'help' ? " onclick=\"$onclick\"" : '';
    switch ($tool) {
    case 'save':
	$tooltip = XH_hsc(utf8_ucfirst($tx['action']['save']));
	break;
    default:
	$tooltip = XH_hsc($plugin_tx['pagemanager']['op_'.$tool]);
    }
    if ($tool !== 'help') {
	$o .= '<button type="button" id="' . $id . '" ' . $style . $onclick . ' title="' . $tooltip . '"' . '>';
	$o .= '</button>';
    } else {
	$o .= '<a href="' . $pth['file']['plugin_help'] . '" target="_blank" id="' . $id . '" title="' . $tooltip . '"></a>';
    }
    if (!$horizontal) {
	$o .= tab('br');
    }
    $o .= "\n";
    return $o;
}

/**
 * Returns the widget configuration.
 *
 * @return string JSON.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 * @global array The localization of the core.
 * @global array The configuration of the plugins.
 * @global array The localization of the plugins.
 */
function pagemanager_config()
{
    global $pth, $cf, $tx, $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['pagemanager'];
    $ptx = $plugin_tx['pagemanager'];
    $config = array(
	'okButton' => $ptx['button_ok'],
	'cancelButton' => $ptx['button_cancel'],
	'deleteButton' => $ptx['button_delete'],
	'menuLevels' => (int) $cf['menu']['levels'],
	'verbose' => (bool) $pcf['verbose'],
	'menuLevelMessage' => $ptx['message_menu_level'],
	'cantRenameError' => $ptx['error_cant_rename'],
	'deleteLastMessage' => $ptx['message_delete_last'],
	'confirmDeletionMessage' => $ptx['message_confirm_deletion'],
	'leaveWarning' => $ptx['message_warning_leave'],
	'leaveConfirmation' => $ptx['message_confirm_leave'],
	'animation' => (int) $pcf['treeview_animation'],
	'loading' => $ptx['treeview_loading'],
	'newNode' => $ptx['treeview_new'],
	'imageDir' => $pth['folder']['plugins'].'pagemanager/images/',
	'menuLevelMessage' => $ptx['message_menu_level'],
	'theme' => $pcf['treeview_theme'],
	'createOp' => $ptx['op_create'],
	'createAfterOp' => $ptx['op_create_after'],
	'renameOp' => $ptx['op_rename'],
	'deleteOp' => $ptx['op_delete'],
	'cutOp' => $ptx['op_cut'],
	'copyOp' => $ptx['op_copy'],
	'pasteOp' => $ptx['op_paste'],
	'pasteAfterOp' => $ptx['op_paste_after'],
	'noSelectionMessage' => $ptx['message_no_selection'],
	'duplicateHeading' => $tx['toc']['dupl'],
	'offendingExtensionError' => $ptx['error_offending_extension'],
	'hasCheckboxes' => $pcf['pagedata_attribute'] !== ''
    );
    return XH_encodeJson($config);
}

/**
 * Returns the view of a single page.
 *
 * @param int    A page index.
 * @param string A page heading.
 * @param string A page data attribute.
 * @param bool   Whether the page may be renamed.
 *
 * @return string (X)HTML.
 */
function Pagemanager_page($n, $heading, $pdattr, $mayRename)
{
    $pdattr = isset($pdattr) ?  " data-pdattr=\"$pdattr\"" : '';
    $rename = $mayRename ? '' : ' class="pagemanager-no-rename"';
    return "<li id=\"pagemanager-$n\" title=\"$heading\"$pdattr$rename>"
	. "<a href=\"#\">$heading</a>";
}

/**
 * Returns the pages view.
 *
 * @return string (X)HTML.
 *
 * @global int    The number of pages.
 * @global array  The menu levels of the pages.
 * @global object The page data router.
 * @global array  The configuration of the plugins.
 * @global object The pagemanager model.
 * @global array  Flags to signal whether the headings may be renamed.
 */
function Pagemanager_pages()
{
    global $cl, $l, $pd_router, $plugin_cf, $_Pagemanager;

    // output the treeview of the page structure
    // uses ugly hack to clean up irregular page structure
    $pcf = $plugin_cf['pagemanager'];
    $pd = $pd_router->find_page(0);
    if ($pcf['pagedata_attribute'] === '') {
	$pdattr = null;
    } elseif ($pd[$pcf['pagedata_attribute']] === '') {
	$pdattr = '1';
    } else {
	$pdattr = $pd[$pcf['pagedata_attribute']];
    }
    $o = '<ul>' . "\n";
    $o .= Pagemanager_page(
	0, $_Pagemanager->headings[0], $pdattr, $_Pagemanager->mayRename[0]
    );
    $stack = array();
    for ($i = 1; $i < $cl; $i++) {
	$ldiff = $l[$i] - $l[$i - 1];
	if ($ldiff <= 0) { // same level or decreasing
	    $o .= '</li>'."\n";
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
		$o .= '</ul></li>'."\n";
	} else { // level increasing
	    if ($ldiff > 1) {
		array_push($stack, $ldiff);
	    }
	    $o .= "\n".'<ul>'."\n";
	}
	$pd = $pd_router->find_page($i);
	if ($pcf['pagedata_attribute'] === '') {
	    $pdattr = null;
	} elseif ($pd[$pcf['pagedata_attribute']] === '') {
	    $pdattr = '1';
	} else {
	    $pdattr = $pd[$pcf['pagedata_attribute']];
	}
	$o .= Pagemanager_page(
	    $i, $_Pagemanager->headings[$i], $pdattr, $_Pagemanager->mayRename[$i]
	);
    }
    $o .= '</ul>'."\n";
    return $o;
}

/**
 * Returns the page administration view.
 *
 * @return string (X)HTML.
 */
function pagemanager_edit()
{
    global $pth, $sn, $plugin_cf, $tx, $plugin_tx, $_Pagemanager;

    $ptx = $plugin_tx['pagemanager'];
    include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();
    include_jQueryPlugin('jsTree', $pth['folder']['plugins']
	    .'pagemanager/jstree/jquery.jstree.min.js');

    $_Pagemanager->getHeadings();

    $xhpages = isset($_GET['xhpages']) ? '&amp;pagemanager-xhpages' : '';
    $actionUrl = $sn . '?&amp;pagemanager&amp;edit' . $xhpages;
    $isIrregular = $_Pagemanager->isIrregular();
    $structureWarning = $ptx['error_structure_warning'];
    $structureConfirmation = $ptx['error_structure_confirmation'];
    $showToolbar = $plugin_cf['pagemanager']['toolbar_show'];
    $tools = array(
	'save', 'expand', 'collapse', 'create',
	'create_after', 'rename', 'delete', 'cut', 'copy',
	'paste', 'paste_after', 'help'
    );
    $toolbarClass = !$plugin_cf['pagemanager']['toolbar_vertical']
	? 'pagemanager-horizontal' : 'pagemanager-vertical';
    $saveButton = utf8_ucfirst($tx['action']['save']);
    $titleConfirm = $ptx['message_confirm'];
    $titleInfo = $ptx['message_information'];
    $script = "{$pth['folder']['plugins']}pagemanager/pagemanager.js";
    $config = Pagemanager_config();
    $bag = compact(
	'actionUrl', 'isIrregular', 'structureWarning', 'structureConfirmation',
	'showToolbar', 'tools', 'toolbarClass', 'saveButton', 'titleConfirm',
	'titleInfo', 'script', 'config'
    );
    $o = Pagemanager_render('widget', $bag);

    return $o;
}


/**
 * Saves the content.
 * Returns whether that succeeded.
 *
 * @return bool
 */
function pagemanager_save($xml) {
    global $c, $pth, $pd_router;

    if (is_writable($pth['file']['content'])) {
	include_once "{$pth['folder']['plugins']}pagemanager/classes/XMLParser.php";
	$parser = new Pagemanager_XMLParser();
	$parser->parse($xml);
	$c = $parser->getContents();
	return $pd_router->model->refresh($parser->getPageData());
    } else {
	e('cntwriteto', 'content', $pth['file']['content']);
	return false;
    }
}

/**
 * Wrapper for Pagemananger_Model::themes().
 *
 * @return array
 *
 * @global object The pagemanager model.
 */
function Pagemanager_themes()
{
    global $_Pagemanager;

    return $_Pagemanager->themes();
}

/*
 * Initialize the global model object.
 */
require_once $pth['folder']['plugin_classes'] . 'Model.php';
$_Pagemanager = new Pagemanager_Model();

/*
 * Hook into new edit menu of CMSimple_XH 1.5
 */
if ($f === 'xhpages' && isset($cf['pagemanager']['external'])
    && in_array($cf['pagemanager']['external'], array('', 'pagemanager')))
{
    include_once $pth['folder']['plugins'] . 'utf8/utf8.php';
    include_once UTF8 . '/ucfirst.php';
    $o .= pagemanager_edit();
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
		$_XH_csrfProtection->check();
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
	    $o .= pagemanager_edit();
	    break;
	default:
	    $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
