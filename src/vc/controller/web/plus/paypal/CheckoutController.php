<?php
namespace vc\controller\web\plus\paypal;

class CheckoutController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        try {
            $formValues = $_POST;

            if (empty($formValues['package']) ||
                empty($formValues['duration'])) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                    array(
                        'formValues' => $formValues
                    )
                );
                throw new \vc\exception\RedirectException($this->path . 'plus/');
            }

            $package = $formValues['package'];
            $duration = intval($formValues['duration']);
            $price = null;
            foreach (\vc\object\Plus::$packages as $packageArray) {
                if ($packageArray[0] == $package) {
                    $price = $packageArray[1];
                }
            }
            if ($price === null || $duration === 0) {
                throw new \vc\exception\RedirectException($this->path . 'plus/');
            }

            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');

            $item = new \PayPal\Api\Item();
            $item->setName(gettext('plus.package.' . $package))
                 ->setCurrency('EUR')
                 ->setQuantity($duration)
                 ->setSku($package)
                 ->setPrice(number_format($price, 2, '.', ''));

            $itemList = new \PayPal\Api\ItemList();
            $itemList->setItems(array($item));

            $amount = new \PayPal\Api\Amount();
            $amount->setCurrency('EUR')
                   ->setTotal($duration * $price);

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount)
                        ->setItemList($itemList)
                        ->setInvoiceNumber(uniqid());

            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl($this->getServerRoot() . 'plus/paypal/confirm')
                         ->setCancelUrl($this->getServerRoot() . 'plus');

            $payment = new \PayPal\Api\Payment();
            $payment->setIntent('sale')
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions(array($transaction));

            $plusComponent = $this->getComponent('Plus');
            $apiContext = $plusComponent->getPaypalApiContext();

            $payment->create($apiContext);

        } catch (\Exception $exception) {
            $debugInfo = array('stacktrace' => var_export($exception->getTraceAsString(), true));
            if (!empty($transaction)) {
                $debugInfo['transaction'] = $transaction;
            }
            if ($exception instanceof \PayPal\Exception\PayPalConnectionException) {
                $debugInfo['code'] = $exception->getCode();
                $debugInfo['data'] = $exception->getData();
            }
            \vc\lib\ErrorHandler::error(
                'Paypal Checkout Error: ' . get_class($exception) . ': ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $debugInfo
            );
            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                gettext('plus.paypal.confirm.failed')
            );
            throw new \vc\exception\RedirectException(
                $this->path . 'plus/?notification=' . $notification
            );
        }

        throw new \vc\exception\RedirectException(
            $payment->getApprovalLink()
        );
    }
}
