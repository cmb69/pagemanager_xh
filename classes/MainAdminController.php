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

class MainAdminController extends Controller
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var Pages
     */
    private $pages;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $pdAttr;

    /**
     * @var PageDataRouter
     */
    private $pdRouter;

    /**
     * @var CSRFProtection
     */
    private $csrfProtector;

    public function __construct()
    {
        global $plugin_cf, $pd_router, $_XH_csrfProtection;

        parent::__construct();
        $this->model = new Model;
        $this->pages = new Pages;
        $this->config = $plugin_cf['pagemanager'];
        $this->pdAttr = $this->config['pagedata_attribute'];
        $this->pdRouter = $pd_router;
        $this->csrfProtector = $_XH_csrfProtection;
    }

    public function indexAction()
    {
        global $pth, $title, $bjs;

        $title = "Pagemanager – {$this->lang['menu_main']}";
        include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
        include_jQuery();
        include_jQueryUI();
        include_jQueryPlugin(
            'jsTree',
            "{$this->pluginFolder}jstree/jquery.jstree.js"
        );
        $bjs .= '<script type="text/javascript">var PAGEMANAGER = ' . $this->jsConfig() . ';</script>'
            . '<script type="text/javascript" src="' . XH_hsc("{$this->pluginFolder}pagemanager.js") . '"></script>';
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
        $view->render();
    }

    /**
     * @return string
     */
    private function submissionURL()
    {
        global $sn;

        $url = new Url($sn, array('pagemanager' => '', 'edit' => ''));
        if (isset($_GET['xhpages'])) {
            $url = $url->with('pagemanager-xhpages', '');
        }
        return (string) $url;
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

        $url = new Url($sn, array());
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
            'dataURL' => (string) $url->with('pagemanager', '')->with('admin', 'plugin_main')
                ->with('action', 'plugin_data')
        );
        return json_encode($config);
    }

    public function dataAction()
    {
        $this->model->calculateHeadings();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->getPagesData());
    }

    /**
     * @param ?int $parent
     * @return array[]
     */
    private function getPagesData($parent = null)
    {
        $res = array();
        $children = !isset($parent)
            ? $this->pages->toplevels(false)
            : $this->pages->children($parent, false);
        foreach ($children as $index) {
            $res[] = $this->getPageData($index);
        }
        return $res;
    }

    /**
     * @param int $index
     * @return array
     */
    private function getPageData($index)
    {
        $pageData = $this->pdRouter->find_page($index);

        $res = array(
            'data' => $this->model->getHeading($index),
            'attr' => array(
                'id' => "pagemanager-$index",
                'title' => $this->model->getHeading($index)
            ),
            'children' => $this->getPagesData($index)
        );
        if ($this->pdAttr !== '') {
            if ($pageData[$this->pdAttr] === '') {
                $res['attr']['data-pdattr'] = '1';
            } else {
                $res['attr']['data-pdattr'] = $pageData[$this->pdAttr];
            }
        }
        if (!$this->model->getMayRename[$index]) {
            $res['attr']['class'] = 'pagemanager-no-rename';
        }
        return $res;
    }

    public function saveAction()
    {
        global $pth;

        $this->csrfProtector->check();
        if ($this->model->save(stsl($_POST['json']))) {
            echo XH_message('success', $this->lang['message_save_success']);
        } else {
            $message = sprintf($this->lang['message_save_failure'], $pth['file']['content']);
            echo XH_message('fail', $message);
        }
    }
}
