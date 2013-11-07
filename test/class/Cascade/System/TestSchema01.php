<?php
/**
 *  TestSchema01.php
 *
 *  @author   Hiroki Uemura <hiroki.uemura@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_System_TestSchema01
 */
final class Cascade_System_TestSchema01
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------

    // ----[ Methods ]------------------------------------------------
    // {{{ test_replace_ident_01
    /**
     * @dataProvider provide_ident
     */
    public function test_replace_ident_01($ident, $expected)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('protected method test requires php 5.3');
        }
        $method = new ReflectionMethod('Cascade_System_Schema', 'replaceIdent');
        $method->setAccessible(TRUE);
        $this->assertSame($expected, $method->invoke(NULL, $ident));
    }
    // }}}
    // {{{ provide_ident
    public function provide_ident()
    {
        return array(
            array('aaa_bbb_ccc\\ddd', 'Aaa_Bbb_Ccc\\Ddd'),
            array('aaa__bbb_ccc\\ddd', 'Aaa__Bbb_Ccc\\Ddd'),
            array('_aaa_bbb_ccc\\ddd', '_Aaa_Bbb_Ccc\\Ddd'),
            array('\\aaa_bbb_ccc\\ddd', '\\Aaa_Bbb_Ccc\\Ddd'),
            array('__aaa_bbb_ccc\\\\ddd', '__Aaa_Bbb_Ccc\\\\Ddd'),
            array('\\\\aaa_bbb__ccc\\ddd', '\\\\Aaa_Bbb__Ccc\\Ddd'),
        );
    }
    // }}}
};
