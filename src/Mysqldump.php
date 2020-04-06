<?php

namespace xiaodi\Mysqldump;

class Mysqldump
{

    private $username;
    private $password;
    private $hostname = '127.0.0.1';
    private $hostport = 3306;
    private $database;

    protected $inlucdeTables = [];

    protected $conn;

    protected $dom;

    protected $dropTableIfExists = true;

    protected $includeTableStructure = true;

    protected $includeTableContent = true;


    /**
     * 链接数据库
     * 
     * @return void
     */
    protected function connect()
    {
        $this->conn = new \mysqli($this->hostname, $this->username, $this->password, $this->database, $this->hostport);
    }

    /**
     * 数据库用户名
     * 
     * @param string $username
     * @return self
     */
    public function username(string $username):self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * 数据库密码
     * 
     * @param string $password
     * @return self
     */
    public function password(string $password):self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * 导出整个数据库
     *
     * @param string $name
     * @return self
     */
    public function database(string $name): self
    {
        $this->database = $name;

        return $this;
    }

    /**
     * 导出数据表
     *
     * @param string|array $name
     * @return $this
     */
    public function table($name): self
    {
        if (is_array($name)) {
            $this->inlucdeTables = $name;
        } else {
            $this->inlucdeTables[] = $name;
        }

        return $this;
    }

    /**
     * 是否导出数据
     *
     * @param boolean $flag
     * @return void
     */
    public function inlucdeTableContent($flag = true)
    {
        $this->includeTableContent = $flag;

        return $this;
    }

    /**
     * 是否如果存在则删除表
     *
     * @param boolean $flag
     * @return void
     */
    public function dropTableIfExists($flag = true)
    {
        $this->dropTableIfExists = $flag;

        return $this;
    }

    /**
     * 是否导出表结构
     *
     * @param boolean $flag
     * @return void
     */
    public function includeTableStructure($flag = true)
    {
        $this->includeTableStructure = $flag;

        return $this;
    }

    /**
     * 导出 如果存在则删除表
     *
     * @param string $table
     * @return void
     */
    protected function hangleDropTableIfExists(string $table)
    {
        if ($this->dropTableIfExists) {
            $this->dom .= "DROP TABLE IF EXISTS $table;\n";
        }
    }

    /**
     * 导出表结构
     *
     * @param string $table
     * @return void
     */
    protected function handleIncludeTableStructure(string $table)
    {
        if ($this->includeTableStructure) {
            $result = $this->conn->query("SHOW CREATE TABLE {$table}");
            [$tableName, $sql] = mysqli_fetch_row($result);

            $this->dom .= str_replace("\t", '', $sql) . "\n\n";
        }
    }

    /**
     * 导出表内容
     *
     * @param string $table
     * @return void
     */
    protected function handleInlucdeTableContent(string $table)
    {
        if ($this->includeTableContent) {
            $this->dom .= "INSERT INTO `{$table}` (";

            $r = $this->conn->query("SELECT column_name as column_name FROM information_schema.columns WHERE table_name='{$table}';");

            $fields = [];
            while ($row = mysqli_fetch_row($r)) {
                $fields[] = "`{$row[0]}`";
            }

            $this->dom .= implode(', ', $fields) . ") VALUES \n";

            $rows = $this->conn->query("SELECT * FROM {$table}");

            $inserts = [];
            while ($row = mysqli_fetch_row($rows)) {
                $values = [];
                foreach ($row as $item) {
                    $values[] = "'" . mysqli_real_escape_string($this->conn, $item) . "'";
                }
                $inserts[] = '(' . implode(', ', $values) . ')';
            }

            $this->dom .= implode(",\n", $inserts);
        }
    }

    /**
     * 输出头部信息
     *
     * @return void
     */
    protected function handleHeader()
    {
        $date = date('Y-m-d H:i:s');
        $this->dom = <<<EOF
-- -------------------------------------------------------------
-- Mysqldump EdenLeung
--
-- https://xiaodim.com/
--
-- Database: $this->database
-- Generation Time: $date
-- -------------------------------------------------------------



EOF;
    }

    /**
     * 保存文件
     *
     * @param string $fileName
     * @return void
     */
    protected function saveFile(string $fileName)
    {
        $fp = @fopen($fileName . '.sql', 'w+');
        @fwrite($fp, $this->dom);
        @fclose($fp);
    }

    /**
     * 开始执行导出表
     *
     * @param string $table
     * @return void
     */
    protected function dump(string $table)
    {
        $this->hangleDropTableIfExists($table);
        $this->handleIncludeTableStructure($table);
        $this->handleInlucdeTableContent($table);
    }

    /**
     * 获取当前数据库下所有数据表
     *
     * @return void
     */
    protected function getTable()
    {
        $tables = [];
        $result = $this->conn->query('SHOW TABLES');
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * 开始执行导出
     *
     * @return void
     */
    public function start()
    {
        $this->connect();

        $this->handleHeader();

        if (!empty($this->inlucdeTables)) {
            $tables = $this->inlucdeTables;
            $fileName = $this->database . '-tables';
        } else {
            $tables = $this->getTable();
            $fileName = $this->database;
        }

        foreach ($tables as $table) {
            $this->dump($table);
            $this->dom .= "\n\n";
        }

        $this->saveFile($fileName);
    }
}
