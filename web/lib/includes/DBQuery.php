<?php
class SQLException extends Exception {}

class DBQuery {
	public  static $supportTransaction = false; // 是否支持事务
	private static $instances = array();  // 已创建的连接集合
	private static $defaultDsn = __DEFAULT_DSN; // 默认连接
	private $dsn = null;
	private $logNum = 0;
	private $scheme = "";
	private $dblink = false;
	private static $registerClose = true;

	/* 构造函数 */
	private function __construct($dsn) {
		$this->connect($dsn);
	}

	/* 数据库连接实例  */
	public static function instance($dsn="") {
		if(empty($dsn)) {
			$dsn = self::$defaultDsn;
		}
		if(isset(self::$instances[$dsn])) {
			$instance = self::$instances[$dsn];
			if(!empty($instance) && is_object($instance)) {
				return $instance;
			}
		}
		$instance = new DBQuery($dsn);
		self::$instances[$dsn] = $instance;
		return $instance;
	}
	

	/* 事务开始  */
	public function startTransaction() {
		if(self::$supportTransaction == false) {
			return;
		}
		$this->executeUpdate("START TRANSACTION");
	}

	/* 提交事务 */
	public function commit() {
		if(self::$supportTransaction == false) {
			return;
		}
		$this->executeUpdate("COMMIT");
	}

	/* 回滚事务  */
	public function rollback() {
		if(self::$supportTransaction == false) {
			return;
		}
		$this->executeUpdate("ROLLBACK");
	}

	public function connect($dsn) {
		if(empty($dsn)) {
			throw new Exception("connect dsn is empty.");
		}
		//connect
		$startTime = microtime(true);
		$this->dsn = $dsn;
		$info = parse_url($dsn);
		//other params
		$params = array();
		if(!empty($info['query'])) {
			parse_str($info['query'], $params);
		}
		$this->scheme = strtolower($info['scheme']);
		$host = $info['host'];
		if(!empty($info['port'])) {
			$host .= ":" . $info['port'];
		}
		$this->dblink = $this->func("connect", $host, $info['user'], $info['pass'], true);
		$this->logSQL($this->dblink, "Connect", (microtime(true) - $startTime));
		//select db
		if(strlen($info['path']) > 1) { //定义了DB名称
			$dbname = substr($info['path'], 1); //remove '/'
			$this->selectDB($dbname);
		}
		//set charset
		$charset = 'utf8';
		if(!empty($params['charset'])) {
			$charset = $params['charset'];
		}
		if($this->scheme == "mysql") {
			$this->func("set_charset", $charset);
		}
		//注册PHP运行结束前关闭所有DB连接
		if(self::$registerClose) {
			self::$registerClose = false;
			register_shutdown_function(array('DBQuery', 'closeAll'));
		}
	}
	
	protected function func() {
		$num = func_num_args();
		if($num == 0) {
			throw new Exception("parameter error. must special function name.");
		}
		$p = array(func_get_arg(0));
		$func = $this->scheme . "_" . $p[0];
		$result = false;
		$cmd = "\$result = \$func(";
		for($i=1; $i<$num; $i++) {
			$p[$i] = func_get_arg($i);
			if($i == 1) {
				$cmd .= "\$p[{$i}]";
			} else {
				$cmd .= ",\$p[{$i}]";;
			}
		}
		$cmd .= ");";
		eval($cmd);
		return $result;
	}
	
	public function selectDB($dbname) {
		$startTime = microtime(true);
		$ret = $this->func("select_db", $dbname, $this->dblink);
		$this->logSQL($ret, "select_db({$dbname})", (microtime(true) - $startTime));
	}

	public function getInsertID() {
		$sql = "SELECT LAST_INSERT_ID()";
		return $this->getOne($sql);
	}

	public function executeUpdate($sql){
		$startTime = microtime(true);
		$result = $this->func("query", $sql, $this->dblink);
		$this->logSQL($result, $sql, (microtime(true) - $startTime));
		return $result;
	}

	public function getOne($sql){
		$row = $this->getRow($sql);
		if($row == null) {
			return null;
		}
		return current($row);
	}

	public function getRow($sql){
		$startTime = microtime(true);

		$result = $this->func("query", $sql, $this->dblink);
		if($result) {
			$row = $this->func("fetch_assoc", $result);
			if($row == false) {
				$row = null;
			}
		} else {
			$row = null;
		}

		$this->logSQL($result, $sql, (microtime(true) - $startTime));
		return $row;
	}

	public function getAll($sql){
		$startTime = microtime(true);

		$result = $this->func("query", $sql, $this->dblink);
		if($result) {
			$all = array();
			while($row = $this->func("fetch_assoc", $result)) {
				$all[] = $row;
			}
		} else {
			$all = null;
		}

		$this->logSQL($result, $sql, (microtime(true) - $startTime));
		return $all;
	}

	public function close() {
		if($this->dblink) {
			$this->func("close", $this->dblink);
			$this->dblink = false;
			unset(self::$instances[$this->dsn]);
		}
	}
	
	public static function closeAll() {
		foreach(self::$instances as $key => $obj) {
			$obj->close();
		}
	}

	/**
	 * 过虑特殊字符
	 * 类同于addslashes，但支持charset
	 * @param string/array $param - 需过滤的参数
	 * @param string $charset - DB中的字符集编码
	 */
	public static function filter($param, $charset=__CHARSET) {
		if(is_array($param)) {
			foreach ($param as $key => $val) {
				$param[$key] = self::filter($val, $charset);
			}
			return $param;
		} else {
			$charset = strtolower($charset);
			if($charset == 'utf8') $charset = 'utf-8';
			if($charset == 'latin1') $charset = 'iso-8859-1';
			$len = mb_strlen($param, $charset);
			$buff = "";
			for($i=0; $i<$len; $i++) {
				$ch = mb_substr($param, $i, 1, $charset);
				if($ch == "\0") {
					$ch = "\\0";
				} else if($ch == "\\" || $ch == "'" || $ch == "\"") {
					$ch = "\\".$ch;
				} else if($ch[0] == "\0" && strlen($ch) == 2) { //宽字符
					if($ch[1] == "\0") {
						$ch = "\0\\\00";
					} else if($ch[1] == "\\" || $ch[1] == "'" || $ch[1] == "\"") {
						$ch = "\0\\".$ch;
					}
				}
				$buff .= $ch;
			}
			return $buff;
		}
	}

	public static function toUpdateStr($oldRecord, $newRecord) {
		$keys = array_keys($oldRecord);
		$str = "";
		foreach($keys as $key) {
			if(isset($newRecord[$key]) && $oldRecord[$key] != $newRecord[$key]) {
				if($str != "") {
					$str .= ",";
				}
				$str .= "$key='".self::filter($newRecord[$key])."'";
			}
		}
		
		return $str;
	}

	public static function toUpdateRecord($record) {
		$keys = array_keys($record);
		$str = "";
		foreach($keys as $key) {
			if(isset($record[$key])) {
				if($str != "") {
					$str .= ",";
				}
				$str .= "$key='".self::filter($record[$key])."'";
			}
		}
		
		return $str;
	}
	public static function toInsertStr($newRecord) {
		$keys = array_keys($newRecord);
		$fields = "";
		$values = "";
		foreach($keys as $key) {
			if($fields != "") {
				$fields .= ",";
				$values .= ",";
			}
			$fields .= $key;
			$values .= "'".self::filter($newRecord[$key])."'";
		}
		$str = "($fields) VALUES ($values)";
		return $str;
	}

	public function logSQL($result, $sql, $costTime) {
		$costTime = round($costTime, 3);
		$this->logNum++;
		if ($result === false){
			logError("Num: ".$this->logNum.", CostTime: {$costTime}, SQL:\n\t{$sql}");
			throw new SQLException($this->func("error")."\n\t"."SQL=".$sql);
		} else {
			logDebug("Num: ".$this->logNum.", CostTime: {$costTime}, SQL:\n\t{$sql}");
		}
	}

	/**
	 * 分页查询
	 * TODO：目前仅支持MYSQL
	 * @param string $sql - 查询的SQL语句。不含定位符（如MYSQL中的LIMIT字句）
	 * @param int $pageSize - 每页显示的条数（即，将返回的最大条数）
	 * @param int $pageNo - 第N页。从第1页开始（小于1的数，自动转换为1）
	 * @return array(
	 *    'count'=>int //查询的总条数
	 *    'data'=>array //查询的行结果集
	 * )
	 */
    public function selectData($sql, $pageSize=20, $pageNo=1) {
		$pageNo = max($pageNo, 1); //页号从1开始计数
		$offset = ($pageNo-1) * $pageSize;

		$sql = trim($sql);
		if(strtolower(substr($sql, 0, 6)) != 'select') {
			throw new Exception("Not a SELECT SQL: ".$sql);
		}
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".substr($sql, 6);
		if(preg_match('/LIMIT[\s\,0-9]+$/i', $sql) == false) {
			$sql .= " LIMIT {$offset}, {$pageSize}";
		}
		$result = array();
		$result['data'] = $this->getAll($sql);
		$result['count'] = $this->getOne("SELECT FOUND_ROWS()");
		return $result;
	}
}
