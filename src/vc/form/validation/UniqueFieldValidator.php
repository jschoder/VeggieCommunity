<?php
namespace vc\form\validation;

class UniqueFieldValidator extends AbstractValidator
{
    private $table;

    private $field;

    private $message;

    private $postfix;

    private $idField;

    private $fieldValue;

    public function __construct($table, $field, $message, $postfix = null, $idField = null, $fieldValue = null)
    {
        $this->table = $table;
        $this->field = $field;
        $this->message = $message;
        $this->postfix = $postfix;
        $this->idField = $idField;
        $this->fieldValue = $fieldValue;
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        $formValue = $this->getValue($formElement->getName(), $formValues);
        if (!empty($formValue)) {
            $query = 'SELECT 1 FROM ' . $this->table . ' WHERE ' . $this->field . ' = ?';
            $queryParams = array($formValue);
            if (!empty($this->postfix)) {
                $query .= ' AND ' . $this->postfix;
            }
            if (!empty($this->idField)) {
                $query .= ' AND ' . $this->idField . ' != ?';
                $queryParams[] = $this->fieldValue;
            }
            $query .= ' LIMIT 1';

            $statement = $db->queryPrepared($query, $queryParams);
            $statement->store_result();
            $numRows = $statement->num_rows;
            $statement->close();

            if ($numRows > 0) {
                return array($this->message);
            }
        }
        return null;
    }
}
