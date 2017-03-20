<?php

/**
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Pagemanager;

use XH\Pages;

class JSONGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Pages
     */
    protected $pages;

    /**
     * @param Model $model
     * @param Pages $pages
     */
    public function __construct(Model $model, Pages $pages)
    {
        $this->model = $model;
        $this->pages = $pages;
    }

    public function execute()
    {
        $this->model->getHeadings();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->getPagesData());
    }

    /**
     * @param ?int $parent
     * @return array
     */
    protected function getPagesData($parent = null)
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
    protected function getPageData($index)
    {
        global $plugin_cf, $pd_router;

        $pdattr = $plugin_cf['pagemanager']['pagedata_attribute'];
        $pageData = $pd_router->find_page($index);

        $res = array(
            'data' => $this->model->headings[$index],
            'attr' => array(
                'id' => "pagemanager-$index",
                'title' => $this->model->headings[$index]
            ),
            'children' => $this->getPagesData($index)
        );
        if ($pdattr !== '') {
            if ($pageData[$pdattr] === '') {
                $res['attr']['data-pdattr'] = '1';
            } else {
                $res['attr']['data-pdattr'] = $pageData[$pdattr];
            }
        }
        if (!$this->model->mayRename[$index]) {
            $res['attr']['class'] = 'pagemanager-no-rename';
        }
        return $res;
    }
}
