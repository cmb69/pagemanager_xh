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

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_MockFunction;

class JSONProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JSONProcessor
     */
    private $parser;

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

    protected function setUp()
    {
        global $cf;

        $cf['uri']['word_separator'] = '_';
        $contents = array(
            '<!--XH_ml1:Welcome-->Welcome to my website!',
            '<!--XH_ml2:About-->About me',
            '<!--XH_ml1:News-->Here are some news.'
        );
        $pdattrName = 'show';
        $this->setUpPDRouterStub();
        $this->subject = new JSONProcessor($contents, $pdattrName);
        $timeMock = new PHPUnit_Extensions_MockFunction('time', $this->subject);
        $timeMock->expects($this->any())->will($this->returnValue(1420903422));
    }

    /**
     * @return array
     */
    public function dataForProcess()
    {
        return array(
            array( // unmodified
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1",
        "class": ""
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "1",
            "class": ""
        },
        "children": []
    }]
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "0",
        "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml1:News-->Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // insert page
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1",
        "class": ""
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "1",
            "class": ""
        },
        "children": []
    }, {
        "data": "New Page",
        "attr": {
            "id": "", "title": "New Page", "data-pdattr": "1", "class": ""
        },
        "children": []
    }]
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "0", "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml2:New Page-->',
                    '<!--XH_ml1:News-->Here are some news.'
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
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1",
        "class": ""
    },
    "children": []
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "0", "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml1:News-->Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // move page
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1", "class": ""
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "1", "class": ""
        },
        "children": [{
            "data": "News",
            "attr": {
                "id": "pagemanager-2", "title": "News", "data-pdattr": "0",
                "class": ""
            },
            "children": []
        }]
    }]
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml3:News-->Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // copy page
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1", "class": ""
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "1", "class": ""
        },
        "children": []
    }, {
        "data": "About",
        "attr": {
            "id": "copy_pagemanager-1", "title": "About", "data-pdattr": "1",
            "class": ""
            },
        "children": []
    }]
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "0", "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml1:News-->Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // flip page data attribute
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "0", "class": ""
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "0",
            "class": ""
        },
        "children": []
    }]
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "1", "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml1:News-->Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '1')
                )
            ),
            array( // no rename
                <<<JSON
[{
    "data": "Welcome",
    "attr": {
        "id": "pagemanager-0", "title": "Welcome", "data-pdattr": "1",
        "class": "pagemanager-no-rename"
    },
    "children": [{
        "data": "About",
        "attr": {
            "id": "pagemanager-1", "title": "About", "data-pdattr": "1", "class": ""
        },
        "children": []
    }]
}, {
    "data": "News",
    "attr": {
        "id": "pagemanager-2", "title": "News", "data-pdattr": "0", "class": ""
    },
    "children": []
}]
JSON
                , array(
                    '<!--XH_ml1:Welcome-->Welcome to my website!',
                    '<!--XH_ml2:About-->About me',
                    '<!--XH_ml1:News-->Here are some news.'
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
     * @dataProvider dataForProcess
     * @param string $json
     * @param array $expectedContent
     * @param array $expectedPageData
     */
    public function testProcess($json, $expectedContent, $expectedPageData)
    {
        $this->subject->process($json);
        $this->assertEquals($expectedContent, $this->subject->getContents());
        $this->assertEquals($expectedPageData, $this->subject->getPageData());
    }
}
