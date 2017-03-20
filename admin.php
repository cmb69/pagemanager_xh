<?php

/**
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

use Pagemanager\Controller;

define('PAGEMANAGER_VERSION', '@PAGEMANAGER_VERSION@');

/**
 * @return array
 */
function Pagemanager_themes()
{
    global $_Pagemanager;

    return $_Pagemanager->model->themes();
}

$_Pagemanager = new Controller();
$o .= $_Pagemanager->dispatch();
