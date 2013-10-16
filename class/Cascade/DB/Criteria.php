<?php
/**
 *  Criteria.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */

/**
 *  [抽象クラス] データ抽出条件
 *
 *  データ抽出条件インターフェースを使用した抽象クラス定義。
 *   - クラス変数として定義された値へのアクセスをサポートする。
 *   - 未定義変数へのアクセスは例外が発生する。
 *  <pre>
 *     $criteria = new XXXXCriteria;
 *     echo $criteria->var_1;
 *     echo $criteria->var_2;
 *  </pre>
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */
abstract class Cascade_DB_Criteria
    extends    Cascade_Object
    implements Serializable
{
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __get
    /**
     *  変数値を取得する
     *
     *  クラスの内部変数値を取得する。<br/>
     *  変数定義が存在しない場合は例外が発生する。
     *
     *  @throws  Cascade_Exception_DBException
     *  @param   string  変数名
     *  @return  mixed   変数値
     */
    public final /* mixed */
        function __get(/* string */ $name)
    {
        if (property_exists($this, $name) === FALSE) {
            $ex_msg  = 'Permission denied to get criteria {name} %s';
            $ex_msg  = sprintf($ex_msg, $name);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        return $this->$name;
    }
    // }}}
    // {{{ __set
    /**
     *  変数値を設定する
     *
     *  クラスの内部変数値を設定する。<br/>
     *  変数定義が存在しない場合は例外が発生する。
     *
     *  @throws  Cascade_Exception_DBException
     *  @param   string  変数名
     *  @param   mixed   変数値
     */
    public final /* void */
        function __set(/* string */ $name,
                       /* mixed  */ $val)
    {
        if (property_exists($this, $name) === FALSE) {
            $ex_msg  = 'Permission denied to set criteria {name} %s';
            $ex_msg  = sprintf($ex_msg, $name);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        $this->$name = $val;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ serialize
    /**
     *  オブジェクトを文字列であらわしたものを返します
     */
    public /* string */
        function serialize(/* void */)
    {
        $data   = array();
        $filter = ReflectionProperty::IS_PUBLIC
            |     ReflectionProperty::IS_PROTECTED
            |     ReflectionProperty::IS_PRIVATE;
        foreach ($this->getProperties($filter) as $prop) {
            $name        = $prop->getName();
            $data[$name] = $this->$name;
        }
        return serialize($data);
    }
    // }}}
    // {{{ unserialize
    /**
     *  オブジェクトのアンシリアライズ時にコールされます
     */
    public /* void */
        function unserialize(/* string */ $serialized)
    {
        parent::__construct();
        $data = unserialize($serialized);
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }
    // }}}
};
