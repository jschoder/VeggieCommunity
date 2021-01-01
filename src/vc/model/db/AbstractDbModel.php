<?php
namespace vc\model\db;

abstract class AbstractDbModel extends \vc\model\AbstractModel
{
    private $db = null;

    public function setDb(DbConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @return \vc\model\db\DbConnection
     */
    public function getDb()
    {
        return $this->db ;
    }

    public function prepareSQL($sql)
    {
        return $this->db->prepareSQL($sql);
    }

    public function getUniqueToken($table, $column, $length, $caseSensitive = true)
    {
        do {
            $token = $this->createToken($length, $caseSensitive);
            $query = 'SELECT count(*) FROM ' . $table . ' WHERE  ' . $column . ' = ?';
            $statement = $this->getDb()->queryPrepared($query, array($token)) ;

            $statement->bind_result($count);
            $statement->fetch();
            $statement->close();

            if ($count > 0) {
                $token = null;
            }
        } while ($token === null);
        return $token;
    }

    public function createToken($length, $caseSensitive = true)
    {
        if ($caseSensitive) {
            $chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m',
                           'n', 'o','p','q','r','s','t','u','v','w','x','y','z',
                           'A','B','C','D','E','F','G','H','I','J','K','L','M',
                           'N', 'O','P','Q','R','S','T','U','V','W','X','Y','Z',
                           '1','2','3','4','5','6','7','8','9','0');
        } else {
            $chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m',
                           'n', 'o','p','q','r','s','t','u','v','w','x','y','z',
                           '1','2','3','4','5','6','7','8','9','0');
        }
        $randomMax = count($chars) - 1;
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[rand(0, $randomMax)];
        }
        return $token;
    }

    public function getField($field, $whereKey, $whereValue)
    {
        $query = 'SELECT ' . $field. ' FROM ' . $this::DB_TABLE . ' WHERE ' . $whereKey. ' = ? LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array($whereValue));
        $statement->store_result();
        if ($statement->num_rows == 0) {
            $statement->close();
            return null;
        } else {
            $statement->bind_result($value);
            $statement->fetch();
            $statement->close();
            return $value;
        }
    }

    public function getFieldList($field, $queryParams = array(), $joins = array(), $orderBy = null, $limit = null)
    {
        $fieldList = array();

        $where = array();
        $whereParams = array();
        foreach ($queryParams as $i => $queryParam) {
            if (is_numeric($i)) {
                $where[] = $queryParam;
            } elseif (is_array($queryParam)) {
                $where[] = $i . ' IN (' . $this->fillQuery(count($queryParam)) . ')';
                $whereParams = array_merge($whereParams, $queryParam);
            } else {
                $queryEnd = substr($i, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $where[] = $i . ' ?';
                    $whereParams[] = $queryParam;
                } else {
                    $where[] = $i . ' = ?';
                    $whereParams[] = $queryParam;
                }
            }
        }

        $query = 'SELECT ' . $field . ' FROM ' . $this::DB_TABLE .
                 ' ' . implode(' ', $joins);
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        if ($orderBy !== null) {
            if (is_array($orderBy)) {
                $query .= ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $query .= ' ORDER BY ' . $orderBy;
            }
        }
        if ($limit !== null) {
            $query .= ' LIMIT ' . $limit;
        }
        $statement = $this->getDb()->queryPrepared($query, $whereParams);
        $statement->bind_result($field);
        while ($statement->fetch()) {
            $fieldList[] = $field;
        }
        $statement->close();

        return $fieldList;
    }

    public function getFieldMap($key, $field, $queryParams, $joins = array(), $orderBy = null)
    {
        $fieldMap = array();

        $where = array();
        $whereParams = array();
        foreach ($queryParams as $i => $queryParam) {
            if (is_numeric($i)) {
                $where[] = $queryParam;
            } elseif (is_array($queryParam)) {
                $where[] = $i . ' IN (' . $this->fillQuery(count($queryParam)) . ')';
                $whereParams = array_merge($whereParams, $queryParam);
            } else {
                $queryEnd = substr($i, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $where[] = $i . ' ?';
                    $whereParams[] = $queryParam;
                } else {
                    $where[] = $i . ' = ?';
                    $whereParams[] = $queryParam;
                }
            }
        }

        $query = 'SELECT ' . $key . ',' . $field . ' FROM ' . $this::DB_TABLE .
                 ' ' . implode(' ', $joins);
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        if ($orderBy !== null) {
            if (is_array($orderBy)) {
                $query .= ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $query .= ' ORDER BY ' . $orderBy;
            }
        }
        $statement = $this->getDb()->queryPrepared($query, $whereParams);
        $statement->bind_result($key, $field);
        while ($statement->fetch()) {
            $fieldMap[$key] = $field;
        }
        $statement->close();

        return $fieldMap;
    }

    public function getIdByHashId($hashId)
    {
        $query = 'SELECT id FROM ' . $this::DB_TABLE . ' WHERE hash_id = ? LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array($hashId));
        $statement->store_result();
        if ($statement->num_rows == 0) {
            $statement->close();
            return null;
        } else {
            $statement->bind_result($id);
            $statement->fetch();
            $statement->close();
            return $id;
        }
    }

    public function getHashIdById($id)
    {
        $query = 'SELECT hash_id FROM ' . $this::DB_TABLE . ' WHERE id = ? LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array($id));
        $statement->store_result();
        if ($statement->num_rows == 0) {
            $statement->close();
            return null;
        } else {
            $statement->bind_result($hashId);
            $statement->fetch();
            $statement->close();
            return $hashId;
        }
    }

    public function getCount($queryParams, $joins = array())
    {
        $where = array();
        $whereParams = array();
        foreach ($joins as $i => $join) {
            if (!is_numeric($i)) {
                arrayAppend($whereParams, $join);
            }
        }
        foreach ($queryParams as $i => $queryParam) {
            if (is_numeric($i)) {
                $where[] = $queryParam;
            } elseif (strpos($i, '?') !== false) {
                $where[] = $i;
                if (is_array($queryParam)) {
                    arrayAppend($whereParams, $queryParam);
                } else {
                    $whereParams[] = $queryParam;
                }
            } elseif (is_array($queryParam)) {
                $where[] = $i . ' IN (' . $this->fillQuery(count($queryParam)) . ')';
                $whereParams = array_merge($whereParams, $queryParam);
            } else {
                $queryEnd = substr($i, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $where[] = $i . ' ?';
                    $whereParams[] = $queryParam;
                } else {
                    $where[] = $i . ' = ?';
                    $whereParams[] = $queryParam;
                }
            }
        }

        $query = 'SELECT count(*) FROM ' . $this::DB_TABLE;

        foreach ($joins as $i => $join) {
            if (is_numeric($i)) {
                $query .= ' ' . $join;
            } else {
                $query .= ' ' . $i;
            }
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $this->getDb()->queryPrepared($query, $whereParams);
        $statement->bind_result($count);
        $statement->fetch();
        $statement->close();
        return $count;
    }

    public function loadObject($queryParams, $joins = array(), $orderBy = null)
    {
        $objects = $this->loadObjects($queryParams, $joins, $orderBy, 1);
        if (empty($objects)) {
            return null;
        } else {
            return $objects[0];
        }
    }

    public function loadObjects($queryParams, $joins = array(), $orderBy = null, $limit = null, $objectClass = null)
    {
        $objects = array();

        if ($objectClass === null) {
            $objectClass = $this::OBJECT_CLASS;
        }
        $dbMapping = array();
        $bindFields = array();
        foreach ($objectClass::$fields as $fieldName => $field) {
            if (isset($field['dbmapping'])) {
                if (isset($field['join'])) {
                    $fieldDbMapping = $field['dbmapping'];
                    $joins[] = $field['join'];
                } else {
                    $fieldDbMapping = $this::DB_TABLE . '.' . $field['dbmapping'];
                }
                $dbMapping[$fieldDbMapping] = $fieldName;
                $bindFieldName = 'bind' . $fieldName;
                $bindFields[] = &$$bindFieldName;
            }
        }
        $where = array();
        $whereParams = array();
        foreach ($joins as $i => $join) {
            if (!is_numeric($i)) {
                arrayAppend($whereParams, $join);
            }
        }
        foreach ($queryParams as $i => $queryParam) {
            if (is_numeric($i)) {
                $where[] = $queryParam;
            } elseif (strpos($i, '?') !== false) {
                $where[] = $i;
                if (is_array($queryParam)) {
                    arrayAppend($whereParams, $queryParam);
                } else {
                    $whereParams[] = $queryParam;
                }
            } elseif (is_array($queryParam)) {
                $where[] = $i . ' IN (' . $this->fillQuery(count($queryParam)) . ')';
                $whereParams = array_merge($whereParams, $queryParam);
            } else {
                $queryEnd = substr($i, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $where[] = $i . ' ?';
                    $whereParams[] = $queryParam;
                } else {
                    $where[] = $i . ' = ?';
                    $whereParams[] = $queryParam;
                }
            }
        }

        $query = 'SELECT ' . implode(',', array_keys($dbMapping)) .
                 ' FROM ' . $this::DB_TABLE;
        foreach ($joins as $i => $join) {
            if (is_numeric($i)) {
                $query .= ' ' . $join;
            } else {
                $query .= ' ' . $i;
            }
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy !== null) {
            if (is_array($orderBy)) {
                $query .= ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $query .= ' ORDER BY ' . $orderBy;
            }
        }
        if ($limit !== null) {
            $query .= ' LIMIT ' . $limit;
        }

        $statement = $this->getDb()->queryPrepared($query, $whereParams);
        call_user_func_array(array($statement,'bind_result'), $bindFields);
        while ($statement->fetch()) {
            $object = new $objectClass();
            foreach ($dbMapping as $fieldName => $field) {
                $bindFieldName = 'bind' . $field;
                if ($objectClass::$fields[$field]['type'] === 'boolean') {
                    $object->$field = (boolean) $$bindFieldName;
                } elseif ($objectClass::$fields[$field]['type'] === 'json.array') {
                    $object->$field = json_decode($$bindFieldName, true);
                } elseif ($objectClass::$fields[$field]['type'] === 'json.object') {
                    $object->$field = json_decode($$bindFieldName, false);
                } else {
                    $object->$field = $$bindFieldName;
                }
            }
            $objects[] = $object;
        }
        $statement->close();

        return $objects;
    }

    public function insertObject($currentUser, $object, $validate = true)
    {
        return $this->saveObject($currentUser, $object, $validate, false);
    }

    public function updateObject($currentUser, $object, $validate = true)
    {
        return $this->saveObject($currentUser, $object, $validate, true);
    }

    private function saveObject($currentUser, $object, $validate, $isUpdate)
    {
        $objectClass = $this::OBJECT_CLASS;
        $primaryKey = $objectClass::$primaryKey;

        $queryFields = array();
        $fieldTypes = '';
        $fieldValues = array();
        foreach ($objectClass::$fields as $fieldName => $fieldConfig) {
            if (!empty($fieldConfig['dbmapping']) &&
                (!array_key_exists('autoincrement', $fieldConfig) || $fieldConfig['autoincrement'] !== true) &&
                // Don't update primary key fields
                (!$isUpdate || !in_array($fieldName, $primaryKey)) &&
                // Don't update empty passwords
                (!$isUpdate || $fieldConfig['type'] !== 'password' || !empty($object->$fieldName)) &&
                // Skip fields marked as non-update
                (!$isUpdate || !isset($fieldConfig['update']) || $fieldConfig['update'] === true)) {
                $queryFields[] = $fieldConfig['dbmapping'] . '=?';
                if (empty($object->$fieldName) &&
                    $object->$fieldName !== 0) {
                    if (isset($fieldConfig['default'])) {
                        $default = $fieldConfig['default'];
                        if (strpos($default, '{{') !== false) {
                            $find = array();
                            $replace = array();

                            if (strpos($default, '{{UNIQUE_TOKEN}}') !== false) {
                                $find[] = '{{UNIQUE_TOKEN}}';
                                $replace[] = $this->getUniqueToken(
                                    $this::DB_TABLE,
                                    $fieldConfig['dbmapping'],
                                    $fieldConfig['length'],
                                    false
                                );
                            }

                            $find[] = '{{CURRENT_TIME}}';
                            $replace[] = date('Y-m-d H:i:s');

                            $find[] = '{{CURRENT_USER}}';
                            if ($currentUser === null) {
                                $replace[] = 0;
                            } else {
                                $replace[] = $currentUser->id;
                            }
                            $default = str_replace($find, $replace, $default);
                        }
                        $fieldValue = $default;
                    } else {
                        $fieldValue = null;
                    }
                } else {
                    $fieldValue = $object->$fieldName;
                }

                // Bind
                // i   corresponding variable has type integer
                // d   corresponding variable has type double
                // s   corresponding variable has type string
                // b   corresponding variable is a blob and will be sent in packets
                if ($fieldConfig['type'] == 'integer') {
                    if ($fieldValue !== null) {
                        $fieldValue = intval($fieldValue);
                    }
                    $fieldTypes .= 'i';
                } elseif ($fieldConfig['type'] == 'boolean') {
                    if ($fieldValue !== null) {
                        if ($fieldValue) {
                            $fieldValue = 1;
                        } else {
                            $fieldValue = 0;
                        }
                    }
                    $fieldTypes .= 'i';
                } elseif ($fieldConfig['type'] == 'double') {
                    $fieldTypes .= 'd';
                } elseif ($fieldConfig['type'] == 'text' ||
                          $fieldConfig['type'] == 'password' ||
                          $fieldConfig['type'] == 'date' ||
                          $fieldConfig['type'] == 'datetime') {
                    $fieldTypes .= 's';
                } elseif ($fieldConfig['type'] == 'image') {
                    $fieldTypes .= 's';
                } elseif ($fieldConfig['type'] == 'json.array' ||
                          $fieldConfig['type'] == 'json.object') {
                    if (empty($fieldValue)) {
                        $fieldValue = null;
                    } else {
                        $fieldValue = json_encode($fieldValue);
                    }
                    $fieldTypes .= 's';
                } else {
                    \vc\lib\ErrorHandler::error(
                        'Invalid fieldConfig-Type',
                        __FILE__,
                        __LINE__,
                        array(
                            'fieldName' => $fieldName,
                            'fieldConfig' => $fieldConfig
                        )
                    );
                    $fieldTypes .= 's';
                }

                $bindFieldName = 'bind' . $fieldName;
                $$bindFieldName = $fieldValue;
                $fieldValues[$bindFieldName] =  &$$bindFieldName;
            }
        }
        if ($isUpdate) {
            if (empty($primaryKey)) {
                \vc\lib\ErrorHandler::error(
                    'Trying to update an object without primaryKey',
                    __FILE__,
                    __LINE__,
                    array(
                        'object' => var_export($object, true),
                        'objectClass' => $objectClass
                    )
                );
                return false;
            }
            $whereFields = array();
            foreach ($primaryKey as $key) {
                // Presuming the primaryKey only consists of integers
                $fieldTypes .= 'i';
                $dbMapping = $objectClass::$fields[$key]['dbmapping'];
                $whereFields[] = $dbMapping . '=?';

                $$key = $object->$key;
                $fieldValues[$key] = &$$key;
            }
            $query = 'UPDATE ' . $this::DB_TABLE . ' SET ' .
                     implode(',', $queryFields) .
                     ' WHERE ' . implode(' AND ', $whereFields);
        } else {
            $query = 'INSERT INTO ' . $this::DB_TABLE . ' SET ' . implode(',', $queryFields);
        }

        $statement = $this->getDb()->prepare($query);
        if (!$statement) {
            \vc\lib\ErrorHandler::error(
                'Error creating statement for saving object',
                __FILE__,
                __LINE__,
                array(
                    'object' => var_export($object, true),
                    'query' => $query
                )
            );
        }

        $paramsBinded = call_user_func_array(
            array($statement,'bind_param'),
            array_merge(array('bindingTypes' => $fieldTypes), $fieldValues)
        );
        if (!$paramsBinded) {
            \vc\lib\ErrorHandler::error(
                'Error binding parameters for saving object',
                __FILE__,
                __LINE__,
                array(
                    'object' => var_export($object, true),
                    'query' => $query,
                    'fieldTypes' => var_export($fieldTypes, true),
                    'fieldValues' => var_export($fieldValues, true)
                )
            );
        }

        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while saving object: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array(
                    'object' => $object,
                    'validate' => $validate
                )
            );
            return false;
        }

        $statement->close();

        if ($executed) {
            if (!$isUpdate && count($primaryKey) == 1) {
                return $this->getDb()->getInsertId();
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function update($whereValues, $updateValues, $limitToOne = true)
    {
        $queryFields = array();
        $fieldTypes = '';
        $fieldValues = array();
        foreach ($updateValues as $fieldName => $fieldValue) {
            // Bind
            // i   corresponding variable has type integer
            // d   corresponding variable has type double
            // s   corresponding variable has type string
            // b   corresponding variable is a blob and will be sent in packets
            if (is_integer($fieldValue)) {
                $fieldTypes .= 'i';
            } elseif (is_double($fieldValue)) {
                $fieldTypes .= 'd';
            } else {
                $fieldTypes .= 's';
            }

            $bindFieldName = 'bindUpdate' . $fieldName;
            $$bindFieldName = $fieldValue;
            $fieldValues[$bindFieldName] =  &$$bindFieldName;
        }

        foreach ($whereValues as $fieldName => $fieldValue) {
            if (is_numeric($fieldName)) {
                $queryFields[] = $fieldValue;
            } elseif (is_null($fieldValue)) {
                $queryFields[] = $fieldName . ' IS NULL';
            } elseif (is_array($fieldValue)) {
                $queryFields[] = $fieldName . ' IN (' . $this->fillQuery(count($fieldValue)) . ')';
                foreach ($fieldValue as $arrayIndex => $arrayValue) {
                    // Bind
                    // i   corresponding variable has type integer
                    // d   corresponding variable has type double
                    // s   corresponding variable has type string
                    // b   corresponding variable is a blob and will be sent in packets
                    if (is_integer($fieldValue)) {
                        $fieldTypes .= 'i';
                    } elseif (is_double($fieldValue)) {
                        $fieldTypes .= 'd';
                    } else {
                        $fieldTypes .= 's';
                    }

                    $bindFieldName = 'bindWhere' . $fieldName . $arrayIndex;
                    $$bindFieldName = $arrayValue;
                    $fieldValues[$bindFieldName] =  &$$bindFieldName;
                }
            } else {
                $queryEnd = substr($fieldName, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $queryFields[] = $fieldName . ' ?';
                } else {
                    $queryFields[] = $fieldName . ' = ?';
                }

                // Bind
                // i   corresponding variable has type integer
                // d   corresponding variable has type double
                // s   corresponding variable has type string
                // b   corresponding variable is a blob and will be sent in packets
                if (is_integer($fieldValue)) {
                    $fieldTypes .= 'i';
                } elseif (is_double($fieldValue)) {
                    $fieldTypes .= 'd';
                } else {
                    $fieldTypes .= 's';
                }

                $bindFieldName = 'bindWhere' . $fieldName;
                $$bindFieldName = $fieldValue;
                $fieldValues[$bindFieldName] =  &$$bindFieldName;
            }
        }

        $query = 'UPDATE ' . $this::DB_TABLE . ' SET ' .
                 implode( '=?, ', array_keys($updateValues)) . '=? ' .
                 'WHERE ' . implode(' AND ', $queryFields);
        if ($limitToOne) {
            $query .= ' LIMIT 1';
        }

        $statement = $this->getDb()->prepare($query);
        if (!$statement) {
            \vc\lib\ErrorHandler::error(
                'Error creating statement for update',
                __FILE__,
                __LINE__,
                array(
                    'whereValues' => var_export($whereValues, true),
                    'updateValues' => var_export($updateValues, true),
                    'limitToOne' => var_export($limitToOne, true),
                    'query' => $query
                )
            );
            return false;
        }

        $paramsBinded = call_user_func_array(
            array($statement,'bind_param'),
            array_merge(array('bindingTypes' => $fieldTypes), $fieldValues)
        );
        if (!$paramsBinded) {
            \vc\lib\ErrorHandler::error(
                'Error binding parameters for updating table',
                __FILE__,
                __LINE__,
                array(
                    'whereValues' => var_export($whereValues, true),
                    'updateValues' => var_export($updateValues, true),
                    'limitToOne' => var_export($limitToOne, true),
                    'query' => $query,
                    'fieldTypes' => var_export($fieldTypes, true),
                    'fieldValues' => var_export($fieldValues, true)
                )
            );
            $statement->close();
            return false;
        }

        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while updating table: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('whereValues' => $whereValues,
                      'updateValues' => $updateValues,
                      'limitToOne' => $limitToOne)
            );
            $statement->close();
            return false;
        }
        $statement->close();
        return true;
    }

    public function delete($whereValues, $limitToOne = false)
    {
        $queryFields = array();
        $fieldTypes = '';
        $fieldValues = array();

        foreach ($whereValues as $fieldName => $fieldValue) {
            if (is_numeric($fieldName)) {
                $queryFields[] = $fieldValue;
            } elseif (is_null($fieldValue)) {
                $queryFields[] = $fieldName . ' IS NULL';
            } elseif (is_array($fieldValue)) {
                $queryFields[] = $fieldName . ' IN (' . $this->fillQuery(count($fieldValue)) . ')';
                foreach ($fieldValue as $arrayIndex => $arrayValue) {
                    // Bind
                    // i   corresponding variable has type integer
                    // d   corresponding variable has type double
                    // s   corresponding variable has type string
                    // b   corresponding variable is a blob and will be sent in packets
                    if (is_integer($fieldValue)) {
                        $fieldTypes .= 'i';
                    } elseif (is_double($fieldValue)) {
                        $fieldTypes .= 'd';
                    } else {
                        $fieldTypes .= 's';
                    }

                    $bindFieldName = 'bindWhere' . $fieldName . $arrayIndex;
                    $$bindFieldName = $arrayValue;
                    $fieldValues[$bindFieldName] =  &$$bindFieldName;
                }
            } else {
                $queryEnd = substr($fieldName, -1);
                if ($queryEnd == '<' ||
                    $queryEnd == '>' ||
                    $queryEnd == '!=' ||
                    $queryEnd == '=') {
                    $queryFields[] = $fieldName . ' ?';
                } else {
                    $queryFields[] = $fieldName . ' = ?';
                }

                // Bind
                // i   corresponding variable has type integer
                // d   corresponding variable has type double
                // s   corresponding variable has type string
                // b   corresponding variable is a blob and will be sent in packets
                if (is_integer($fieldValue)) {
                    $fieldTypes .= 'i';
                } elseif (is_double($fieldValue)) {
                    $fieldTypes .= 'd';
                } else {
                    $fieldTypes .= 's';
                }

                $bindFieldName = 'bindWhere' . $fieldName;
                $$bindFieldName = $fieldValue;
                $fieldValues[$bindFieldName] =  &$$bindFieldName;
            }
        }

        $query = 'DELETE FROM ' . $this::DB_TABLE .
                 ' WHERE ' . implode($queryFields, ' AND ');
        if ($limitToOne) {
            $query .= ' LIMIT 1';
        }

        $statement = $this->getDb()->prepare($query);
        if (!$statement) {
            \vc\lib\ErrorHandler::error(
                'Error creating statement for delete',
                __FILE__,
                __LINE__,
                array(
                    'whereValues' => var_export($whereValues, true),
                    'limitToOne' => var_export($limitToOne, true),
                    'query' => $query
                )
            );
            return false;
        }

        $paramsBinded = call_user_func_array(
            array($statement,'bind_param'),
            array_merge(array('bindingTypes' => $fieldTypes), $fieldValues)
        );
        if (!$paramsBinded) {
            \vc\lib\ErrorHandler::error(
                'Error binding parameters for deleting from table',
                __FILE__,
                __LINE__,
                array(
                    'whereValues' => var_export($whereValues, true),
                    'limitToOne' => var_export($limitToOne, true),
                    'query' => $query,
                    'fieldTypes' => var_export($fieldTypes, true),
                    'fieldValues' => var_export($fieldValues, true)
                )
            );
            $statement->close();
            return false;
        }

        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while deleting from table: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('whereValues' => $whereValues,
                      'limitToOne' => $limitToOne)
            );
            $statement->close();
            return false;
        }
        $statement->close();
        return true;
    }

    public function stripIconChars($text)
    {
        return preg_replace(
            '/[\x{FFFD}-\x{FFFD}\x{E000}-\x{F8FF}\x{1D800}-\x{1FFFF}\x{F0000}-\x{FFFFF}\x{100000}-\x{10FFFF}]/u',
            '',
            $text
        );
    }

    protected function arrayToBindParams($oldValues)
    {
        $binParams = array();
        foreach ($oldValues as $key => $value) {
            $bindFieldName = 'bind' . $key;
            $$bindFieldName = $value;
            $binParams[$bindFieldName] =  &$$bindFieldName;
        }
        return $binParams;
    }

    public function fillQuery($count)
    {
        if ($count == 1) {
            return '?';
        } else {
            return '?' . str_repeat(',?', $count - 1);
        }
    }
}
