<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set("America/Asuncion");
class DataBase extends MySQLi{

    private $link;
    private $result;
    private $sql;
    private $lastError;
    private $resultSize;
    private static $connection;
    private static $sqlQueries;
    private static $totalQueries;
    private $lastErrorCode;
    private $logger = false;
    private $log_dir = __DIR__ . '/log';
    private $log_file_name = "log_querys_bd";
    private $log_file_ext = "txt";
    const L_ERROR   = "ERROR";
    const L_INFO    = "INFO";

    // Local
    const DB_NAME = 'rrhh_db';
    const DB_USER = 'root';
    const DB_PSW = '';
    const DB_HOST = 'localhost';
    const DB_PORT = '3306';
 
    public function logger($status = true, $comentario = ""){
        $this->logger = $status;
        $this->comentario = $comentario;
    }
    public function set_log_dir($dir){
        $this->log_dir = $dir;
    }
    public function set_log_file_name($name){
        $this->log_file_name = $name;
    }

    public function set_log_comentario($comentario = ""){
        $this->comentario = $comentario;
    }

    public function log_sql($level){
        if($this->logger){
            if(!file_exists($this->log_dir)){
                mkdir($this->log_dir);
            }
            $message = 'Log ['. $level ."]\r\n";
            $message .= 'Comentario: ' . $this->comentario."\r\n";
            $message .= 'Fecha: ' . date('Y-m-d H:i:s')."\r\n";
            $message .= 'Query: '. $this->sql ."\r\n";
            $message .= 'Affected Rows: '. $this->affected_rows ."\r\n";
            $message .= "---------------------------------------------------"."\r\n";
            
            file_put_contents($this->log_dir . "/" . $this->log_file_name . "." . $this->log_file_ext, $message, FILE_APPEND);
        }
    }

    public function log_db_errors($code, $error, $query)
    {

        $message = date('Y-m-d H:i:s')."\r\n";
        $message .= 'Query: '. htmlentities($query)."\r\n";
        $message .= 'Error Code: ' . $code."\r\n";
        $message .= 'Error: ' . $error."\r\n";
        $message .= "---------------------------------------------------"."\r\n";
        
        file_put_contents("errores_bd.txt", $message, FILE_APPEND);
    }
    
    public static function conectar(){
        if (is_null(self::$connection)) {
            self::$connection = new DataBase();
            self::$connection->set_charset("utf8");
        }
        return self::$connection;
    }

    private function __construct(){
        $this->link = parent::__construct(self::DB_HOST, self::DB_USER, self::DB_PSW, self::DB_NAME, self::DB_PORT);
        if($this->connect_errno == 0){
            self::$totalQueries = 0;
            self::$sqlQueries = array();
        }
        else {
            $this->log_db_errors($this->connect_errno, $this->connect_error, "Error en la conexion");
            echo 'Error en la conexion: ' . $this->connect_error;
        }
    }
    
    // Escape the string get ready to insert or update
    public function clearText($sql) {
        $sql = trim($sql);
        return mysqli::real_escape_string($sql);
    }
    
    private function execute(){
        $result = $this->result = $this->query($this->sql);
        $log_level = self::L_INFO;
        if(!$result){
            $this->lastError = $this->error;
            $this->lastErrorCode = $this->errno;
            $this->log_db_errors($this->lastErrorCode, $this->lastError, $this->sql);
            $log_level = self::L_ERROR;
        }
        self::$sqlQueries[] = $this->sql;
        self::$totalQueries++;
        $this->resultSize = $this->result->num_rows;
        $this->log_sql($log_level);
        return $result;
    }

    public function alter(){
        $result = $this->result = $this->query($this->sql);
        $log_level = self::L_INFO;
        if(!$result){
            $this->lastError = $this->error;
            $this->lastErrorCode = $this->errno;
            $this->log_db_errors($this->lastErrorCode, $this->lastError, $this->sql);
            $log_level = self::L_ERROR;
        }
        self::$sqlQueries[] = $this->sql;
        self::$totalQueries++;
        $this->resultSize = $this->result->num_rows;
        $this->log_sql($log_level);
        return $result;
    }

    public function loadObjectList(){
        if (!$this->execute()){
            return null;
        }
        $resultSet = array();
        while ($objectRow = $this->result->fetch_object()){
            $resultSet[] = $objectRow;
        }
      //  $this->result->close();
        return $resultSet;  
    }
    
    public function loadObject(){
        if ($this->execute()){
            if ($object = $this->result->fetch_object()){
              //  $this->result->close();
                return $object;
            }
            else return null;
        }
        else return false;
    }
    
   public function setQuery($sql){
        if(empty($sql)){
            return false;
        }
        $this->sql = $sql;
        
        if(!preg_match("/(^\s+select)|(^select)/i", $sql)) {
			$usuario = $_SESSION['usuario'];
			$sql_clean = $this->clearText(trim(preg_replace('/\s+/', ' ', $sql)));
			//$message = date('Y-m-d H:i:s')."|".$sql_clean."\r\n";
			//file_put_contents("querys.txt", $message, FILE_APPEND);
			$mysqli = DataBase::conectar();
			$mysqli->query("INSERT INTO auditoria(fecha, query, usuario) VALUES (NOW(),'$sql_clean','$usuario')");
		}
		return true;
    }

    public function getTotalQueries(){
        return self::$totalQueries;
    }
    
    public function getSQLQueries(){
        return self::$sqlQueries;
    }
    
    public function getError(){
        return $this->lastError;
    }
    
    public function getErrorCode(){
        return $this->lastErrorCode;
    }

    public function getAffectedRows(){
        return $this->resultSize;
    }

    public function error() {
        return ($this->affected_rows === -1);
    }
    
    public function getLastID(){
        return $this->insert_id;
    }
    
    function __destruct(){
       $this->close();
    }
}

?>
