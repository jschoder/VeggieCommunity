<?php
namespace vc\form\validation;

class PedobearAgeValidator extends AbstractValidator
{
    private $userId;
    private $ownAge;
    private $ip;
    private $minimum;
    private $maximum;

    public function __construct($userId, $ownAge, $ip, $minimum, $maximum)
    {
        $this->userId = $userId;
        $this->ownAge = $ownAge;
        $this->ip = $ip;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        if (!($formElement instanceof \vc\form\NumberRange)) {
            \vc\lib\ErrorHandler::error(
                'Invalid class for validator ' . get_class($formElement),
                __FILE__,
                __LINE__,
                array('formValues' => $formValues)
            );
        } else {
            $formValue = $this->getValue($formElement->getName(), $formValues);

            if (empty($formValue['from']) || empty($formValue['to'])) {
                \vc\lib\ErrorHandler::error(
                    'Validation of pedobear age failed.',
                    __FILE__,
                    __LINE__,
                    array(
                        'formValue' => $formValue
                    )
                );
            } else {
                $from = intval($formValue['from']);
                $to = intval($formValue['to']);
                if ($from !== $this->minimum || $to !== $this->maximum) {
                    if ($this->ownAge > 20 && $from < 16) {
                        if ($this->ownAge > 25) {
                            $this->addModMessage(
                                $db,
                                $this->userId,
                                $this->ip,
                                'PedoCheck: User entered an invalid age range (#1)' . "\n" .
                                "User: " . $this->userId . "\n" .
                                "Age: " . $this->ownAge . "\n" .
                                "Searching Romantic From: " . $from . "\n" .
                                "Searching Romantic To: " . $to
                            );
                        }
                        return array(gettext('form.validator.pedobear'));

                    } else if ($this->ownAge > 29 && $from < 18) {
                        if ($this->ownAge > 40) {
                            $this->addModMessage(
                                $db,
                                $this->userId,
                                $this->ip,
                                'PedoCheck: User entered an invalid age range (#2)' . "\n" .
                                "User: " . $this->userId . "\n" .
                                "Age: " . $this->ownAge . "\n" .
                                "Searching Romantic From: " . $from . "\n" .
                                "Searching Romantic To: " . $to
                            );
                        }
                        return array(gettext('form.validator.pedobear'));
                    }
                }
            }
            return null;
        }
    }

    /**
     *
     * @param \vc\model\db\DbConnection $db
     * @param type $userId
     * @param type $ip
     * @param type $message
     */
    private function addModMessage($db, $userId, $ip, $message)
    {
        $db->executePrepared(
            'INSERT INTO vc_mod_message SET user_id = ?, ip = ?, message = ?, created_at = ?',
            array(
                $userId,
                $ip,
                $message,
                date('Y-m-d H:i:s')
            )
        );
    }
}



