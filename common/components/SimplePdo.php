<?php

namespace common\components;

use yii\base\Object;
use PDO;

/**
 * Simple action for PDO
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-08-26 16:08:08
 */
class SimplePdo extends Object
{

    /**
     * @var object instance of Log
     */
    public $logger = null;

    /**
     * @license optional change
     * @var string Log template
     */
    public $logTpl = 'SQL: [%s] [%s] %s';

    /**
     * @var object The instance of PDO
     */
    protected $_pdo;

    /**
     * @var object The bind data
     */
    protected $_bind;

    /**
     * @var object The instance of PDOStatement
     */
    protected $_pdoStatement;

    /**
     * @var string The sql for query
     */
    protected $_sql;

    /**
     * @var array All of the instance of self
     */
    protected static $_instance;

    /**
     * @var array Configs
     */
    private $_config = [
        'dsn' => 'mysql:dbname=mysql;host=127.0.0.1;charset:utf8',
        'username' => 'root',
        'password' => '123456',
        'timeout' => 1
    ];

    /**
     * @var array Style for query result
     */
    protected $_style = [
        // 返回一个索引为结果集列名和以0开始的列号的数组 [默认]
        0 => PDO::FETCH_BOTH,
        // 返回一个索引为结果集列名的数组
        1 => PDO::FETCH_ASSOC,
        // 想要返回一个包含结果集中单独一列所有值的数组 - 查询单一字段时
        2 => PDO::FETCH_COLUMN,
        // 返回TRUE ，并分配结果集中的列值给 PDOStatement::bindColumn() 方法绑定的PHP变量。
        3 => PDO::FETCH_BOUND,
        // 如果 fetch_style 包含 PDO::FETCH_CLASSTYPE（例如：PDO::FETCH_CLASS | PDO::FETCH_CLASSTYPE），则类名由第一列的值决定
        4 => PDO::FETCH_CLASS,
        // 更新一个被请求类已存在的实例，映射结果集中的列到类中命名的属性
        5 => PDO::FETCH_INTO,
        // 结合使用 PDO::FETCH_BOTH 和 PDO::FETCH_OBJ，创建供用来访问的对象变量名
        6 => PDO::FETCH_LAZY,
        // 返回一个索引为以0开始的结果集列号的数组
        7 => PDO::FETCH_NUM,
        // 返回一个属性名对应结果集列名的匿名对象
        8 => PDO::FETCH_OBJ
    ];

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct();

        $this->_config = array_merge($this->_config, $config);
        $this->_connect($this->_config);
    }

    /**
     * Singleton
     *
     * @access public
     *
     * @param array $config
     *
     * @return object
     */
    static public function singleton($config)
    {
        $key = md5(json_encode($config));
        if (empty(self::$_instance) || empty(self::$_instance[$key])) {
            self::$_instance[$key] = new self($config);
        }

        return self::$_instance[$key];
    }

    /**
     * Connect
     *
     * @access private
     *
     * @param array $config
     *
     * @return void
     */
    private function _connect($config)
    {
        $this->_pdo = new PDO($config['dsn'], $config['username'], $config['password'], [
            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_TIMEOUT => $config['timeout'],
        ]);

        if (0 === strpos($config['dsn'], 'mysql')) {
            $this->_pdo->query('SET NAMES utf8');
        }
    }

    /**
     * Disconnect
     *
     * @access public
     * @return void
     */
    public function disConnect()
    {
        $this->_pdo = null;
        $this->_pdoStatement = null;
    }

    /**
     * Exec prepare sql
     *
     * @access public
     * @return bool
     */
    public function queryWithBind()
    {
        Helper::cost('begin');
        $result = $this->_pdoStatement->execute();
        Helper::cost('end');

        if ($result) {
            $sql = $this->_sql;
            foreach ($this->_bind as $value) {
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            $message = sprintf($this->logTpl, Helper::cost('begin', 'end'), Helper::cost('begin', 'end', 'm'), $sql);
            $this->logger && $this->logger->sql($message);

            return true;
        }

        return $this->_pdo->errorInfo();
    }

    /**
     * Query sql
     *
     * @access public
     *
     * @param string $statement
     *
     * @return mixed
     */
    public function queryWithNonBind($statement)
    {
        Helper::cost('begin');
        $result = $this->_pdo->query($statement);
        Helper::cost('end');

        if ($result) {

            $message = sprintf($this->logTpl, Helper::cost('begin', 'end'), Helper::cost('begin', 'end', 'm'), $statement);
            $this->logger && $this->logger->sql($message);

            $this->_pdoStatement = $result;
            $this->_sql = $statement;

            return $this;
        }

        $msg = $this->_pdo->errorInfo();

        return empty($msg[2]) ? false : $msg[2];
    }

    /**
     * Execute sql
     *
     * @access private
     *
     * @param $sql
     * @param $bind
     *
     * @return object
     */
    private function _execute($sql, $bind = null)
    {
        if (empty($bind)) {
            $this->queryWithNonBind($sql);
        } else {
            $this->prepare($sql)->bindArray($bind)->queryWithBind();
        }

        return $this;
    }

    /**
     * Select one
     *
     * @access public
     *
     * @param string  $sql
     * @param array   $bind
     * @param integer $type The result type index of $this->_style
     *
     * @return array
     */
    public function fetchOne($sql, $bind = null, $type = 1)
    {
        $this->_execute($sql, $bind);

        return $this->_pdoStatement->fetch($this->_style[$type]);
    }

    /**
     * Select all
     *
     * @access public
     *
     * @param string  $sql
     * @param array   $bind
     * @param integer $type The result type index of $this->_style
     *
     * @return array
     */
    public function fetchAll($sql, $bind = null, $type = 1)
    {
        $this->_execute($sql, $bind);

        return $this->_pdoStatement->fetchAll($this->_style[$type]);
    }

    /**
     * Insert
     *
     * @access public
     *
     * @param $sql
     * @param $bind
     *
     * @return integer
     */
    public function insert($sql, $bind = null)
    {
        $this->_execute($sql, $bind);

        return $this->_pdo->lastInsertId();
    }

    /**
     * Update
     *
     * @access public
     *
     * @param string $sql
     * @param array  $bind
     *
     * @return integer
     */
    public function update($sql, $bind = null)
    {
        $this->_execute($sql, $bind);

        return $this->_pdoStatement->rowCount();
    }

    /**
     * Prepare sql
     *
     * @access public
     *
     * @param string $statement
     *
     * @return object
     */
    public function prepare($statement)
    {
        $result = $this->_pdo->prepare($statement);
        if ($result) {
            $this->_pdoStatement = $result;
            $this->_sql = $statement;

            return $this;
        }

        return $this->_pdo->errorInfo();
    }

    /**
     * Bind data
     *
     * @access public
     *
     * @param array $array
     *
     * @return object
     */
    public function bindArray($array)
    {
        $this->_bind = $array;
        foreach ($array as $k => $v) {
            $this->_pdoStatement->bindValue($k + 1, $v, PDO::PARAM_STR);
        }

        return $this;
    }

    /**
     * Begin tran
     *
     * @access public
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * Run tran
     *
     * @access public
     * @return bool
     */
    public function commitTransaction()
    {
        return $this->_pdo->commit();
    }

    /**
     * Rollback tran
     *
     * @access public
     * @return bool
     */
    public function rollbackTransaction()
    {
        return $this->_pdo->rollBack();
    }

    /**
     * Get support driver of PDO
     *
     * @access public
     * @return array
     */
    public static function getSupportDriver()
    {
        return PDO::getAvailableDrivers();
    }

    /**
     * Get version of the driver
     *
     * @access public
     * @return array
     */
    public function getDriverVersion()
    {
        $name = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return [
            $name => $this->_pdo->getAttribute(PDO::ATTR_CLIENT_VERSION)
        ];
    }
}