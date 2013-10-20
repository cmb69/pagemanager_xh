<?php

/**
 * XMLParser of Pagemanager_XH
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2013 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The XML parser class.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Pagemanager_XMLParser
{
    /**
     * The new contents array.
     *
     * @var array
     */
    var $contents;

    /**
     * The new page data array.
     *
     * @var array
     */
    var $pageData;

    /**
     * The current nesting level.
     *
     * @var int
     */
    var $level;

    /**
     * The current page id (number?).
     *
     * @var int
     */
    var $id;

    /**
     * The current page heading.
     *
     * @var string
     */
    var $title;

    /**
     * The current page data attribute.
     *
     * @var bool
     */
    var $pdattr;

    /**
     * Parses the given <var>$xml</var>.
     *
     * @param string $xml XML.
     *
     * @return void
     */
    function parse($xml)
    {
	$parser = xml_parser_create('UTF-8');
	xml_set_element_handler(
            $parser, array($this, 'startElementHandler'),
            array($this, 'endElementHandler')
        );
	xml_set_character_data_handler($parser, array($this, 'cDataHandler'));
	$this->level = 0;
	$this->contents = array();
	$this->pageData = array();
	xml_parse($parser, $xml, true);
    }

    /**
     * Returns the new contents array.
     *
     * @return array
     */
    function getContents()
    {
        return $this->contents;
    }

    /**
     * Returns the new page data array.
     *
     * @return array.
     */
    function getPageData()
    {
        return $this->pageData;
    }

    /**
     * Handles the start elements of the XML.
     *
     * @param resource $parser  An XML parser.
     * @param string   $name    Name of the current element.
     * @param array    $attribs Attributes of the current element.
     *
     * @return void
     */
    function startElementHandler($parser, $name, $attribs)
    {
        if ($name === 'ITEM') {
            $this->level++;
            $pattern = '/(copy_)?pagemanager-([0-9]*)/';
            $this->id = $attribs['ID'] === ''
                ? ''
                : preg_replace($pattern, '$2', $attribs['ID']);
            $this->title = htmlspecialchars(
                $attribs['TITLE'], ENT_NOQUOTES, 'UTF-8'
            );
            $this->pdattr = $attribs['PDATTR'];
        }
    }

    /**
     * Handles the end elements of the XML.
     *
     * @param resource $parser An XML parser.
     * @param string   $name   Name of the current element.
     *
     * @return void
     */
    function endElementHandler($parser, $name)
    {
        if ($name === 'ITEM') {
            $this->level--;
        }
    }

    /**
     * Handles the character data of the XML.
     *
     * @param resource $parser An XML parser.
     * @param string   $data   The current character data.
     *
     * @return void
     *
     * @global array  The contents of the pages.
     * @global array  The configuration of the core.
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    function cDataHandler($parser, $data)
    {
        global $c, $cf, $plugin_cf, $pd_router;

        $data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
        if (isset($c[$this->id])) {
            $cnt = $c[$this->id];
            $pattern = '/<h[1-' . $cf['menu']['levels'] . ']([^>]*)>'
                . '((<[^>]*>)*)[^<]*((<[^>]*>)*)'
                . '<\/h[1-' . $cf['menu']['levels'] . ']([^>]*)>/i';
            $replacement = '<h' . $this->level . '$1>${2}'
                . addcslashes($this->title, '$\\') . '$4'
                . '</h' . $this->level . '$6>';
            $cnt = preg_replace($pattern, $replacement, $cnt, 1);
            $this->contents[] = $cnt;
        } else {
            $this->contents[] = '<h' . $this->level . '>' . $this->title
                . '</h' . $this->level . '>';
        }
        if ($this->id == '') {
            $pd = $pd_router->new_page(array());
        } else {
            $pd = $pd_router->find_page($this->id);
        }
        $pd['url'] = uenc($this->title);
        $pd[$plugin_cf['pagemanager']['pagedata_attribute']] = $this->pdattr;
        $this->pageData[] = $pd;
    }
}

?>
