<?php

/**
 * Copyright 2011-2019 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;

class JSONProcessorTest extends TestCase
{
    /**
     * @var JSONProcessor
     */
    private $parser;

    private function setUpPDRouterStub()
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

    protected function setUp(): void
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
        $this->subject = new JSONProcessor($contents, $pdattrName, 1420903422);
    }

    /**
     * @return array[]
     */
    public function dataForProcess()
    {
        return array(
            array( // unmodified
                <<<JSON
[{
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": true},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }]
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": false},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": true},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }, {
        "text": "New Page",
        "id": "",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }]
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": false},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": true},
    "children": []
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": false},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": true},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": true},
        "children": [{
            "text": "News",
            "id": "pagemanager_2",
            "type": "default",
            "state": {"checked": false},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": true},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }, {
        "text": "About",
        "id": "pagemanager_1_copy_1234567890",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }]
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": false},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "default",
    "state": {"checked": false},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": false},
        "children": []
    }]
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": true},
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
    "text": "Welcome",
    "id": "pagemanager_0",
    "type": "unrenameable",
    "state": {"checked": true},
    "children": [{
        "text": "About",
        "id": "pagemanager_1",
        "type": "default",
        "state": {"checked": true},
        "children": []
    }]
}, {
    "text": "News",
    "id": "pagemanager_2",
    "type": "default",
    "state": {"checked": false},
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
     * @param string[] $expectedContent
     * @param array[] $expectedPageData
     */
    public function testProcess($json, array $expectedContent, array $expectedPageData)
    {
        $this->subject->process($json);
        $this->assertEquals($expectedContent, $this->subject->getContents());
        $this->assertEquals($expectedPageData, $this->subject->getPageData());
    }
}
