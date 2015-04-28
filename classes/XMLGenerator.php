<?php

/**
 * The XML generators.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

namespace Pagemanager;

use XH\Pages;

/**
 * The XML generators.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class XMLGenerator
{
    /**
     * The pagemanager model.
     *
     * @var Model
     */
    protected $model;

    /**
     * The pages object.
     *
     * @var Pages
     */
    protected $pages;

    /**
     * Initializes a new instance.
     *
     * @param Model $model A pagemanager model.
     * @param Pages $pages A pages object.
     */
    public function __construct(Model $model, Pages $pages)
    {
        $this->model = $model;
        $this->pages = $pages;
    }

    /**
     * Executes the generator.
     *
     * @return void
     */
    public function execute()
    {
        $this->model->getHeadings();
        header('Content-Type: application/xml; charset=UTF-8');
        echo $this->renderPages();
    }

    /**
     * Returns the page structure.
     *
     * @param int $parent The index of the parent page.
     *
     * @return string XML.
     */
    public function renderPages($parent = null)
    {
        if (!isset($parent)) {
            $o = '<root>';
        }
        $children = !isset($parent)
            ? $this->pages->toplevels(false)
            : $this->pages->children($parent, false);
        foreach ($children as $index) {
            $o .= $this->renderPage($index) . $this->renderPages($index) . '</item>';
        }
        if (!isset($parent)) {
            $o .= '</root>';
        }
        return $o;
    }

    /**
     * Returns the view of a single page.
     *
     * @param int $index A page index.
     *
     * @return string XML.
     *
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    protected function renderPage($index)
    {
        global $plugin_cf, $pd_router;

        $pcf = $plugin_cf['pagemanager'];
        $pageData = $pd_router->find_page($index);
        if ($pcf['pagedata_attribute'] === '') {
            $pdattr = '';
        } elseif ($pageData[$pcf['pagedata_attribute']] === '') {
            $pdattr = ' data-pdattr="1"';
        } else {
            $pdattr = $pageData[$pcf['pagedata_attribute']];
            $pdattr = " data-pdattr=\"$pdattr\"";
        }
        $heading = $this->model->headings[$index];
        $mayRename = $this->model->mayRename[$index];
        $rename = $mayRename ? '' : ' class="pagemanager-no-rename"';
        return "<item id=\"pagemanager-$index\" title=\"$heading\"$pdattr$rename>"
            . "<content><name>$heading</name></content>";
    }
}

?>
