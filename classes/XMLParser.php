<?php

/**
 * XMLParser of Pagemanager_XH
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

/**
 * The XML parser class.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class XMLParser
{
    /**
     * The original contents array.
     *
     * @var array
     */
    protected $contents;

    /**
     * The new contents array.
     *
     * @var array
     */
    protected $newContents;

    /**
     * The new page data array.
     *
     * @var array
     */
    protected $pageData;

    /**
     * The maximum nesting level.
     *
     * @var int
     */
    protected $levels;

    /**
     * The current nesting level.
     *
     * @var int
     */
    protected $level;

    /**
     * The current page id (number?).
     *
     * @var int
     */
    protected $id;

    /**
     * The current page heading.
     *
     * @var string
     */
    protected $title;

    /**
     * The name of the page data attribute.
     *
     * @var string
     */
    protected $pdattrName;

    /**
     * The current page data attribute.
     *
     * @var bool
     */
    protected $pdattr;

    /**
     * Whether the current page may be renamed.
     *
     * @var bool
     */
    protected $mayRename;

    /**
     * Initializes a newly created object.
     *
     * @param array  $contents   Page contents.
     * @param int    $levels     Maximum page level.
     * @param string $pdattrName Name of a page data attribute.
     */
    public function __construct($contents, $levels, $pdattrName)
    {
        $this->contents = $contents;
        $this->levels = $levels;
        $this->pdattrName = $pdattrName;
    }

    /**
     * Parses the given <var>$xml</var>.
     *
     * @param string $xml XML.
     *
     * @return void
     */
    public function parse($xml)
    {
        $parser = xml_parser_create('UTF-8');
        // In PHP 4, we have to use a reference to create the callbacks.
        // For PHP 5, we don't want references, though.
        if (version_compare(phpversion(), '5', 'ge')) {
            xml_set_element_handler(
                $parser, array($this, 'startElementHandler'),
                array($this, 'endElementHandler')
            );
            xml_set_character_data_handler($parser, array($this, 'cDataHandler'));
        } else {
            xml_set_element_handler(
                $parser, array(&$this, 'startElementHandler'),
                array(&$this, 'endElementHandler')
            );
            xml_set_character_data_handler($parser, array(&$this, 'cDataHandler'));
        }
        $this->level = 0;
        $this->newContents = array();
        $this->pageData = array();
        xml_parse($parser, $xml, true);
    }

    /**
     * Returns the new contents array.
     *
     * @return array
     */
    public function getContents()
    {
        return $this->newContents;
    }

    /**
     * Returns the new page data array.
     *
     * @return array
     */
    public function getPageData()
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function startElementHandler($parser, $name, $attribs)
    {
        if ($name === 'ITEM') {
            $this->level++;
            $pattern = '/(copy_)?pagemanager-([0-9]*)/';
            $this->id = $attribs['ID'] === ''
                ? null
                : (int) preg_replace($pattern, '$2', $attribs['ID']);
            $this->title = htmlspecialchars(
                $attribs['TITLE'], ENT_NOQUOTES, 'UTF-8'
            );
            $this->pdattr = isset($attribs['DATA-PDATTR'])
                ? $attribs['DATA-PDATTR'] : null;
            $this->mayRename = $attribs['CLASS'] == '';
        }
    }

    /**
     * Handles the end elements of the XML.
     *
     * @param resource $parser An XML parser.
     * @param string   $name   Name of the current element.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function endElementHandler($parser, $name)
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
     * @global object The page data router.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function cDataHandler($parser, $data)
    {
        global $pd_router;

        if (trim($data) === '') {
            return;
        }
        $data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
        if (isset($this->contents[$this->id])) {
            $content = $this->contents[$this->id];
            if ($this->mayRename) {
                $pattern = '/<h[1-' . $this->levels . ']([^>]*)>'
                    . '((<[^>]*>)*)[^<]*((<[^>]*>)*)'
                    . '<\/h[1-' . $this->levels . ']([^>]*)>/i';
                $replacement = '<h' . $this->level . '$1>${2}'
                    . addcslashes($this->title, '$\\') . '$4'
                    . '</h' . $this->level . '$6>';
                $content = preg_replace($pattern, $replacement, $content, 1);
            }
            $this->newContents[] = $content;
        } else {
            $this->newContents[] = '<h' . $this->level . '>' . $this->title
                . '</h' . $this->level . '>';
        }
        if (isset($this->id)) {
            $pageData = $pd_router->find_page($this->id);
        } else {
            $pageData = $pd_router->new_page();
            $pageData['last_edit'] = time();
        }
        if ($this->mayRename) {
            $pageData['url'] = uenc($this->title);
        }
        if ($this->pdattrName !== '') {
            $pageData[$this->pdattrName] = $this->pdattr;
        }
        $this->pageData[] = $pageData;
    }
}

?>
