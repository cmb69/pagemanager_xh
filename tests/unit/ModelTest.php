<?php

/**
 * Testing the model class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

require_once './vendor/autoload.php';

/**
 * The file under test.
 */
require_once './classes/Model.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A test case to for the model class.
 *
 * @category Testing
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var Pagemanager_Model
     */
    protected $model;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The content of the pages.
     * @global int   The number of pages.
     * @global array The levels of the pages.
     * @global array The configuration of the core.
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $c, $cl, $l, $cf, $tx;

        $c = array(
            '<h1>Welcome</h1>',
            '<h2>Subpage</h2>',
            '<h2>Subpage</h2>',
            '<h2></h2>',
            '<h1>F<em>o</em>o</h1>',
            '<h2>Foo &amp; bar</h2>',
            '<h2>Foo &nbsp; bar</h2>'
        );
        $cl = count($c);
        $l = array(1, 2, 2, 2, 1, 2, 2);

        $cf['menu']['levels'] = '3';
        $tx['toc']['empty'] = 'EMPTY HEADING';

        $this->model = new Pagemanager_Model();
    }

    /**
     * Tests getHeadings().
     *
     * @return void
     */
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

    /**
     * Tests themes().
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     */
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

    /**
     * Tests isIrregular().
     *
     * @return void
     *
     * @global array The levels of the pages.
     */
    public function testIsIrregular()
    {
        global $l;

        $this->assertFalse($this->model->isIrregular());
        $l = array(1, 3);
        $this->assertTrue($this->model->isIrregular());
    }
}

?>
