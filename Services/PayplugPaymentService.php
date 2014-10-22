<?php

namespace Alcalyn\PayplugBundle\Services;

use Symfony\Component\Routing\Router;
use Alcalyn\PayplugBundle\Model\Payment;
use Alcalyn\PayplugBundle\Exceptions\PayplugUndefinedAccountParameterException;

class PayplugPaymentService
{
    /**
     * Payplug url from account configuration
     * 
     * @var string
     */
    private $baseUrl;
    
    /**
     * Your private key from account configuration
     * 
     * @var string
     */
    private $privateKey;
    
    /**
     * IPN url
     * 
     * @var string
     */
    private $ipnUrl;
    
    /**
     * @param string $baseUrl Payplug url from account configuration
     * @param string $privateKey Your private key from account configuration
     */
    public function __construct($baseUrl, $privateKey, Router $router)
    {
        $this->baseUrl = $baseUrl;
        $this->privateKey = $privateKey;
        $this->ipnUrl = $router->generate('payplug_ipn', array(), true);
    }
    
    /**
     * Generate payment url for $payment
     * 
     * @param Payment $payment
     * 
     * @return string
     */
    public function generateUrl(Payment $payment)
    {
        if (null === $this->privateKey) {
            throw new PayplugUndefinedAccountParameterException('payplug_account_yourPrivateKey');
        }
        
        if (null === $this->baseUrl) {
            throw new PayplugUndefinedAccountParameterException('payplug_account_url');
        }
        
        // Create data parameter
        $params = $this->convertPaymentToArray($payment);
        $url_params = http_build_query($params);
        $data = urlencode(base64_encode($url_params));
        
        // Create signature parameter
        $signature = null;
        $privatekey = openssl_pkey_get_private($this->privateKey);
        openssl_sign($url_params, $signature, $privatekey, OPENSSL_ALGO_SHA1);
        $signatureBase64 = urlencode(base64_encode($signature));
        
        return $this->baseUrl . '?data=' . $data . '&sign=' . $signatureBase64;
    }
    
    /**
     * Return default ipn url used by the bundle (Something like "http://yoursite.com/payplug_ipn").
     * 
     * @return string
     */
    public function getIpnUrl()
    {
        return $this->ipnUrl;
    }
    
    /**
     * @param \Alcalyn\PayplugBundle\Model\Payment $payment
     * 
     * @return array
     */
    private function convertPaymentToArray(Payment $payment)
    {
        return array(
            'amount'        => $payment->getAmount(),
            'currency'      => $payment->getCurrency(),
            'ipn_url'       => $payment->getIpnUrl() ?: $this->ipnUrl,
            'return_url'    => $payment->getReturnUrl(),
            'cancel_url'    => $payment->getCancelUrl(),
            'email'         => $payment->getEmail(),
            'first_name'    => $payment->getFirstName(),
            'last_name'     => $payment->getLastName(),
            'customer'      => $payment->getCustomer(),
            'order'         => $payment->getOrder(),
            'custom_data'   => $payment->getCustomData(),
            'origin'        => $payment->getOrigin(),
        );
    }
}
