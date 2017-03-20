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

class JSONGenerator
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
