<?php
namespace vc\component;

class PlusComponent extends AbstractComponent
{
    public function getPaypalApiContext()
    {
        $paypalConfig = \vc\config\Globals::$apps[$this->getServer()]['paypal'];
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $paypalConfig['clientId'],
                $paypalConfig['secret']
            )
        );
        if ($paypalConfig['sandbox'] === true) {
            $apiContext->setConfig(array(
               'mode' => 'sandbox',
               'log.LogEnabled' => true,
               'log.FileName' => TMP_DIR . '/PayPal.log',
               'log.LogLevel' => 'DEBUG',
               'validation.level' => 'strict',
               'cache.enabled' => 'true',
               'cache.FileName' => TMP_DIR . '/PayPalAuth.tmp'
            ));
        } else {
            $apiContext->setConfig(array(
               'mode' => 'live',
               'cache.enabled' => 'true',
               'cache.FileName' => TMP_DIR . '/PayPalAuth.tmp'
            ));
        }
        return $apiContext;
    }
}
