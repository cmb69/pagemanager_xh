<?php

/**
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Pagemanager;

class JSONProcessor
{
    /**
     * @var array
     */
    protected $contents;

    /**
     * @var array
     */
    protected $newContents;

    /**
     * @var array
     */
    protected $pageData;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $pdattrName;

    /**
     * @var bool
     */
    protected $pdattr;

    /**
     * @var bool
     */
    protected $mayRename;

    /**
     * @param array $contents
     * @param string $pdattrName
     */
    public function __construct($contents, $pdattrName)
    {
        $this->contents = $contents;
        $this->pdattrName = $pdattrName;
    }

    /**
     * @param string $json
     */
    public function process($json)
    {
        $this->level = 0;
        $this->newContents = array();
        $this->pageData = array();
        $this->processPages(json_decode($json, true));
    }

    /**
     * @param array $pages
     */
    protected function processPages($pages)
    {
        $this->level++;
        foreach ($pages as $page) {
            $this->processPage($page);
        }
        $this->level--;
    }

    /**
     * @param array $page
     */
    protected function processPage($page)
    {
        $pattern = '/(copy_)?pagemanager-([0-9]*)/';
        $this->id = empty($page['attr']['id'])
            ? null
            : (int) preg_replace($pattern, '$2', $page['attr']['id']);
        $this->title = htmlspecialchars($page['attr']['title'], ENT_NOQUOTES, 'UTF-8');
        $this->pdattr = isset($page['attr']['data-pdattr'])
            ? $page['attr']['data-pdattr'] : null;
        $this->mayRename = $page['attr']['class'] == '';

        if (isset($this->contents[$this->id])) {
            $this->appendExistingPageContent();
        } else {
            $this->appendNewPageContent();
        }
        $this->appendPageData();

        $this->processPages($page['children']);
    }

    protected function appendExistingPageContent()
    {
        $content = $this->contents[$this->id];
        if ($this->mayRename) {
            $content = $this->replaceHeading($content);
        }
        $this->newContents[] = $content;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function replaceHeading($content)
    {
        $pattern = "/<!--XH_ml[0-9]:.*?-->/";
        $replacement = "<!--XH_ml{$this->level}:"
            . addcslashes($this->title, '$\\') . '-->';
        return preg_replace($pattern, $replacement, $content, 1);
    }

    protected function appendNewPageContent()
    {
        $this->newContents[] = "<!--XH_ml{$this->level}:{$this->title}-->";
    }

    protected function appendPageData()
    {
        global $pd_router;

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

    /**
     * @return array
     */
    public function getContents()
    {
        return $this->newContents;
    }

    /**
     * @return array
     */
    public function getPageData()
    {
        return $this->pageData;
    }
}
