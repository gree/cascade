<?php
/**
 *  Statement.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */

/**
 *  Cascade_DB_SQL_Statement
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */
final class Cascade_DB_SQL_Statement
    extends Cascade_DB_Statement
{
    // ----[ Class Constants ]----------------------------------------
    /** データ取得形式 : 添字配列 */
    const FETCH_MODE_NUM     = 0x01;
    /** データ取得形式 : 連想配列 */
    const FETCH_MODE_ASSOC   = 0x02;

    // ----[ Class Constants ]----------------------------------------
    /** バインド変数情報 : 変数型         */
    const BIND_TYPE          = 'type';
    /** バインド変数情報 : 変数値         */
    const BIND_VAL           = 'value';
    /** バインド変数情報 : 変数型(変換前) */
    const BIND_ORIG_TYPE     = 'orig-type';
    /** バインド変数情報 : 変数値(変換前) */
    const BIND_ORIG_VAL      = 'orig-value';

    // ----[ Class Constants ]----------------------------------------
    /** バインド変数型 : NULL   */
    const VAR_TYPE_IS_NULL   = 'NULL';
    /** バインド変数型 : bool   */
    const VAR_TYPE_IS_BOOL   = 'boolean';
    /** バインド変数型 : long   */
    const VAR_TYPE_IS_LONG   = 'integer';
    /** バインド変数型 : double */
    const VAR_TYPE_IS_DOUBLE = 'double';
    /** バインド変数型 : string */
    const VAR_TYPE_IS_STRING = 'string';

    // ----[ Class Constants ]----------------------------------------
    /** プレスフォルダ情報 : ステートメント   */
    const P_HOLDER_STMT      = 'stmt';
    /** プレスフォルダ情報 : バインド変数     */
    const P_HOLDER_BIND      = 'bind';
    /** プレスフォルダ情報 : リクエスト変数名 */
    const P_HOLDER_NAME      = 'name';

    // ----[ Properties ]---------------------------------------------
    /**
     *  データ抽出条件
     *  @var  Cascade_DB_SQL_Criteria
     */
    protected $criteria           = NULL;

    /**
     *  データ・フォーマット
     *  @var  Cascade_DB_SQL_DataFormat
     */
    protected $data_format        = NULL;

    // ----[ Properties ]---------------------------------------------
    /**
     *  内部変数値 : 実行クエリー文字列
     *  @var  string
     */
    protected $query              = NULL;

    /**
     *  内部変数値 : バインド変数値
     *  @var  array
     */
    protected $bind_values        = array();

    /**
     *  内部変数値 : バインド変数値(詳細情報)
     *  @var  array
     */
    protected $bind_value_details = array();

    // ----[ Methods ]------------------------------------------------
    // {{{ init
    /**
     *  初期化処理
     */
    protected /* void */
        function init(/* void */)
    {
        // 内部変数の初期化
        $this->query       = NULL;
        $this->bind_values = array();
        // 抽出条件インスタンスを確認
        if (($this->criteria instanceof Cascade_DB_SQL_Criteria) === FALSE) {
            $ex_msg = 'Invalid Criteria type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($this->criteria));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // データ・フォーマットを確認
        if (($this->data_format instanceof Cascade_DB_SQL_DataFormat) === FALSE) {
            $ex_msg = 'Invalid DataFormat type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($data_fmt));
            throw new Cascade_Exception_DBException($ex_msg);
        }
    }
    // }}}
    // {{{ prepare
    /**
     *  実行に必要な準備をする
     *
     *  ステートメント実行に必要となる準備を行う。
     *   - 実行クエリーの準備
     *   - バインド変数値の準備
     */
    protected /* void */
        function prepare(/* void */)
    {
        // 初期化
        $this->init();
        // 実行クエリー、バインド変数の実行準備
        switch ($this->criteria->type) {
        case Cascade_DB_SQL_Criteria::TYPE_IS_GET:
            $this->prepareForGet();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_MGET:
            $this->prepareForMultiGet();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_FIND:
        case Cascade_DB_SQL_Criteria::TYPE_IS_VALUE:
        case Cascade_DB_SQL_Criteria::TYPE_IS_EXEC:
            $this->prepareForGeneric();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_CREATE_DB:
            $this->prepareForCreateDB();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_LAST_INSERT_ID:
        case Cascade_DB_SQL_Criteria::TYPE_IS_BEGIN:
        case Cascade_DB_SQL_Criteria::TYPE_IS_COMMIT:
        case Cascade_DB_SQL_Criteria::TYPE_IS_ROLLBACK:
            break;
        default:
            $ex_msg  = 'Unsupported execution type of Criteria {type} %d';
            $ex_msg  = sprintf($ex_msg, $this->criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
    }
    // }}}
    // {{{ prepareForGet
    /**
     *  ステートメントの実行準備
     *
     *  ステートメント状態を実行可能な状態に構築する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_GET}
     */
    protected /* void */
        function prepareForGet(/* void */)
    {
        // 抽出KEYを取得
        $cardinal_key = $this->data_format->getCardinalKey();
        if ($cardinal_key === NULL) {
            $ex_msg  = 'Not found required primary_key, or fetch_key {df} %s';
            $ex_msg  = sprintf($ex_msg, get_class($this->data_format));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // 実行準備
        if (is_array($cardinal_key) === FALSE) {
            // 単体キー
            $query  = 'SELECT * FROM __TABLE_NAME__ WHERE %1$s = :%1$s';
            $query  = sprintf($query, $cardinal_key);
            $params = array($cardinal_key => $this->criteria->params);
        } else {
            // 複合キー
            $condition = NULL;
            foreach ($cardinal_key as $_key) {
                $condition = ($condition === NULL)
                    ? sprintf(         '%1$s = :%1$s', $_key)
                    : sprintf('%2$s AND %1$s = :%1$s', $_key, $condition);
            }
            $query  = 'SELECT * FROM __TABLE_NAME__ WHERE ' . $condition;
            $params = $this->criteria->params;
        }
        $this->processQuery($query, $params);
    }
    // }}}
    // {{{ prepareForMultiGet
    /**
     *  ステートメントの実行準備
     *
     *  ステートメント状態を実行可能な状態に構築する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_MGET}
     */
    protected /* array */
        function prepareForMultiGet(/* void */)
    {
        // 抽出KEYを取得
        $cardinal_key = $this->data_format->getCardinalKey();
        if ($cardinal_key === NULL) {
            $ex_msg  = 'Not found required primary_key, or fetch_key {df} %s';
            $ex_msg  = sprintf($ex_msg, get_class($this->data_format));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // 実行準備
        if (is_array($cardinal_key) === FALSE) {
            // 単体キー
            $query = 'SELECT * FROM __TABLE_NAME__ WHERE %1$s IN (:%1$s)';
            $query  = sprintf($query, $cardinal_key);
            $params = array($cardinal_key => $this->criteria->params);
        } else {
            // 複合キー
            $condition_01 = NULL;
            $condition_02 = NULL;
            foreach ($cardinal_key as $_key) {
                $condition_01 = ($condition_01 === NULL)
                    ? sprintf('%1$s',       $_key)
                    : sprintf('%2$s, %1$s', $_key, $condition_01);
                $condition_02 = ($condition_02 === NULL)
                    ? sprintf(':%1$s',       $_key)
                    : sprintf('%2$s, :%1$s', $_key, $condition_02);
            }
            $query  = 'SELECT * FROM __TABLE_NAME__ WHERE (%1$s) IN (:cardinal_key<%2$s>)';
            $query  = sprintf($query, $condition_01, $condition_02);
            $params = array('cardinal_key' => $this->criteria->params);
        }
        $this->processQuery($query, $params);
    }
    // }}}
    // {{{ prepareForGeneric
    /**
     *  ステートメントの実行準備
     *
     *  ステートメント状態を実行可能な状態に構築する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_FIND}
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_VALUE}
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_EXEC}
     */
    protected /* array */
        function prepareForGeneric(/* void */)
    {
        $query  = $this->data_format->getDynamicQuery($this->criteria);
        $params = $this->criteria->params;
        $this->processQuery($query, $params);
    }
    // }}}
    // {{{ prepareForCreateDB
    /**
     *  ステートメントの実行準備
     *
     *  ステートメント状態を実行可能な状態に構築する。<br/>
     *  条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_CREATE_DB}
     */
    protected /* void */
        function prepareForCreateDB(/* void */)
    {
        $db_name = $this->data_format->getDatabaseName($this->criteria);
        if (!strlen($db_name)) {
            $ex_msg = 'Need to override Dataformat function ::getDatabaseName()';
            $ex_msg = sprintf($ex_msg, $this->criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        $query = "CREATE DATABASE IF NOT EXISTS {$db_name}";
        $this->processQuery($query, $params = array());
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ perform
    /**
     *  ステートメントを実行する
     *
     *  実行準備が整ったステートメントを実行する。
     *
     *  @return  mixed  実行結果
     */
    protected /* mixed */
        function perform(/* void */)
    {
        // クエリーの実行と、結果の取得
        $result     = NULL;
        $driver     = $this->getDriver();
        $is_success = TRUE;
        switch ($this->criteria->type) {
        case Cascade_DB_SQL_Criteria::TYPE_IS_LAST_INSERT_ID:
            $result = $is_success = $driver->last_insert_id();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_BEGIN:
            $result = $is_success = $driver->begin();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_COMMIT:
            $result = $is_success = $driver->commit();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_ROLLBACK:
            $result = $is_success = $driver->rollback();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_GET:
        case Cascade_DB_SQL_Criteria::TYPE_IS_MGET:
        case Cascade_DB_SQL_Criteria::TYPE_IS_FIND:
        case Cascade_DB_SQL_Criteria::TYPE_IS_VALUE:
        case Cascade_DB_SQL_Criteria::TYPE_IS_EXEC:
            $is_success = $driver->query(
                $this->query,
                $this->bind_values,
                TRUE,
                $this->getDataFormat()->getDatabaseName($this->criteria)
            );
            if ($is_success) {
                $result = $this->fetchResult();
            }
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_CREATE_DB:
            $result = $is_success = $driver->query(
                $this->query,
                $this->bind_values
            );
            break;
        default:
            $ex_msg = 'Unsupported execution type of Criteria {type} %d';
            $ex_msg = sprintf($ex_msg, $this->criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // エラー処理
        if ($is_success === FALSE) {
            $errno = $driver->get_error_code();
            $error = $driver->get_error_message();
            throw new Cascade_Exception_DriverException($error, $errno);
        }
        // 結果を返す
        return $result;
    }
    // }}}
    // {{{ fetchResult
    /**
     *  実行結果を取得する
     *
     *  実行されたステートメント結果を取得する。
     *
     *  @return  mixed  実行結果
     */
    protected /* mixed */
        function fetchResult(/* void */)
    {
        switch ($this->criteria->type) {
        case Cascade_DB_SQL_Criteria::TYPE_IS_GET:
            $result = $this->fetchResultForGet();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_MGET:
            $result = $this->fetchResultForMultiGet();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_FIND:
            $result = $this->fetchResultForFind();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_VALUE:
            $result = $this->fetchResultForValue();
            break;
        case Cascade_DB_SQL_Criteria::TYPE_IS_EXEC:
            $result = $this->fetchResultForExec();
            break;
        default:
            $ex_msg = 'Unsupported execution type of Criteria {type} %d';
            $ex_msg = sprintf($ex_msg, $this->criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        return $result;
    }
    // }}}
    // {{{ fetchResultForGet
    /**
     *  ステートメントの実行
     *
     *  ステートメントを実行して結果値を取得する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_GET}
     *
     *  @return  mixed  実行結果
     */
    protected /* array */
        function fetchResultForGet(/* void */)
    {
        // 結果データを構築
        $result     = NULL;
        $driver     = $this->getDriver();
        $fetch_mode = $this->data_format->getFetchMode();
        if ($this->data_format->isUseFetchKey() === FALSE) {
            // 主キーを使用した場合
            switch ($fetch_mode) {
            case self::FETCH_MODE_NUM:
                $result = array_values($driver->fetch());
                break;
            case self::FETCH_MODE_ASSOC:
                $result = $driver->fetch();
                break;
            default:
                $ex_msg = 'Unsupported fetch_mode {df, fetch_mode} %s %d';
                $ex_msg = sprintf($ex_msg, get_class($this->data_format, $fetch_mode));
                throw new Cascade_Exception_DBException($ex_msg);
            }
        } else {
            // データ取得キーを使用した場合
            $result = $this->fetchResultForFind();
        }
        return $result;
    }
    // }}}
    // {{{ fetchResultForMultiGet
    /**
     *  ステートメントの実行
     *
     *  ステートメントを実行して結果値を取得する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_MGET}
     *
     *  @return  mixed  実行結果
     */
    protected /* array */
        function fetchResultForMultiGet(/* void */)
    {
        // 結果データを構築
        $result      = array();
        $driver      = $this->getDriver();
        $primary_key = $this->data_format->getPrimaryKey();
        $fetch_key   = $this->data_format->getFetchKey();
        $fetch_mode  = $this->data_format->getFetchMode();
        if ($this->data_format->isUseFetchKey() === FALSE) {
            // 主キーを使用した場合
            $result = $this->fetchResultForFind();
        } else {
            // データ取得キーを使用した場合
            $result = $idx_map = array();
            while ($record = $driver->fetch($fetch_mode)) {
                $idx_01 = $this->getResultIndex($primary_key, $record);
                $idx_02 = $this->getResultIndex($fetch_key,   $record);
                switch ($fetch_mode) {
                case self::FETCH_MODE_NUM:
                    $num = isset($idx_map[$idx_02])
                        ? $idx_map[$idx_02]
                        : $idx_map[$idx_02] = count($idx_map);
                    $result[$num][] = array_values($record);
                    break;
                case self::FETCH_MODE_ASSOC:
                    if ($idx_01 === NULL) {
                        $result[$idx_02][] = $record;
                    } else {
                        $result[$idx_02][$idx_01] = $record;
                    }
                    break;
                default:
                    $ex_msg = 'Unsupported fetch_mode {df, fetch_mode} %s %d';
                    $ex_msg = sprintf($ex_msg, get_class($this->data_format, $fetch_mode));
                    throw new Cascade_Exception_DBException($ex_msg);
                }
            }
        }
        return $result;
    }
    // }}}
    // {{{ fetchResultForFind
    /**
     *  ステートメントの実行
     *
     *  ステートメントを実行して結果値を取得する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_FIND}
     *
     *  @return  mixed  実行結果
     */
    protected /* array */
        function fetchResultForFind(/* void */)
    {
        // 結果データを構築
        $result      = array();
        $driver      = $this->getDriver();
        $primary_key = $this->data_format->getPrimaryKey();
        $fetch_mode  = $this->data_format->getFetchMode();
        while ($record = $driver->fetch()) {
            switch ($fetch_mode) {
            case self::FETCH_MODE_NUM:
                $result[] = array_values($record);
                break;
            case self::FETCH_MODE_ASSOC:
                if (NULL !== ($idx = $this->getResultIndex($primary_key, $record))) {
                    $result[$idx] = $record;
                } else {
                    $result[] = $record;
                }
                break;
            default:
                $ex_msg = 'Unsupported fetch_mode {df, fetch_mode} %s %d';
                $ex_msg = sprintf($ex_msg, get_class($this->data_format, $fetch_mode));
                throw new Cascade_Exception_DBException($ex_msg);
            }
        }
        return $result;
    }
    // }}}
    // {{{ fetchResultForValue
    /**
     *  ステートメントの実行
     *
     *  ステートメントを実行して結果値を取得する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_VALUE}
     *
     *  @return  mixed  実行結果
     */
    protected /* array */
        function fetchResultForValue(/* void */)
    {
        return $this->getDriver()->fetch_one();
    }
    // }}}
    // {{{ fetchResultForExec
    /**
     *  ステートメントの実行
     *
     *  ステートメントを実行して結果値を取得する。<br/>
     *  抽出条件が下記の条件に該当する場合に実行される
     *   - {@link Cascade_DB_SQL_Criteria::TYPE_IS_EXEC}
     *
     *  @return  mixed  実行結果
     */
    protected /* array */
        function fetchResultForExec(/* void */)
    {
        return $this->getDriver()->affected_rows();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ processQuery
    /**
     *  クエリを解析する
     *
     *  @param  string  クエリ
     *  @param  array   クエリ変数値
     */
    protected /* void */
        function processQuery(/* string */ $query,
                              /* array  */ $params)
    {
        // 文字列タイプのプレースホルダを抽出する
        $pholder = array();
        $query   = preg_replace('/\s+/', ' ', $query);
        $pholder[self::P_HOLDER_STMT] = $query;
        $pholder[self::P_HOLDER_NAME] = NULL;
        $pholder[self::P_HOLDER_BIND] = array();
        preg_match_all('/[^\x5c](:([a-zA-Z0-9_]+)(?:<.+>)?)/', $query, $tokens);
        for ($pos = 0; $pos < count($tokens[0]); $pos++) {
            $ptr =& $pholder[self::P_HOLDER_BIND][$pos];
            $ptr[self::P_HOLDER_STMT] = $tokens[1][$pos];
            $ptr[self::P_HOLDER_NAME] = $tokens[2][$pos];
            $ptr[self::P_HOLDER_BIND] = array();
        }
        // 行値構成子
        foreach ($pholder[self::P_HOLDER_BIND] as $pos => &$bind) {
            $tmp = preg_replace('/:[a-zA-Z0-9_]+(?:<(.+)>)?/', '$1', $bind[self::P_HOLDER_STMT]);
            preg_match_all('/(?:^|[^\x5c])(:([a-zA-Z0-9_]+))/', $tmp, $tokens);
            for ($pos = 0; $pos < count($tokens[0]); $pos++) {
                $ptr =& $bind[self::P_HOLDER_BIND][$pos];
                $ptr[self::P_HOLDER_STMT] = $tokens[1][$pos];
                $ptr[self::P_HOLDER_NAME] = $tokens[2][$pos];
                $ptr[self::P_HOLDER_BIND] = array();
            }
        }
        unset($bind);
        // クエリー/バインド変数の設定
        $this->setupBindValue($pholder, $params);
        $this->setupQuery($pholder, $params);
    }
    // }}}
    // {{{ setupQuery
    /**
     *  クエリ文字列処理
     *
     *  クエリー文字列を内部変数に格納する。<br />
     *  同時に最適化処理も行う
     *   - スペース、TAB、改行などを除去
     *   - 特殊文字列の処理
     *   - 予約語の置換
     *
     *  @param  array  バインド変数情報
     *  @param  array  クエリ変数値
     */
    protected /* void */
        function setupQuery(/* array */ $pholder,
                            /* array */ $params)
    {
        // クエリの設定
        $query = $pholder[self::P_HOLDER_STMT];
        foreach ($pholder[self::P_HOLDER_BIND] as $bind) {
            $b_stmt  = $bind[self::P_HOLDER_STMT];
            $b_name  = $bind[self::P_HOLDER_NAME];
            $b_value = $params[$b_name];
            // 疑問符プレスフォルダに置き換え
            if (preg_match('/^:[a-zA-Z0-9_]+<(.+)>$/', $b_stmt, $maches)) {
                // 行値構成子
                $token = '('.$maches[1].')';
                foreach ($bind[self::P_HOLDER_BIND] as $m_bind) {
                    $m_stmt  = $m_bind[self::P_HOLDER_STMT];
                    $m_name  = $m_bind[self::P_HOLDER_NAME];
                    $_token = (is_array($b_value[0][$m_name]))
                        ? '${1}?' . str_repeat(', ?', count($b_value[0][$m_name]) - 1)
                        : '${1}?';
                    $pattern = sprintf('/(^|[^\x5c])%s/', preg_quote($m_stmt));
                    $token   = preg_replace($pattern, $_token, $token, 1);
                }
                $token  .= str_repeat(', ' . $token, count($b_value) - 1);
                $fields  = '${1}' . $token;
            } else {
                $fields = '${1}?';
                if (is_array($b_value)) {
                    $fields .= str_repeat(', ?', count($b_value) - 1);
                }
            }
            $pattern = sprintf('/(^|[^\x5c])%s/', preg_quote($b_stmt));
            $query   = preg_replace($pattern, $fields, $query, 1);
        }
        // クエリ条件設定 (最大取得件数)
        if ($this->criteria->limit !== NULL) {
            $query .= ' LIMIT ?';
        }
        // クエリ条件設定 (オフセット)
        if ($this->criteria->offset !== NULL) {
            $query .= ' OFFSET ?';
        }
        // 予約文字を置き換える
        if (strpos($query, '__TABLE_NAME__') !== false) {
            $tbl_name = $this->data_format->getTableName($this->criteria);
            $query = str_replace('__TABLE_NAME__', $tbl_name, $query);
        }
        $query = preg_replace('/\x5c:/', ':', $query);
        // クエリ登録
        $this->query = $query;
    }
    // }}}
    // {{{ setupBindValue
    /**
     *  バインド変数処理
     *
     *  @param  array  バインド変数情報
     *  @param  array  クエリ変数値
     */
    protected /* void */
        function setupBindValue(/* array */ $pholder,
                                /* array */ $params)
    {
        // バインド値の設定
        foreach ($pholder[self::P_HOLDER_BIND] as $bind) {
            $b_stmt  = $bind[self::P_HOLDER_STMT];
            $b_name  = $bind[self::P_HOLDER_NAME];
            if (array_key_exists($b_name, $params) === FALSE) {
                $ex_msg  = 'Bind value was not specified {df, key} %s %s';
                $ex_msg  = sprintf($ex_msg, get_class($this->data_format), $b_name);
                throw new Cascade_Exception_DBException($ex_msg);
            }
            $b_value = is_array($params[$b_name])
                ? $params[$b_name]
                : array($params[$b_name]);
            if (preg_match('/^:[a-zA-Z0-9_]+<(.+)>$/', $b_stmt, $maches)) {
                // 行値構成子
                foreach ($b_value as $_value) {
                    foreach ($bind[self::P_HOLDER_BIND] as $m_bind) {
                        $m_name  = $m_bind[self::P_HOLDER_NAME];
                        if (array_key_exists($m_name, $_value) === FALSE) {
                            $ex_msg  = 'Bind value was not specified {df, key} %s %s';
                            $ex_msg  = sprintf($ex_msg, get_class($this->data_format), $m_name);
                            throw new Cascade_Exception_DBException($ex_msg);
                        }
                        $m_value = is_array($_value[$m_name])
                            ? $_value[$m_name]
                            : array($_value[$m_name]);
                        foreach ($m_value as $__value) {
                            if ($__value === null) {
                                $this->addBindValue($__value, self::VAR_TYPE_IS_NULL);
                            } else {
                                $this->addBindValue($__value);
                            }
                        }
                    }
                }
            } else {
                foreach ($b_value as $_value) {
                    if ($_value === null) {
                        // nullはそのまま
                        $this->addBindValue($_value, self::VAR_TYPE_IS_NULL);
                    } else {
                        // null以外は文字列に変換
                        $this->addBindValue($_value);
                    }
                }
            }
        }
        // クエリ条件設定 (最大取得件数)
        if ($this->criteria->limit !== NULL) {
            $this->addBindValue($this->criteria->limit, self::VAR_TYPE_IS_LONG);
        }
        // クエリ条件設定 (オフセット)
        if ($this->criteria->offset !== NULL) {
            $this->addBindValue($this->criteria->offset, self::VAR_TYPE_IS_LONG);
        }
    }
    // }}}
    // {{{ addBindValue
    /**
     *  バインド変数値を内部変数に追加する
     *
     *  クエリー文字列に定義されている疑問符プレースホルダに、<br/>
     *  先頭のポジションからバインドする値を設定していく。<br/>
     *  変換可能な型
     *   - {@link VAR_TYPE_IS_NULL}
     *   - {@link VAR_TYPE_IS_BOOL}
     *   - {@link VAR_TYPE_IS_LONG}
     *   - {@link VAR_TYPE_IS_DOUBLE}
     *   - {@link VAR_TYPE_IS_STRING}
     *
     *  @param  mixed  バインド変数値
     *  @param  int    (optional) 指定の変数型に変換する場合に指定
     */
    protected /* void */
        function addBindValue(/* mixed */ $value,
                              /* int   */ $type = self::VAR_TYPE_IS_STRING)
    {
        // Z_TYPEの取得
        $orig      = $value;
        $orig_type = gettype($value);
        // 指定の型に変換
        switch ($type) {
        case self::VAR_TYPE_IS_NULL:
        case self::VAR_TYPE_IS_BOOL:
        case self::VAR_TYPE_IS_LONG:
        case self::VAR_TYPE_IS_DOUBLE:
            settype($value, $type);
            break;
        case self::VAR_TYPE_IS_STRING:
        {
            switch($orig_type) {
            case self::VAR_TYPE_IS_NULL:
                $value = 'NULL';
                break;
            case self::VAR_TYPE_IS_BOOL:
                $value = $value ? '1' : '0';
                break;
            case self::VAR_TYPE_IS_LONG:
            case self::VAR_TYPE_IS_DOUBLE:
            case self::VAR_TYPE_IS_STRING:
                settype($value, $type);
                break;
            default:
                $str_value = Cascade_System_Util::export($value, TRUE);
                $ex_msg = 'Invalid Z_TYPE of bind-value {z_type, value} %s %s';
                $ex_msg = sprintf($ex_msg, gettype($value), $str_value);
                throw new Cascade_Exception_DBException($ex_msg);
            }
            break;
        }
        default:
            $str_value = Cascade_System_Util::export($value, TRUE);
            $ex_msg = 'Unexpected the type of bind-value {type, value} %d %d';
            $ex_msg = sprintf($ex_msg, $type, $str_value);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // 内部変数に保存
        $this->bind_values[]        = $value;
        $this->bind_value_details[] = array(
            self::BIND_TYPE      => $type,
            self::BIND_VAL       => $value,
            self::BIND_ORIG_TYPE => $orig_type,
            self::BIND_ORIG_VAL  => $orig,
        );
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getDriver
    /**
     *  ドライバを取得する
     *
     *  データ・フォーマットの情報に該当するドライバ・インスタンスを取得する。<br/>
     *  インスタンスを取得した時点でコネクションが確立される。
     *
     *  @return  Cascade_DB_SQL_Driver  DB操作ドライバ
     */
    protected /* Cascade_DB_SQL_Driver */
        function getDriver(/* void */)
    {
        // ドライバを取得する
        $type = $this->getDataFormat()->getDriverType();
        $args = array(
            $this->getDataFormat()->getActiveDSN($this->getCriteria())
        );
        $driver = Cascade_Driver_Factory::getInstance($type, $args);
        return $driver;
    }
    // }}}
    // {{{ getResultIndex
    /**
     *  結果データのインデックス文字列を生成する
     *
     *  指定キーを基準に、結果データのインデックス文字列を取得する。
     *
     *  @param   string|array  基準とするキー情報
     *  @param   array         結果データ行
     *  @return  string        結果データのINDEX
     */
    protected /* string */
        function getResultIndex(/* string|array */ $keys,
                                /* array */        $record)
    {
        $idx_token = NULL;
        $driver    = $this->getDriver();
        $type      = $driver->getConstant('FIELD_DEF_IS_NAME');
        if (is_array($keys) === FALSE) {
            $field     = $driver->fetch_field($keys, $type);
            $idx_token = isset($record[$field]) ? $record[$field] : NULL;
        } else {
            foreach ($keys as $_key) {
                $field = $driver->fetch_field($_key, $type);
                if (isset($record[$field])) {
                    $idx_token = ($idx_token === NULL)
                        ? sprintf(   '%s',             $record[$field])
                        : sprintf('%s_%s', $idx_token, $record[$field]);
                    continue;
                }
                $idx_token = NULL;
                break;
            }
        }
        return $idx_token;
    }
    // }}}
};
