<?php
namespace vc\form\validation;

class OwnPasswordValidator extends AbstractValidator
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        $query = 'SELECT 1 FROM vc_profile WHERE id = ? AND `password` = SHA1(CONCAT(salt, ?, salt)) LIMIT 1';
        $statement = $db->queryPrepared($query, array($this->userId, $formValues['password']));
        $statement->store_result();
        $numRows = $statement->num_rows;
        $statement->close();

        if ($numRows == 0) {
            return array(gettext('form.validator.ownpassword'));
        }

        return null;
    }
}
