<?php
/**
 *  Config.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */

/**
 *  Cascade_Facade_Config
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */
final class Cascade_Facade_Config
    extends Cascade_Facade_Facade
{
    // ----[ Methods ]------------------------------------------------
    // {{{ getAll
    /**
     *  設定情報を全て取得する
     *
     *  @param   string  スキーマ名
     *  @return  array   設定情報
     */
    public /* array */
        function getAll(/* string */ $df_name)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_Config_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_GET_ALL');
        $criteria->df_name = $df_name;
        // ステートメント構築
        $stmt = new Cascade_DB_Config_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ get
    /**
     *  指定セクションの値を取得する
     *
     *  @param   string  スキーマ名
     *  @param   string  セクション
     *  @param   mixed   (optional) 変数名
     *  @return  mixed   指定セクションに格納された値を取得する。
     */
    public static /* mixed */
        function get(/* string */ $df_name,
                     /* string */ $section,
                     /* mixed  */ $name = NULL)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_Config_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_GET');
        $criteria->df_name = $df_name;
        $criteria->section = $section;
        $criteria->name    = $name;
        // ステートメント構築
        $stmt = new Cascade_DB_Config_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ count
    /**
     *  セクション数を取得する
     *
     *  @param   string  スキーマ名
     *  @return  mixed   セクション数
     */
    public static /* mixed */
        function count(/* string  */ $df_name)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_Config_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_COUNT');
        $criteria->df_name = $df_name;
        // ステートメント構築
        $stmt = new Cascade_DB_Config_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
};