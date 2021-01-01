<?php
namespace vc\model\db;

class PlusDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_plus';
    const OBJECT_CLASS = '\\vc\object\\Plus';

    public function getPlusLevel($userId)
    {
        $query = 'SELECT MAX(plus_type) FROM vc_plus
                  WHERE user_id = ? AND ? BETWEEN start_date AND end_date';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($userId), date('Y-m-d H:i:s'))
        );
        $statement->bind_result(
            $plusType
        );
        $statement->fetch();
        $statement->close();
        return $plusType;
    }

    public function create($userId, $plusType, $newPlusMonth, $paymentType, $paymentId)
    {
        // :TODO: JOE - extended debug

        $success = true;
        $now = time();

        $activePlusAccounts = $this->loadObjects(array(
            'user_id' => $userId,
            'end_date >' => date('Y-m-d H:i:s', $now)
        ));

        $startNewPlus = $now;
        $differentPlusTypeActive = false;
        if (!empty($activePlusAccounts)) {
            foreach ($activePlusAccounts as $account) {
                if ($account->plusType !== $plusType) {
                    $differentPlusTypeActive = true;
                }
                $startNewPlus = max($startNewPlus, strtotime($account->endDate) + 1);
            }

            if ($differentPlusTypeActive) {
                $activePlusIds = array();
                $restValue = 0;
                foreach ($activePlusAccounts as $account) {
                    $activePlusIds[] = $account->id;

                    if (strtotime($account->startDate) > time()) {
                        // Unstarted plus accounts
                        $startDatetime = new \DateTime($account->startDate);
                        $endDatetime = new \DateTime($account->endDate);
                        $diff = $endDatetime->diff($startDatetime);
                        $restValue += intval($diff->format('%m')) * \vc\object\Plus::$packages[$account->plusType][1];
                    } else {
                        // Started plus accounts (rest value only)
                        $startDatetime = new \DateTime();
                        $endDatetime = new \DateTime($account->endDate);
                        $diff = $endDatetime->diff($startDatetime);

                        $month = intval($diff->format('%m'));
                        $days = intval($diff->format('%d'));
                        $daysInMonth = intval($startDatetime->format('t'));
                        $hours = intval($diff->format('%H'));
                        $minutes = intval($diff->format('%i'));

                        $monthFraction = $month + ($days + ($hours / 24.0) + ($minutes / 1440.0)) / $daysInMonth;
                        $restValue += $monthFraction * \vc\object\Plus::$packages[$account->plusType][1];
                    }
                }

                // Creating an account with the rest values
                $startDatetime = new \DateTime();
                $endDatetime = new \DateTime();
                $monthToAdd = $restValue / \vc\object\Plus::$packages[$plusType][1];
                $flooredMonthToAdd = floor($monthToAdd);
                $endDatetime->add(new \DateInterval('P' . $flooredMonthToAdd . 'M'));
                $flooredDaysToAdd = floor(intval($endDatetime->format('t')) * ($monthToAdd - $flooredMonthToAdd));
                $endDatetime->add(new \DateInterval('P' . $flooredDaysToAdd . 'D'));

                $plusObject = new \vc\object\Plus();
                $plusObject->userId = $userId;
                $plusObject->plusType = $plusType;
                $plusObject->startDate = $startDatetime->format('Y-m-d H:i:s');
                $plusObject->endDate = $endDatetime->format('Y-m-d H:i:s');
                $plusObject->paymentType = \vc\object\Plus::PAYMENT_TYPE_RESTVALUE;
                $plusObject->paymentId = null;
                $insertedRestValue = $this->insertObject(null, $plusObject);

                $insertedRestValue = true;

                if ($insertedRestValue) {
                    // Updating the old active accounts
                    $this->update(
                        array(
                            'id' => $activePlusIds
                        ),
                        array(
                            'end_date' => $startDatetime->format('Y-m-d H:i:s')
                        ),
                        false
                    );

                    $startNewPlus = $endDatetime->getTimestamp() + 1;
                } else {
                    $success = false;
                }
            }
        }

        $dateTime = new \DateTime(date('Y-m-d H:i:s', $startNewPlus));
        $dateTime->add(new \DateInterval('P' . intval($newPlusMonth) . 'M'));
        $plusObject = new \vc\object\Plus();
        $plusObject->userId = $userId;
        $plusObject->plusType = $plusType;
        $plusObject->startDate = date('Y-m-d H:i:s', $startNewPlus);
        $plusObject->endDate = $dateTime->format('Y-m-d H:i:s');
        $plusObject->paymentType = $paymentType;
        $plusObject->paymentId = $paymentId;
        $inserted = $this->insertObject(null, $plusObject);
        if (!$inserted) {
            $success = false;
        }

        return $success;
    }
}
