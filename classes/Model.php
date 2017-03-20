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

class Model
{
    /**
     * @var array
     * @todo Make private.
     */
    public $headings;

    /**
     * @var array
     */
    private $mayRename;

    /**
     * @param string $heading
     * @return string
     */
    private function mayRename($heading)
    {
        return !preg_match('/<|&(?!(?:amp|quot|lt|gt);)/', $heading);
    }

    /**
     * @param string $heading
     * @return string
     */
    private function cleanedHeading($heading)
    {
        $heading = trim(strip_tags($heading));
        $heading = html_entity_decode($heading, ENT_COMPAT, 'UTF-8');
        $heading = htmlspecialchars($heading, ENT_COMPAT, 'UTF-8');
        return $heading;
    }

    public function getHeadings()
    {
        global $h, $tx;

        $empty = 0;
        foreach (array_keys($h) as $i) {
            $heading = $this->cleanedHeading($h[$i]);
            if ($heading === '') {
                $empty += 1;
                $this->headings[$i] = $tx['toc']['empty'] . ' ' . $empty;
            } else {
                $this->headings[$i] = $heading;
            }
            $this->mayRename[$i] = $this->mayRename($h[$i]);
        }
    }

    /**
     * @return bool
     */
    public function isIrregular()
    {
        global $l, $cl;

        for ($i = 1; $i < $cl; $i++) {
            $delta = $l[$i] - $l[$i - 1];
            if ($delta > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function themes()
    {
        global $pth;

        $themes = array();
        $path = "{$pth['folder']['plugins']}pagemanager/jstree/themes/";
        $dir = opendir($path);
        if ($dir !== false) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] !== '.' && is_dir($path . $entry)) {
                    $themes[] = $entry;
                }
            }
        }
        natcasesort($themes);
        return $themes;
    }

    /**
     * @param string $json
     * @return bool
     */
    public function save($json)
    {
        global $c, $plugin_cf, $pd_router;

        $parser = new JSONProcessor(
            $c,
            $plugin_cf['pagemanager']['pagedata_attribute']
        );
        $parser->process($json);
        $c = $parser->getContents();
        return $pd_router->refresh($parser->getPageData());
    }
}
