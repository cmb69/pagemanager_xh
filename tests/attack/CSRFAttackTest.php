<?php

/**
 * The environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. / or /cmsimple_xh/).
 *
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Pagemanager;

class CSRFAttackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var resource
     */
    protected $curlHandle;

    /**
     * @var string
     */
    protected $cookieFile;

    public function setUp()
    {
        $this->url = 'http://localhost' . getenv('CMSIMPLEDIR');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CC');

        $this->curlHandle = curl_init($this->url . '?&login=true&keycut=test');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($this->curlHandle);
        curl_close($this->curlHandle);
    }

    /**
     * @param array $fields
     */
    protected function setCurlOptions($fields)
    {
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            //CURLOPT_COOKIEJAR => $this->cookieFile
        );
        curl_setopt_array($this->curlHandle, $options);
    }

    /**
     * @return array
     */
    public function dataForAttack()
    {
        return array(
            array(
                array(
                      'admin' => 'plugin_main',
                      'action' => 'plugin_save'
                ),
                '&pagemanager'
            )
        );
    }

    /**
     * @dataProvider dataForAttack
     * @param array $fields
     * @param string $queryString
     */
    public function testAttack($fields, $queryString = null)
    {
        $url = $this->url . (isset($queryString) ? '?' . $queryString : '');
        $this->curlHandle = curl_init($url);
        $this->setCurlOptions($fields);
        curl_exec($this->curlHandle);
        $actual = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        curl_close($this->curlHandle);
        $this->assertEquals(403, $actual);
    }
}
