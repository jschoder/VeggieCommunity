<?php
namespace vc\controller\web\plus\paypal;

class ConfirmController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        if (empty($_GET['paymentId']) ||
            empty($_GET['PayerID'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'get' => $_GET
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        try {
            $paymentId = $_GET['paymentId'];
            $payerId = $_GET['PayerID'];

            $this->setTitle(gettext('plus.book.title'));
            $plusComponent = $this->getComponent('Plus');
            $apiContext = $plusComponent->getPaypalApiContext();
            $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);

            $this->getView()->set('payment', $payment);
            $this->getView()->set('payerId', $payerId);
            echo $this->getView()->render('plus/paypal/confirm', true);
        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                'Paypal Confirm Error: ' . get_class($exception) . ': ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                array(
                    'code' => $exception->getCode(),
                    'data' => $exception->getData(),
                    'stacktrace' => var_export($exception->getTraceAsString(), true)
                )
            );

            $systemMessageModel = $this->getDbModel('SystemMessage');
            $systemMessageModel->informModerators(
                'Paypal Payment Error #1',
                'User ' . $this->getSession()->getUserId() . ' couldn\'t pay due to a system error.'
            );

            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                gettext('plus.paypal.confirm.failed')
            );
            throw new \vc\exception\RedirectException(
                $this->path . 'plus/?notification=' . $notification
            );
        }
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        $formValues = $_POST;

        if (empty($formValues['payment_id']) ||
            empty($formValues['payer_id'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        try {
            $paymentId = $formValues['payment_id'];
            $payerId = $formValues['payer_id'];

            $plusComponent = $this->getComponent('Plus');
            $apiContext = $plusComponent->getPaypalApiContext();
            $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);

            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $apiContext);
            if ($result->getState() == 'approved') {
                $plusModel = $this->getDbModel('Plus');
                $plusPaypalPayment = $this->getDbModel('PlusPaypalPayment');
                $success = true;
                foreach ($payment->getTransactions() as $transaction) {
                    $paypalPaymentObject = new \vc\object\PlusPaypalPayment();
                    $paypalPaymentObject->paymentId = $payment->getId();
                    $paypalPaymentObject->payerId = $payment->getPayer()->getPayerInfo()->getPayerId();
                    $paypalPaymentObject->paypalCreateTime = date('Y-m-d H:i:s', strtotime($payment->getCreateTime()));
                    $paypalPaymentObject->paypalUpdateTime = date('Y-m-d H:i:s', strtotime($payment->getUpdateTime()));
                    $paymentId = $plusPaypalPayment->insertObject(
                        $this->getSession()->getProfile(),
                        $paypalPaymentObject
                    );

                    foreach ($transaction->getItemList()->getItems() as $item) {
                        $selectedPlusType = null;
                        foreach (\vc\object\Plus::$packages as $plusType => $packageArray) {
                            if ($packageArray[0] == $item->getSku()) {
                                $selectedPlusType = $plusType;
                            }
                        }
                        if ($selectedPlusType === null) {
                            \vc\lib\ErrorHandler::error(
                                'Invalid Item-SKU: ' . $item->getSku(),
                                __FILE__,
                                __LINE
                            );

                            // Using the mini package as fallback
                            $selectedPlusType = \vc\object\Plus::PLUS_TYPE_MINI;
                        }

                        $success = $success && $plusModel->create(
                            $this->getSession()->getUserId(),
                            $selectedPlusType,
                            $item->getQuantity(),
                            \vc\object\Plus::PAYMENT_TYPE_PAYPAL,
                            $paymentId
                        );
                    }
                }

                $profileModel = $this->getDbModel('Profile');
                $profileModel->updatePlusMarker(
                    $this->getSession()->getUserId(),
                    $this->getSession()->getSetting(\vc\object\Settings::PLUS_MARKER)
                );

                // Updating the plus information in the current session (Allows immediate access to plus features)
                $profiles = $profileModel->getProfiles($this->locale, array($this->getSession()->getUserId()));
                $this->getSession()->updateSession($profiles[0]);

                if ($success) {
                    $notification = $this->setNotification(
                        self::NOTIFICATION_SUCCESS,
                        gettext('plus.paypal.confirm.success')
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'plus/history/?notification=' . $notification
                    );
                } else {
                    $notification = $this->setNotification(
                        self::NOTIFICATION_ERROR,
                        gettext('plus.paypal.confirm.failed')
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'plus/?notification=' . $notification
                    );
                }
            } else {
                $systemMessageModel = $this->getDbModel('SystemMessage');
                $systemMessageModel->informModerators(
                    'Paypal Payment Error #2',
                    'User ' . $this->getSession()->getUserId() . ' couldn\'t pay due to a system error.'
                );

                $notification = $this->setNotification(
                    self::NOTIFICATION_ERROR,
                    gettext('plus.paypal.confirm.failed')
                );
                throw new \vc\exception\RedirectException(
                    $this->path . 'plus/?notification=' . $notification
                );
            }
        } catch (\Exception $exception) {
            if ($exception instanceof \vc\exception\RedirectException) {
                throw $exception;
            }

            $debugInfo = array('stacktrace' => var_export($exception->getTraceAsString(), true));
            if ($exception instanceof \PayPal\Exception\PayPalConnectionException) {
                $data = $exception->getData();
                if (is_object($data) && !empty($data->name)) {
                    if ($data->name === 'INSTRUMENT_DECLINED') {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_WARNING,
                            gettext('plus.paypal.confirm.failed.instrumentDeclined')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'plus/?notification=' . $notification
                        );
                    }
                }
                $debugInfo['code'] = $exception->getCode();
                $debugInfo['url'] = $exception->getUrl();
                $debugInfo['data'] = $data;
            }

            \vc\lib\ErrorHandler::error(
                'Paypal Confirm Error: ' . get_class($exception) . ': ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $debugInfo
            );

            $systemMessageModel = $this->getDbModel('SystemMessage');
            $systemMessageModel->informModerators(
                'Paypal Payment Error #3',
                'User ' . $this->getSession()->getUserId() . ' couldn\'t pay due to a system error.'
            );

            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                gettext('plus.paypal.confirm.failed')
            );
            throw new \vc\exception\RedirectException(
                $this->path . 'plus/?notification=' . $notification
            );
        }
    }
}
