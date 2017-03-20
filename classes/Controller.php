<?php

/**
 * Copyright 2011-2017 Christoph M. Becker
 *
 * This file is part of Pagemanager_XH.
 *
 * Pagemanager_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pagemanager_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Pagemanager_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Pagemanager;

use XH\Pages;

class Controller
{
    /**
     * @var object
     * @todo Make protected.
     */
    public $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @param string $template
     * @return string
     */
    protected function render($template)
    {
        global $pth;

        $template = "{$pth['folder']['plugins']}pagemanager/views/$template.php";
        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * @return array
     */
    protected function systemChecks()
    {
        global $pth, $plugin_tx;

        $ptx = $plugin_tx['pagemanager'];
        $phpVersion = '5.3.0';
        $checks = array();
        $key = sprintf($ptx['syscheck_phpversion'], $phpVersion);
        $ok = version_compare(PHP_VERSION, $phpVersion) >= 0;
        $checks[$key] = $ok ? 'ok' : 'fail';
        foreach (array('pcre', 'xml') as $ext) {
            $key = sprintf($ptx['syscheck_extension'], $ext);
            $checks[$key] = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $xhVersion = 'CMSimple_XH 1.7dev';
        $ok = strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') === 0
            && version_compare(CMSIMPLE_XH_VERSION, $xhVersion) >= 0;
        $xhVersion = substr($xhVersion, 12);
        $key = sprintf($ptx['syscheck_xhversion'], $xhVersion);
        $checks[$key] = $ok ? 'ok' : 'fail';
        $ok = file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php');
        $checks[$ptx['syscheck_jquery']] = $ok ? 'ok' : 'fail';
        $folders = array();
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'pagemanager/' . $folder;
        }
        foreach ($folders as $folder) {
            $key = sprintf($ptx['syscheck_writable'], $folder);
            $checks[$key] = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }

    /**
     * @return string
     */
    protected function pluginIconPath()
    {
        global $pth;

        return $pth['folder']['plugins'] . 'pagemanager/pagemanager.png';
    }

    /**
     * @param string $state
     * @return string
     */
    protected function stateIconPath($state)
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/images/$state.png";
    }

    /**
     * @return string
     */
    protected function ajaxLoaderPath()
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/images/ajax-loader-bar.gif";
    }

    /**
     * @param string $key
     * @return string
     */
    protected function lang($key)
    {
        global $plugin_tx;

        return $plugin_tx['pagemanager'][$key];
    }

    /**
     * @param string $tool
     * @return string
     */
    protected function tool($tool)
    {
        global $pth, $plugin_cf, $plugin_tx;

        $horizontal = !$plugin_cf['pagemanager']['toolbar_vertical'];
        $id = "pagemanager-$tool";
        $o = '';
        $style = $tool === 'save' ? ' style="display: none"' : '';
        if ($tool === 'save') {
            $tooltip = XH_hsc($plugin_tx['pagemanager']['button_save']);
        } else {
            $tooltip = XH_hsc($plugin_tx['pagemanager']['op_'.$tool]);
        }
        if ($tool !== 'help') {
            $o .= '<button type="button" id="' . $id . '" ' . $style
                . ' title="' . $tooltip . '"' . '></button>';
        } else {
            $o .= '<a href="' . $pth['file']['plugin_help']
                . '" target="_blank" id="' . $id . '" title="' . $tooltip . '"></a>';
        }
        if (!$horizontal) {
            $o .= '<br>';
        }
        $o .= "\n";
        return $o;
    }

    /**
     * @return string
     */
    protected function jsConfig()
    {
        global $pth, $sn, $tx, $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['pagemanager'];
        $ptx = $plugin_tx['pagemanager'];
        $config = array(
            'okButton' => $ptx['button_ok'],
            'cancelButton' => $ptx['button_cancel'],
            'deleteButton' => $ptx['button_delete'],
            'menuLevels' => 9,
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
            'hasCheckboxes' => $pcf['pagedata_attribute'] !== '',
            'dataURL' => $sn . '?&pagemanager&admin=plugin_main'
                . '&action=plugin_data&edit'
        );
        return XH_encodeJson($config);
    }

    /**
     * @return string
     */
    protected function toolbarClass()
    {
        global $plugin_cf;

        return $plugin_cf['pagemanager']['toolbar_vertical']
            ? 'pagemanager-vertical' : 'pagemanager-horizontal';
    }

    /**
     * @return bool
     */
    protected function hasToolbar()
    {
        global $plugin_cf;

        return (bool) $plugin_cf['pagemanager']['toolbar_show'];
    }

    /**
     * @return array
     */
    protected function tools()
    {
        return array(
            'save', 'expand', 'collapse', 'create', 'create_after', 'rename',
            'delete', 'cut', 'copy', 'paste', 'paste_after', 'help'
        );
    }

    /**
     * @return string
     */
    protected function submissionURL()
    {
        global $sn;

        $xhpages = isset($_GET['xhpages']) ? '&pagemanager-xhpages' : '';
        return "$sn?&pagemanager&$xhpages&edit";
    }

    /**
     * @return string
     */
    protected function jsScriptPath()
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/pagemanager.js";
    }

    /**
     * @return string
     */
    protected function editView()
    {
        global $pth, $title, $plugin_tx;

        $title = 'Pagemanager &ndash; ' . $plugin_tx['pagemanager']['menu_main'];
        include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
        include_jQuery();
        include_jQueryUI();
        include_jQueryPlugin(
            'jsTree',
            $pth['folder']['plugins'] . 'pagemanager/jstree/jquery.jstree.js'
        );
        return $this->render('widget');
    }

    protected function save()
    {
        global $pth, $plugin_tx, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $ptx = $plugin_tx['pagemanager'];
        if ($this->model->save(stsl($_POST['json']))) {
            echo XH_message('success', $ptx['message_save_success']);
        } else {
            $message = sprintf($ptx['message_save_failure'], $pth['file']['content']);
            echo XH_message('fail', $message);
        }
        exit;
    }

    /**
     * @return string
     */
    public function dispatch()
    {
        global $admin, $action, $plugin, $f, $cf;

        if (function_exists('XH_registerStandardPluginMenuItems')) {
            XH_registerStandardPluginMenuItems(false);
        }
        $o = '';
        if ($f === 'xhpages'
            && in_array($cf['pagemanager']['external'], array('', 'pagemanager'))
        ) {
            $o .= $this->editView();
        } elseif ($this->isAdministrationRequested()) {
            $o .= print_plugin_admin('on');
            switch ($admin) {
                case '':
                    $o .= $this->renderInfoView();
                    break;
                case 'plugin_main':
                    switch ($action) {
                        case 'plugin_data':
                            $temp = new JSONGenerator($this->model, new Pages());
                            $temp->execute();
                            exit;
                        case 'plugin_save':
                            $o .= $this->save();
                            break;
                        default:
                            $o .= $this->editView();
                    }
                    break;
                default:
                    $o .= plugin_admin_common($action, $admin, $plugin);
            }
        }
        return $o;
    }

    /**
     * @return bool
     */
    protected function isAdministrationRequested()
    {
        global $pagemanager;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('pagemanager')
            || isset($pagemanager) && $pagemanager === 'true';
    }

    /**
     * @return string
     */
    protected function renderInfoView()
    {
        global $title, $plugin_tx;

        $title = 'Pagemanager &ndash; ' . $plugin_tx['pagemanager']['menu_info'];
        return $this->render('info');
    }
}
