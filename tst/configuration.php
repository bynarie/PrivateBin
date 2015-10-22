<?php
class configurationTest extends PHPUnit_Framework_TestCase
{
    private $_options = array(
        'main' => array(
            'discussion' => true,
            'opendiscussion' => false,
            'password' => true,
            'fileupload' => false,
            'burnafterreadingselected' => false,
            'defaultformatter' => 'plaintext',
            'syntaxhighlightingtheme' => null,
            'sizelimit' => 2097152,
            'template' => 'bootstrap',
            'notice' => '',
            'base64version' => '2.1.9',
            'languageselection' => false,
            'languagedefault' => '',
        ),
        'expire' => array(
            'default' => '1week',
            'clone' => true,
        ),
        'expire_options' => array(
            '5min' => 300,
            '10min' => 600,
            '1hour' => 3600,
            '1day' => 86400,
            '1week' => 604800,
            '1month' => 2592000,
            '1year' => 31536000,
            'never' => 0,
        ),
        'formatter_options' => array(
            'plaintext' => 'Plain Text',
            'syntaxhighlighting' => 'Source Code',
            'markdown' => 'Markdown',
        ),
        'traffic' => array(
            'limit' => 10,
            'header' => null,
            'dir' => '../data',
        ),
        'model' => array(
            'class' => 'zerobin_data',
        ),
        'model_options' => array(
            'dir' => '../data',
        ),
    );

    public function setUp()
    {
        /* Setup Routine */
        helper::confBackup();
    }

    public function tearDown()
    {
        /* Tear Down Routine */
        helper::confRestore();
    }

    public function testDefaultConfigFile()
    {
        $this->assertTrue(copy(CONF . '.bak', CONF), 'copy default configuration file');
        $conf = new configuration;
        $this->assertEquals($this->_options, $conf->get(), 'default configuration is correct');
    }

    public function testHandleFreshConfigFile()
    {
        helper::createIniFile(CONF, $this->_options);
        $conf = new configuration;
        $this->assertEquals($this->_options, $conf->get(), 'newly generated configuration is correct');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 2
     */
    public function testHandleBlankConfigFile()
    {
        file_put_contents(CONF, '');
        $conf = new configuration;
    }

    public function testHandleMinimalConfigFile()
    {
        file_put_contents(CONF, '[main]' . PHP_EOL . '[model]');
        $conf = new configuration;
        $this->assertEquals($this->_options, $conf->get(), 'returns correct defaults on empty file');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 3
     */
    public function testHandleInvalidSection()
    {
        file_put_contents(CONF, '[main]' . PHP_EOL . '[model]');
        $conf = new configuration;
        $conf->getKey('foo', 'bar');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 4
     */
    public function testHandleInvalidKey()
    {
        file_put_contents(CONF, '[main]' . PHP_EOL . '[model]');
        $conf = new configuration;
        $conf->getKey('foo');
    }

    public function testHandleGetKey()
    {
        file_put_contents(CONF, '[main]' . PHP_EOL . '[model]');
        $conf = new configuration;
        $this->assertEquals($this->_options['main']['sizelimit'], $conf->getKey('sizelimit'), 'get default size');
    }

    public function testHandleWrongTypes()
    {
        $this->_options['main']['syntaxhighlightingtheme'] = 'foo';
        $options = $this->_options;
        $options['main']['discussion'] = 'true';
        $options['main']['opendiscussion'] = 0;
        $options['main']['password'] = -1; // evaluates to TRUE
        $options['main']['fileupload'] = 'false';
        $options['expire_options']['foo'] = 'bar';
        $options['formatter_options'][] = 'foo';
        helper::createIniFile(CONF, $options);
        $conf = new configuration;
        $this->assertEquals($this->_options, $conf->get(), 'incorrect types are corrected');
    }
}
