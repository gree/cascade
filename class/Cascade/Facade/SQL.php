<?php
/**
 *  SQL.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */

/**
 *  Cascade_Facade_SQL
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */
final class Cascade_Facade_SQL
    extends Cascade_Facade_Facade
{
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定KEYよりデータ取得
     *
     *  @param   string   スキーマ名
     *  @param   string   条件となるKEYの値
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function get(/* string  */ $df_name,
                     /* string  */ $key,
                     /* mixed   */ $hint       = NULL,
                     /* boolean */ $use_master = FALSE)
    {
        return $this->getEx($df_name, $extra_dsn = NULL, $key, $hint, $use_master);
    }
    // }}}
    // {{{ getEx
    /**
     *  指定KEYよりデータ取得 (拡張DSNが指定可能)
     *
     *  @param   string   スキーマ名
     *  @param   string   拡張DSN
     *  @param   string   条件となるKEYの値
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function getEx(/* string  */ $df_name,
                       /* string  */ $extra_dsn,
                       /* string  */ $key,
                       /* mixed   */ $hint       = NULL,
                       /* boolean */ $use_master = FALSE)
    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_GET');
        $criteria->df_name    = $df_name;
        $criteria->params     = $key;
        $criteria->hint       = $hint;
        $criteria->use_master = ($use_master !== FALSE) ? TRUE : FALSE;
        $criteria->extra_dsn  = $extra_dsn;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ mget
    /**
     *  指定KEYよりデータをリスト取得
     *
     *  @param   string   スキーマ名
     *  @param   array    条件となるKEYのリスト値
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function mget(/* string  */ $df_name,
                      /* array   */ $keys,
                      /* mixed   */ $hint       = NULL,
                      /* boolean */ $use_master = FALSE)
    {
        return $this->mgetEx($df_name, $extra_dsn = NULL, $keys, $hint, $use_master);
    }
    // }}}
    // {{{ mgetEx
    /**
     *  指定KEYよりデータをリスト取得 (拡張DSNが指定可能)
     *
     *  @param   string   スキーマ名
     *  @param   string   拡張DSN
     *  @param   array    条件となるKEYのリスト値
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function mgetEx(/* string  */ $df_name,
                        /* string  */ $extra_dsn,
                        /* array   */ $keys,
                        /* mixed   */ $hint       = NULL,
                        /* boolean */ $use_master = FALSE)
    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_MGET');
        $criteria->df_name    = $df_name;
        $criteria->params     = $keys;
        $criteria->hint       = $hint;
        $criteria->use_master = ($use_master !== FALSE) ? TRUE : FALSE;
        $criteria->extra_dsn  = $extra_dsn;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ findFirst
    /**
     *  指定条件よりデータを取得
     *
     *  @param   string   スキーマ名
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function findFirst(/* string  */ $df_name,
                           /* string  */ $stmt_name,
                           /* array   */ $params     = NULL,
                           /* mixed   */ $hint       = NULL,
                           /* boolean */ $use_master = FALSE)
    {
        return $this->findFirstEx($df_name, $extra_dsn = NULL, $stmt_name, $params, $hint, $use_master);
    }
    // }}}
    // {{{ findFirstEx
    /**
     *  指定条件よりデータを取得 (拡張DSNが指定可能)
     *
     *  @param   string   スキーマ名
     *  @param   string   拡張DSN
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function findFirstEx(/* string  */ $df_name,
                             /* string  */ $extra_dsn,
                             /* string  */ $stmt_name,
                             /* array   */ $params     = NULL,
                             /* mixed   */ $hint       = NULL,
                             /* boolean */ $use_master = FALSE)
    {
        $tmp = $this->findEx($df_name, $extra_dsn, $stmt_name, $params, $offset = NULL, $limit = 1, $hint, $use_master);
        $row = current($tmp);
        return ($row !== FALSE) ? $row : NULL;
    }
    // }}}
    // {{{ find
    /**
     *  指定条件よりデータをリスト取得
     *
     *  @param   string   スキーマ名
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   int      (optional) オフセット
     *  @param   int      (optional) データ取得上限
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function find(/* string  */ $df_name,
                      /* string  */ $stmt_name,
                      /* array   */ $params     = NULL,
                      /* int     */ $offset     = NULL,
                      /* int     */ $limit      = NULL,
                      /* mixed   */ $hint       = NULL,
                      /* boolean */ $use_master = FALSE)
    {
        return $this->findEx($df_name, $extra_dsn = NULL, $stmt_name, $params, $offset, $limit, $hint, $use_master);
    }
    // }}}
    // {{{ findEx
    /**
     *  指定条件よりデータをリスト取得 (拡張DSNが指定可能)
     *
     *  @param   string   スキーマ名
     *  @param   string   拡張DSN
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   int      (optional) オフセット
     *  @param   int      (optional) データ取得上限
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function findEx(/* string  */ $df_name,
                        /* string  */ $extra_dsn,
                        /* string  */ $stmt_name,
                        /* array   */ $params     = NULL,
                        /* int     */ $offset     = NULL,
                        /* int     */ $limit      = NULL,
                        /* mixed   */ $hint       = NULL,
                        /* boolean */ $use_master = FALSE)
    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_FIND');
        $criteria->df_name    = $df_name;
        $criteria->stmt_name  = $stmt_name;
        $criteria->params     = $params;
        $criteria->offset     = $offset;
        $criteria->limit      = $limit;
        $criteria->hint       = $hint;
        $criteria->use_master = ($use_master !== FALSE) ? TRUE : FALSE;
        $criteria->extra_dsn  = $extra_dsn;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ toValue
    /**
     *  指定条件より単一データを取得
     *
     *  @param   string   スキーマ名
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function toValue(/* string  */ $df_name,
                         /* string  */ $stmt_name,
                         /* array   */ $params     = NULL,
                         /* mixed   */ $hint       = NULL,
                         /* boolean */ $use_master = FALSE)
    {
        return $this->toValueEx($df_name, $extra_dsn = NULL, $stmt_name, $params, $hint, $use_master);
    }
    // }}}
    // {{{ toValueEx
    /**
     *  指定条件より単一データを取得 (拡張DSNが指定可能)
     *
     *  @param   string   スキーマ名
     *  @param   string   拡張DSN
     *  @param   string   ステートメント名
     *  @param   array    データ取得条件
     *  @param   mixed    (optional) ShardSelectorヒント
     *  @param   boolean  (optional) TRUE:マスター接続
     *  @return  mixed    取得結果
     */
    public /* mixed */
        function toValueEx(/* string  */ $df_name,
                           /* string  */ $extra_dsn,
                           /* string  */ $stmt_name,
                           /* array   */ $params     = NULL,
                           /* mixed   */ $hint       = NULL,
                           /* boolean */ $use_master = FALSE)
    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_VALUE');
        $criteria->df_name    = $df_name;
        $criteria->stmt_name  = $stmt_name;
        $criteria->params     = $params;
        $criteria->hint       = $hint;
        $criteria->use_master = ($use_master !== FALSE) ? TRUE : FALSE;
        $criteria->extra_dsn  = $extra_dsn;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ execute
    /**
     *  処理の実行を行う
     *
     *  @param   string  DataFormat名
     *  @param   string  ステートメント名
     *  @param   array   データ取得条件
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  mixed   取得結果
     */
    public /* mixed */
        function execute(/* string  */ $df_name,
                         /* string  */ $stmt_name,
                         /* array   */ $params = NULL,
                         /* mixed   */ $hint   = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_EXEC');
        $criteria->df_name    = $df_name;
        $criteria->stmt_name  = $stmt_name;
        $criteria->params     = $params;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ lastInsertId
    /**
     *  最後に挿入された行のシーケンスの値を返す
     *
     *  @param   string  DataFormat名
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  int     シーケンス値
     */
    public /* mixed */
        function lastInsertId(/* string  */ $df_name,
                              /* mixed   */ $hint = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_LAST_INSERT_ID');
        $criteria->df_name    = $df_name;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ beginTransaction
    /**
     *  トランザクションを開始する
     *
     *  @param   string  DataFormat名
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  int     シーケンス値
     */
    public /* mixed */
        function beginTransaction(/* string  */ $df_name,
                                  /* mixed   */ $hint = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_BEGIN');
        $criteria->df_name    = $df_name;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ commit
    /**
     *  トランザクションをコミットする
     *
     *  @param   string  DataFormat名
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  int     シーケンス値
     */
    public /* mixed */
        function commit(/* string  */ $df_name,
                        /* mixed   */ $hint = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_COMMIT');
        $criteria->df_name    = $df_name;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ rollBack
    /**
     *  トランザクションをロールバックする
     *
     *  @param   string  DataFormat名
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  int     シーケンス値
     */
    public /* mixed */
        function rollBack(/* string  */ $df_name,
                          /* mixed   */ $hint = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_ROLLBACK');
        $criteria->df_name    = $df_name;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ createDB
    /**
     *  データベースを作成する
     *
     *  @param   string  DataFormat名
     *  @param   mixed   (optional) ShardSelectorヒント
     *  @return  int     結果
     */
    public /* mixed */
        function createDB(/* string  */ $df_name,
                          /* mixed   */ $hint = NULL)

    {
        // リクエスト構築
        $criteria             = new Cascade_DB_SQL_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_CREATE_DB');
        $criteria->df_name    = $df_name;
        $criteria->hint       = $hint;
        $criteria->use_master = TRUE;
        // ステートメント構築
        $stmt = new Cascade_DB_SQL_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
};