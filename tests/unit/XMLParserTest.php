<?php

/**
 * Testing the XML parser class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2015 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

require_once './vendor/autoload.php';

/**
 * The class under test.
 */
require_once './classes/XMLParser.php';

require_once '../../cmsimple/classes/PageDataRouter.php';
require_once '../../cmsimple/functions.php';

use Pagemanager\XMLParser;

/**
 * A test case to for the XML parser class.
 *
 * @category Testing
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class XMLParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XMLParser
     */
    var $parser;

    /**
     * Sets up the page data router stub.
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    protected function setUpPDRouterStub()
    {
        global $pd_router;

        $pd_router = $this->getMockBuilder('XH\PageDataRouter')
            ->disableOriginalConstructor()
            ->getMock();
        $pd_router->expects($this->any())
            ->method('new_page')
            ->will($this->returnValue(array('url' => '', 'foo' => 'bar')));
        $map = array(
            array(0, array('url' => 'Welcome', 'foo' => 'bar')),
            array(1, array('url' => 'About', 'foo' => 'bar')),
            array(2, array('url' => 'News', 'foo' => 'bar'))
        );
        $pd_router->expects($this->any())
            ->method('find_page')
            ->will($this->returnValueMap($map));
    }

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    protected function setUp()
    {
        global $cf;

        $cf['uri']['word_separator'] = '_';
        $contents = array(
            '<h1>Welcome</h1>Welcome to my website!',
            '<h2>About</h2>About me',
            '<h1>News</h1>Here are some news.'
        );
        $levels = 3;
        $pdattrName = 'show';
        $this->setUpPDRouterStub();
        $this->parser = new XMLParser($contents, $levels, $pdattrName);
        $timeMock = new PHPUnit_Extensions_MockFunction('time', $this->parser);
        $timeMock->expects($this->any())->will($this->returnValue(1420903422));
    }

    /**
     * Returns data for testParse().
     *
     * @return array
     */
    public function dataForParse()
    {
        return array(
            array( // unmodified
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // insert page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
        <item id="" title="New Page" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[New Page]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h2>New Page</h2>',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array(
                        'url' => 'New_Page', 'foo' => 'bar', 'show' => '1',
                        'last_edit' => 1420903422
                    ),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // delete page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // move page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
            <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
                <content><name><![CDATA[News]]></name></content>
            </item>
        </item>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h3>News</h3>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // copy page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
        <item id="copy_pagemanager-1" title="About" data-pdattr="1" class=""
                rel="new">
            <content><name><![CDATA[DUPLICATE HEADING 1]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // flip page data attribute
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="0" class="" rel=""
            state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="0" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="1" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '1')
                )
            ),
            array( // no rename
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="WelcomeNew" data-pdattr="1"
            class="pagemanager-no-rename" rel="" state="closed">
        <content><name><![CDATA[WelcomeNew]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" class="" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" class="" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
        );
    }

    /**
     * Tests parsing.
     *
     * @param string $xml              An XML string.
     * @param array  $expectedContent  An array of expected content.
     * @param array  $expectedPageData An array of expected page data.
     *
     * @dataProvider dataForParse
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    public function testParse($xml, $expectedContent, $expectedPageData)
    {
        global $pd_router;

        $this->parser->parse($xml);
        $this->assertEquals($expectedContent, $this->parser->getContents());
        $this->assertEquals($expectedPageData, $this->parser->getPageData());
    }

}

?>
