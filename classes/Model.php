<?php

/**
 * The model class of Pagemanager_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

namespace Pagemanager;

/**
 * The model class of Pagemanager_XH.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Model
{
    /**
     * The unmodified page headings.
     *
     * @var array
     *
     * @todo Make protected.
     */
    public $headings;

    /**
     * Whether the pages may be renamed.
     *
     * @var array
     *
     * @todo Make protected.
     */
    public $mayRename;

    /**
     * Returns whether a heading may be renamed.
     *
     * Renaming is only allow if the heading doesn't contain any markup,
     * besides the generally recognized XML entities.
     *
     * @param string $heading A page heading.
     *
     * @return string
     *
     * @since 2.0.1
     */
    protected function mayRename($heading)
    {
        return !preg_match('/<|&(?!(?:amp|quot|lt|gt);)/', $heading);
    }

    /**
     * Returns a cleaned heading.
     *
     * Trims, strips off all tags and decodes HTML entities in the heading.
     * For PHP 4 a simplified fallback is used which does not properly decode
     * the HTML entities, but rather replaces them with the Unicode substitution
     * character.
     *
     * @param string $heading A page heading.
     *
     * @return string
     *
     * @since 2.0.1
     */
    protected function cleanedHeading($heading)
    {
        $heading = trim(strip_tags($heading));
        $heading = html_entity_decode($heading, ENT_COMPAT, 'UTF-8');
        $heading = htmlspecialchars($heading, ENT_COMPAT, 'UTF-8');
        return $heading;
    }

    /**
     * Initializes <var>$headings</var> and <var>$mayRename</var>.
     *
     * @return void
     *
     * @global array The headings of the pages.
     * @global array The localization of the core.
     */
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
     * Returns whether the page structure is irregular.
     *
     * @return bool
     *
     * @global array The menu levels of the pages.
     * @global int   The number of pages.
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
     * Returns the available themes.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
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
     * Saves the content. Returns whether that succeeded.
     *
     * @param string $json A JSON string.
     *
     * @return bool
     *
     * @global array  The contents of the pages.
     * @global array  The configuration of the plugins.
     * @global object The page data router.
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

?>
