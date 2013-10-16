<?php
/**
 *  Object.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Object
 */
abstract class Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    // CLASS
    const IS_IMPLICIT_ABSTRACT = ReflectionClass::IS_IMPLICIT_ABSTRACT; // 0x010
    const IS_EXPLICIT_ABSTRACT = ReflectionClass::IS_EXPLICIT_ABSTRACT; // 0x020
    const IS_FINAL             = ReflectionClass::IS_FINAL;             // 0x040
    // METHOD
    const METHOD_IS_PUBLIC     = ReflectionMethod::IS_PUBLIC;           // 0x100
    const METHOD_IS_PROTECTED  = ReflectionMethod::IS_PROTECTED;        // 0x200
    const METHOD_IS_PRIVATE    = ReflectionMethod::IS_PRIVATE;          // 0x400
    const METHOD_IS_STATIC     = ReflectionMethod::IS_STATIC;           // 0x001
    const METHOD_IS_ABSTRACT   = ReflectionMethod::IS_ABSTRACT;         // 0x002
    const METHOD_IS_FINAL      = ReflectionMethod::IS_FINAL;            // 0x004

    // ----[ Properties ]---------------------------------------------
    /**
     *  クラス名
     *  @var string
     */
    private $_name   = NULL;

    /**
     *  リフレクション
     *  @var ReflectionClass
     */
    private $_static = NULL;

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  constructor
     */
    public /* void */
        function __construct(/* void */)
    {
        $this->_static = new ReflectionClass($this);
        $this->_name   = $this->_static->getName();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ __call
    /**
     *  This method is triggered
     *  when invoking inaccessible methods in an object context.
     *
     *  @param  $method  The name of the method
     *  @param  $args    The parameters passed to the method
     *  @return          The invoke result
     */
    public /* mixed */
        function __call(/* string */ $method,
                        /* array  */ $args)
    {
        if (method_exists($this->_static, $method)) {
            try {
                return call_user_func_array(array($this->_static, $method), $args);
            } catch (Exception $ex) {
                throw new Cascade_Exception_Exception($ex->getMessage(), $ex->getCode());
            }
        }
        $ex_msg = 'Fatal error: Call to undefined method %s::%s()';
        $ex_msg = sprintf($ex_msg, $this->_static->getName(), $method);
        trigger_error($ex_msg, E_USER_ERROR);
        throw new Cascade_Exception_Exception($ex_msg);
    }
    // }}}
}
