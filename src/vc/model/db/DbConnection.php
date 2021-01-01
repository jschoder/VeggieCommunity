<?php
namespace vc\model\db;

class DbConnection
{
    private $connection;

    public function __construct($server)
    {
        if (!array_key_exists($server, \vc\config\Globals::$db)) {
            throw new \vc\exception\FatalSystemException('Unknown db-config: ' . $server);
        }
        $db = \vc\config\Globals::$db[$server]['db'];
        $host = \vc\config\Globals::$db[$server]['host'];
        $user = \vc\config\Globals::$db[$server]['user'];
        $password = \vc\config\Globals::$db[$server]['password'];

        $this->connection = new \mysqli($host, $user, $password, $db);

        if ($this->connection->connect_errno) {
            throw new \vc\exception\DBConnectionFailedException();
        }
        $this->connection->query("SET NAMES 'utf8'");
    }

    /**
     * @deprecated since version 3.17.0
     */
    public function execute($query)
    {
        $this->connection->query($query);
        $this->checkMySQLError($query);
    }

    /**
     * @deprecated since version 3.17.0
     */
    public function select($query)
    {
        $start = microtime(true);
        $result = $this->connection->query($query);
        $this->checkMySQLError($query);
        if (\vc\config\Globals::QUERY_DEBUG) {
            $end = microtime(true);
            $time = $end - $start;
            if ($time > 0) {
                \vc\lib\QueryLog::log($time, $query, $params);
            }
        }
        return $result;
    }

    /**
     * @deprecated since version 3.17.0
     */
    public function update($query)
    {
        $start = microtime(true);
        $this->connection->query($query);
        $this->checkMySQLError($query);
        if (\vc\config\Globals::QUERY_DEBUG) {
            $end = microtime(true);
            $time = $end - $start;
            if ($time > 0) {
                \vc\lib\QueryLog::log($time, $query, $params);
            }
        }
        return $this->connection->affected_rows;
    }

    /**
     * @deprecated since version 3.17.0
     */
    public function delete($query)
    {
        $start = microtime(true);
        $this->connection->query($query);
        $this->checkMySQLError($query);
        if (\vc\config\Globals::QUERY_DEBUG) {
            $end = microtime(true);
            $time = $end - $start;
            if ($time > 0) {
                \vc\lib\QueryLog::log($time, $query, $params);
            }
        }
        return $this->connection->affected_rows;
    }

    public function hasError()
    {
        return ($this->connection->errno > 0 ? true : false);
    }

    public function getErrorCode()
    {
        return $this->connection->errno;
    }

    public function getErrorMessage()
    {
        return $this->connection->error;
    }

    /**
     * @deprecated since version 3.17.0
     */
    public function prepareSQL($text)
    {
        while (stripos($text, "\\\\") !== false) {
            $text = str_replace("\\\\", "\\", $text);
        }
        // Stripping out all other characters (Not using Control since it also strips german ß)
        $text = preg_replace(array('@\p{Cf}@', '@\p{Co}@','@\p{Cn}@'), '', $text);
        return $this->connection->real_escape_string($text);
    }

    /**
     * http://php.net/manual/en/mysqli-stmt.bind-param.php
     * @param string $query
     * @return
     */
    public function prepare($query)
    {
        $statement = $this->connection->prepare($query);
        $this->checkMySQLError($query);
        return $statement;
    }

    /**
     *
     * @param type $query
     * @param type $params
     * @return \mysqli_stmt
     */
    public function queryPrepared($query, $params = null)
    {
        $start = microtime(true);
        $statement = $this->prepare($query);
        if (!$statement) {
            \vc\lib\ErrorHandler::error(
                'Error while creating prepared statement: ' . $query,
                __FILE__,
                __LINE__,
                array()
            );
            return null;
        }

        if (!empty($params)) {
            $fieldTypes = '';
            $fieldValues = array();
            foreach ($params as $index => $param) {
                if (is_int($param)) {
                    $fieldTypes .= 'i';
                } elseif (is_float($param)) {
                    $fieldTypes .= 'd';
                } else {
                    $fieldTypes .= 's';
                }
                $bindFieldName = 'bind' . $index;
                $$bindFieldName = $param;
                $fieldValues[$bindFieldName] =  &$$bindFieldName;
            }

            $paramsBinded = call_user_func_array(
                array($statement,'bind_param'),
                array_merge(array('bindingTypes' => $fieldTypes), $fieldValues)
            );
            if (!$paramsBinded) {
                \vc\lib\ErrorHandler::error(
                    'Error while binding parameters.',
                    __FILE__,
                    __LINE__,
                    array(
                        'query' => $query,
                        'fieldTypes' => $fieldTypes,
                        'fieldValues' => $fieldValues,
                        'errno' => $statement->errno,
                        'error' => $statement->error
                    )
                );
                $statement->close();
                return null;
            }
        }
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while executing statement.',
                __FILE__,
                __LINE__,
                array(
                    'query' => $query,
                    'params' => $params,
                    'errno' => $statement->errno,
                    'error' => $statement->error
                )
            );
            $statement->close();
            return null;
        }

        if (\vc\config\Globals::QUERY_DEBUG) {
            $end = microtime(true);
            $time = $end - $start;
            if ($time) {
                \vc\lib\QueryLog::log($time, $query, $params);
            }
        }

        return $statement;
    }

    public function executePrepared($query, $params = null)
    {
        $statement = $this->queryPrepared($query, $params);
        if ($statement === null) {
            return false;
        } else {
            $statement->close();
            return true;
        }
    }

    private function checkMySQLError($query)
    {
        if ($this->hasError()) {
            \vc\lib\ErrorHandler::getInstance()->saveReport(
                $this->getErrorCode(),
                $this->getErrorMessage(),
                $_SERVER['PHP_SELF'],
                0,
                $query
            );
        }
    }

    public function getInsertId()
    {
        return $this->connection->insert_id;
    }

    public function getAffectedRows()
    {
        return $this->connection->affected_rows;
    }
}
