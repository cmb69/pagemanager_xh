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
     */
    private $model;

    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var config
     */
    private $config;

    /**
     * @var array
     */
    private $lang;

    /**
     * @var CSRFProtection
     */
    private $csrfProtector;

    public function __construct()
    {
        global $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;

        $this->model = new Model();
        $this->pluginFolder = "{$pth['folder']['plugins']}pagemanager/";
        $this->config = $plugin_cf['pagemanager'];
        $this->lang = $plugin_tx['pagemanager'];
        $this->csrfProtector = $_XH_csrfProtection;
    }

    /**
     * @return array
     */
    private function systemChecks()
    {
        global $pth;

        $phpVersion = '5.3.0';
        $checks = array();
        $key = sprintf($this->lang['syscheck_phpversion'], $phpVersion);
        $ok = version_compare(PHP_VERSION, $phpVersion) >= 0;
        $checks[$key] = $ok ? 'ok' : 'fail';
        foreach (array('json') as $ext) {
            $key = sprintf($this->lang['syscheck_extension'], $ext);
            $checks[$key] = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $xhVersion = 'CMSimple_XH 1.7dev';
        $ok = strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') === 0
            && version_compare(CMSIMPLE_XH_VERSION, $xhVersion) >= 0;
        $xhVersion = substr($xhVersion, 12);
        $key = sprintf($this->lang['syscheck_xhversion'], $xhVersion);
        $checks[$key] = $ok ? 'ok' : 'fail';
        $ok = file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php');
        $checks[$this->lang['syscheck_jquery']] = $ok ? 'ok' : 'fail';
        $folders = array();
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = "{$this->pluginFolder}{$folder}";
        }
        foreach ($folders as $folder) {
            $key = sprintf($this->lang['syscheck_writable'], $folder);
            $checks[$key] = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }

    /**
     * @param string $tool
     * @return string
     */
    private function tool($tool)
    {
        global $pth;

        $horizontal = !$this->config['toolbar_vertical'];
        $id = "pagemanager-$tool";
        $o = '';
        $style = $tool === 'save' ? ' style="display: none"' : '';
        if ($tool === 'save') {
            $tooltip = XH_hsc($this->lang['button_save']);
        } else {
            $tooltip = XH_hsc($this->lang['op_'.$tool]);
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
    private function jsConfig()
    {
        global $sn, $tx;

        $config = array(
            'okButton' => $this->lang['button_ok'],
            'cancelButton' => $this->lang['button_cancel'],
            'deleteButton' => $this->lang['button_delete'],
            'menuLevels' => 9,
            'verbose' => (bool) $this->config['verbose'],
            'menuLevelMessage' => $this->lang['message_menu_level'],
            'cantRenameError' => $this->lang['error_cant_rename'],
            'deleteLastMessage' => $this->lang['message_delete_last'],
            'confirmDeletionMessage' => $this->lang['message_confirm_deletion'],
            'leaveWarning' => $this->lang['message_warning_leave'],
            'leaveConfirmation' => $this->lang['message_confirm_leave'],
            'animation' => (int) $this->config['treeview_animation'],
            'loading' => $this->lang['treeview_loading'],
            'newNode' => $this->lang['treeview_new'],
            'imageDir' => "{$this->pluginFolder}images/",
            'menuLevelMessage' => $this->lang['message_menu_level'],
            'theme' => $this->config['treeview_theme'],
            'createOp' => $this->lang['op_create'],
            'createAfterOp' => $this->lang['op_create_after'],
            'renameOp' => $this->lang['op_rename'],
            'deleteOp' => $this->lang['op_delete'],
            'cutOp' => $this->lang['op_cut'],
            'copyOp' => $this->lang['op_copy'],
            'pasteOp' => $this->lang['op_paste'],
            'pasteAfterOp' => $this->lang['op_paste_after'],
            'noSelectionMessage' => $this->lang['message_no_selection'],
            'duplicateHeading' => $tx['toc']['dupl'],
            'offendingExtensionError' => $this->lang['error_offending_extension'],
            'hasCheckboxes' => $this->config['pagedata_attribute'] !== '',
            'dataURL' => $sn . '?&pagemanager&admin=plugin_main'
                . '&action=plugin_data&edit'
        );
        return json_encode($config);
    }

    /**
     * @return string[]
     */
    private function tools()
    {
        return array(
            'save', 'expand', 'collapse', 'create', 'create_after', 'rename',
            'delete', 'cut', 'copy', 'paste', 'paste_after', 'help'
        );
    }

    /**
     * @return string
     */
    private function submissionURL()
    {
        global $sn;

        $xhpages = isset($_GET['xhpages']) ? '&pagemanager-xhpages' : '';
        return "$sn?&pagemanager&$xhpages&edit";
    }

    /**
     * @return string
     */
    private function editView()
    {
        global $pth, $title;

        $title = "Pagemanager – {$this->lang['menu_main']}";
        include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
        include_jQuery();
        include_jQueryUI();
        include_jQueryPlugin(
            'jsTree',
            "{$this->pluginFolder}jstree/jquery.jstree.js"
        );
        $view = new View('widget');
        $view->submissionUrl = $this->submissionURL();
        $view->isIrregular = $this->model->isIrregular();
        $view->ajaxLoaderPath = "{$this->pluginFolder}images/ajax-loader-bar.gif";
        $view->hasToolbar = (bool) $this->config['toolbar_show'];
        $view->toolbarClass = $this->config['toolbar_vertical'] ? 'pagemanager-vertical' : 'pagemanager-horizontal';
        $tools = array();
        foreach ($this->tools() as $tool) {
            $tools[] = new HtmlString($this->tool($tool));
        }
        $view->tools = $tools;
        $view->csrfTokenInput = new HtmlString($this->csrfProtector->tokenInput());
        $view->jsConfig = new HtmlString($this->jsConfig());
        $view->jsScriptPath = "{$this->pluginFolder}pagemanager.js";
        return (string) $view;
    }

    private function save()
    {
        global $pth;

        $this->csrfProtector->check();
        if ($this->model->save(stsl($_POST['json']))) {
            echo XH_message('success', $this->lang['message_save_success']);
        } else {
            $message = sprintf($this->lang['message_save_failure'], $pth['file']['content']);
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
        } elseif (XH_wantsPluginAdministration('pagemanager')) {
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
     * @return string
     */
    private function renderInfoView()
    {
        global $title;

        $title = "Pagemanager – {$this->lang['menu_info']}";
        $view = new View('info');
        $view->logoPath = "{$this->pluginFolder}pagemanager.png";
        $view->version = PAGEMANAGER_VERSION;
        $checks = array();
        foreach ($this->systemChecks() as $check => $state) {
            $checks[] = (object) array(
                'check' => $check,
                'state' => $state,
                'icon' => "{$this->pluginFolder}images/$state.png"
            );
        }
        $view->checks = $checks;
        return (string) $view;
    }
}
