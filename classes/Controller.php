<?php

/**
 * The controller class of Pagemanager_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2013 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The controller class of Pagemanager_XH.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Pagemanager_Controller
{
    /**
     * The pagemanager model.
     *
     * @var object
     */
    var $model;

    /**
     * Initializes a newly create object.
     *
     * @global array The paths of system files and folders.
     */
    function Pagemanager_Controller()
    {
        global $pth;

        include_once $pth['folder']['plugin_classes'] . 'Model.php';
        $this->model = new Pagemanager_Model();
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
    function render($_template, $_bag)
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
    function systemChecks()
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
        $ok = file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php');
        $checks[$ptx['syscheck_jquery']] = $ok ? 'ok' : 'fail';
        $ok = file_exists($pth['folder']['plugins'].'utf8/utf8.php');
        $checks[$ptx['syscheck_utf8']] = $ok ? 'ok' : 'fail';
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
    function version()
    {
        global $pth, $plugin_tx;

        $ptx = $plugin_tx['pagemanager'];
        $titles = array(
            'syscheck' => $ptx['syscheck_title'], 'about' => $ptx['about']
        );
        $checks = $this->systemChecks();
        $stateIcons = array();
        foreach (array('ok', 'warn', 'fail') as $state) {
            $stateIcons[$state]
                = "{$pth['folder']['plugins']}pagemanager/images/$state.png";
        }
        $version = PAGEMANAGER_VERSION;
        $icon = "{$pth['folder']['plugins']}pagemanager/pagemanager.png";
        $bag = compact('titles', 'checks', 'stateIcons', 'version', 'icon');
        return $this->render('info', $bag);
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
    function tool($tool)
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
        if ($tool === 'save') {
            $tooltip = XH_hsc(utf8_ucfirst($tx['action']['save']));
        } else {
            $tooltip = XH_hsc($plugin_tx['pagemanager']['op_'.$tool]);
        }
        if ($tool !== 'help') {
            $o .= '<button type="button" id="' . $id . '" ' . $style . $onclick
                . ' title="' . $tooltip . '"' . '></button>';
        } else {
            $o .= '<a href="' . $pth['file']['plugin_help']
                . '" target="_blank" id="' . $id . '" title="' . $tooltip . '"></a>';
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
    function config()
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
    function page($n, $heading, $pdattr, $mayRename)
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
     * @global array  Flags to signal whether the headings may be renamed.
     */
    function pages()
    {
        global $cl, $l, $pd_router, $plugin_cf;

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
        $o .= $this->page(
            0, $this->model->headings[0], $pdattr, $this->model->mayRename[0]
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
            $o .= $this->page(
                $i, $this->model->headings[$i], $pdattr, $this->model->mayRename[$i]
            );
        }
        $o .= '</ul>'."\n";
        return $o;
    }

    /**
     * Returns the page administration view.
     *
     * @return string (X)HTML.
     *
     * @global array  The paths of system files and folders.
     * @global string The script name.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the core.
     * @global array  The localization of the plugins.
     */
    function edit()
    {
        global $pth, $sn, $plugin_cf, $tx, $plugin_tx;

        include_once $pth['folder']['plugins'] . 'utf8/utf8.php';
        include_once UTF8 . '/ucfirst.php';
        $ptx = $plugin_tx['pagemanager'];
        include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
        include_jQuery();
        include_jQueryUI();
        include_jQueryPlugin('jsTree', $pth['folder']['plugins']
                .'pagemanager/jstree/jquery.jstree.min.js');

        $this->model->getHeadings();

        $xhpages = isset($_GET['xhpages']) ? '&amp;pagemanager-xhpages' : '';
        $actionUrl = $sn . '?&amp;pagemanager&amp;edit' . $xhpages;
        $isIrregular = $this->model->isIrregular();
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
        $config = $this->config();
        $bag = compact(
            'actionUrl', 'isIrregular', 'structureWarning', 'structureConfirmation',
            'showToolbar', 'tools', 'toolbarClass', 'saveButton', 'titleConfirm',
            'titleInfo', 'script', 'config'
        );
        $o = $this->render('widget', $bag);

        return $o;
    }

    /**
     * Saves the content. Returns whether that succeeded.
     *
     * @return bool
     *
     * @global array  The contents of the pages.
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the core.
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    function save($xml)
    {
        global $c, $pth, $cf, $plugin_cf, $pd_router;

        include_once "{$pth['folder']['plugins']}pagemanager/classes/XMLParser.php";
        $parser = new Pagemanager_XMLParser(
            $c, (int) $cf['menu']['levels'],
            $plugin_cf['pagemanager']['pagedata_attribute']
        );
        $parser->parse($xml);
        $c = $parser->getContents();
        return $pd_router->model->refresh($parser->getPageData());
    }

    /**
     * Returns the URL to redirect to.
     *
     * @return string
     */
    function redirectURL()
    {
        $queryString = isset($_GET['pagemanager-xhpages'])
            ? '&normal&xhpages'
            : '&pagemanager&normal&admin=plugin_main';
        return CMSIMPLE_URL . '?' . $queryString;
    }

    /**
     * Dispatches according to the current request.
     *
     * @return string The (X)HTML.
     *
     * @global string The admin parameter.
     * @global string The action parameter.
     * @global string Whether pagemanager administration is requested.
     * @global object The CSRF protection object.
     * @global array  The paths of system files and folders.
     * @global string The requested function.
     * @global array  The configuration of the core.
     */
    function dispatch()
    {
        global $admin, $action, $pagemanager, $_XH_csrfProtection, $pth, $plugin, $f, $cf;

        $o = '';
        /*
         * Hook into new edit menu of CMSimple_XH 1.5
         */
        if ($f === 'xhpages'
            && in_array($cf['pagemanager']['external'], array('', 'pagemanager'))
        ) {
            $o .= $this->edit();
        } elseif (isset($pagemanager) && $pagemanager === 'true') {
            $o .= print_plugin_admin('on');
            switch ($admin) {
            case '':
                if ($action == 'plugin_save') {
                    $_XH_csrfProtection->check();
                    if ($this->save(stsl($_POST['xml']))) {
                        header('Location: ' . $this->redirectURL, true, 303);
                        exit();
                    } else {
                        e('cntwriteto', 'content', $pth['file']['content']);
                        $o .= $this->edit();
                        break;
                    }
                } else {
                    $o .= $this->version();
                }
                break;
            case 'plugin_main':
                $o .= $this->edit();
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
            }
        }
        return $o;
    }
}

?>
