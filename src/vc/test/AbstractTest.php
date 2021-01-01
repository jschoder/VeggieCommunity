<?php
namespace vc\test;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $tables = array();

    /**
     * @var \vc\model\db\DbConnection
     */
    private $db;

    private $modelFactory;

    public function __construct()
    {
        $this->db = new \vc\model\db\DbConnection(
            'test'
        );

        $this->modelFactory = new \vc\model\ModelFactory(
            $this->db,
            'local'
        );
    }

    /**
     * @return \vc\model\db\AbstractModel
     */
    public function getModel($modelName)
    {
        return $this->modelFactory->getModel($modelName);
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($modelName)
    {
        return $this->modelFactory->getDbModel($modelName);
    }

    public function setUp()
    {
        foreach ($this->tables as $table) {
            $this->copyTable($table);
        }
    }

    protected function copyTable($table)
    {
        $query = 'TRUNCATE ' . $table;
        $this->db->executePrepared($query);

        $query = 'INSERT INTO ' . $table . ' SELECT * FROM veggiec.' . $table;
        $this->db->queryPrepared($query);

    }

    public function tearDown()
    {
    }

    public function truncate($table)
    {
        $query = 'TRUNCATE ' . $table;
        $this->db->executePrepared($query);
    }

    public function assertRowCount($rowCount, $table, $queryParams = array())
    {
        $count = $this->getCount($table, $queryParams);
        $this->assertEquals(
            $rowCount,
            $count,
            'Table ' . $table . ' doesn\'t have the expected rowCount ' . $rowCount . ' but ' . $count . ' instead.'
        );
    }

    protected function getRowCount($table, $queryParams = array(), $joins = array())
    {
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

        $query = 'SELECT count(*) FROM ' . $table .
                 ' ' . implode(' ', $joins);
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        $statement = $this->db->queryPrepared($query, $whereParams);
        if (!$statement) {
            echo 'Error creating statement for counting rows.' . "\n",
            var_export($query);
            echo "\n";
            var_export($whereParams);
            echo "\n\n";
        }
        $statement->bind_result($count);
        $statement->fetch();
        $statement->close();

        return $count;
    }
}
