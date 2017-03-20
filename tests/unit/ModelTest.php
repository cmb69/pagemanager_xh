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
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Model
     */
    private $model;

    protected function setUp()
    {
        global $h, $cl, $l, $tx;

        $h = array(
            'Welcome',
            'Subpage',
            'Subpage',
            '',
            'Foo',
            'Foo &amp; bar',
            'Foo &nbsp; bar'
        );
        $cl = count($h);
        $l = array(1, 2, 2, 2, 1, 2, 2);

        $tx['toc']['empty'] = 'EMPTY HEADING';

        $this->model = new Model();
    }

    public function testGetHeadings()
    {
        $expected = array(
            'Welcome',
            'Subpage',
            'Subpage', // not "DUPLICATE HEADING 1"
            'EMPTY HEADING 1',
            'Foo',
            'Foo &amp; bar',
            "Foo \xC2\xA0 bar"
        );
        $this->model->getHeadings();
        $actual = $this->model->headings;
        $this->assertEquals($expected, $actual);
    }

    public function testThemes()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth['folder']['plugins'] = vfsStream::url('test') . '/';
        $path = $pth['folder']['plugins'] . 'pagemanager/jstree/themes/';
        mkdir($path, 0777, true);
        $expected = array('foo', 'bar', 'baz');
        foreach ($expected as $theme) {
            mkdir("$path/$theme");
        }
        file_put_contents("$path/foobar", '');
        $actual = $this->model->themes();
        $this->assertEquals($expected, $actual);
    }

    public function testIsIrregular()
    {
        global $l;

        $this->assertFalse($this->model->isIrregular());
        $l = array(1, 3);
        $this->assertTrue($this->model->isIrregular());
    }
}
