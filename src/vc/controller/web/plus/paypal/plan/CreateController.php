<?php
namespace vc\controller\web\plus\paypal\plan;

class CreateController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }


        if (empty($request->getText('package'))) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $request->getValues()
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        $package = $request->getText('package');
        $price = null;
        foreach (\vc\object\Plus::$packages as $packageArray) {
            if ($packageArray[0] == $package) {
                $price = $packageArray[1];
            }
        }
        if ($price === null) {
            throw new \vc\exception\RedirectException($this->path . 'plus/');
        }

        // :TODO: JOE (!) auto kill payment plan on profile delete
        // :TODO: JOE (!) handle failed payments on plan => auto cancel plus and inform user + mod. No punishment (for now)

        $plan = new \PayPal\Api\Plan();

        // Basic Information
        $plan->setName('T-Shirt of the Month Club Plan') // :TODO: JOE - name
             ->setDescription('Template creation.') // :TODO: JOE - description
             ->setType('INFINITE');

        // Payment definitions for this billing plan.
        $paymentDefinition = new \PayPal\Api\PaymentDefinition();

        $paymentDefinition->setName('Regular Payments') // :TODO: JOE - name
                          ->setType('REGULAR')
                          ->setFrequency('Month')
                          ->setFrequencyInterval('1')
                          // :TODO: JOE - rounding error? cent amount?
                          ->setAmount(new \PayPal\Api\Currency(array('value' => $price, 'currency' => 'EUR')));

        $merchantPreferences = new \PayPal\Api\MerchantPreferences();

        // ReturnURL and CancelURL are not required and used when creating billing agreement with payment_method as 'credit_card'.
        // However, it is generally a good idea to set these values, in case you plan to create billing agreements which accepts 'paypal' as payment_method.
        // This will keep your plan compatible with both the possible scenarios on how it is being used in agreement.
        $merchantPreferences->setReturnUrl($this->getServerRoot() . 'plus/paypal/plan/execute/')
                            ->setCancelUrl($this->getServerRoot() . 'plus/')
                            ->setAutoBillAmount('yes')
                            ->setInitialFailAmountAction('CONTINUE')
                            ->setMaxFailAttempts('0');
        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        $plusComponent = $this->getComponent('Plus');
        $apiContext = $plusComponent->getPaypalApiContext();

        try {
            $plan = $plan->create($apiContext);


            $patch = new \PayPal\Api\Patch();
            $value = new \PayPal\Common\PayPalModel('{
                   "state":"ACTIVE"
                 }');
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);
            $patchRequest = new \PayPal\Api\PatchRequest();
            $patchRequest->addPatch($patch);
            $plan->update($patchRequest, $apiContext);

            $plan = \PayPal\Api\Plan::get($plan->getId(), $apiContext);

            $agreement = new \PayPal\Api\Agreement();

            $agreement->setName('Base Agreement') // :TODO: JOE - name
                ->setDescription('Basic Agreement') // :TODO: JOE - name
                ->setStartDate(date('Y-m-d\\TH:i:s\\Z'));

            $agreementPlan = new \PayPal\Api\Plan();
            $agreementPlan->setId($plan->getId());
            $agreement->setPlan($agreementPlan);

            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');
            $agreement->setPayer($payer);

            $agreement = $agreement->create($apiContext);

            $approvalUrl = $agreement->getApprovalLink();

            var_export($plan);
            var_export($agreement);
            var_export($approvalUrl);

//            After the customer approves an agreement for a recurring PayPal payment, you execute the agreement.
//
//This sample request shows how to use the execute link with the payment token from the previous sample response to execute the agreement:
//
//curl -v -X POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/EC-7WN97463LN263864T/agreement-execute \
//-H "Content-Type:application/json" \

        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                'Paypal Plan Error: ' . get_class($exception) . ': ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                array(
                    'code' => $exception->getCode(),
                    'data' => $exception->getData(),
                    'stacktrace' => var_export($exception->getTraceAsString(), true)
                )
            );

            // :TODO: JOE - error message

        }



exit();









        /*
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $item = new \PayPal\Api\Item();
        $item->setName(gettext('plus.package.' . $package))
             ->setCurrency('EUR')
             ->setQuantity($duration)
             ->setSku($package)
             ->setPrice($price);

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

        try {
            $payment->create($apiContext);
        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                get_class($exception) . ': ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                array(
                    'code' => $exception->getCode(),
                    'data' => $exception->getData(),
                    'stacktrace' => var_export($exception->getTraceAsString(), true)
                )
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
        */
    }
}
