<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Gateway\Request\HtmlRedirect;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use EonInfosys\PortWallet\Helper\Data;

/**
 * Class OrderDataBuilder
 */
class OrderDataBuilder implements BuilderInterface
{

    private $live_endpointUrl = "https://api.portwallet.com/payment/v2/invoice/";
    private $sandbox_endpointUrl = "https://api-sandbox.portwallet.com/payment/v2/invoice/";

    protected $_paymentURL="https://payment.portwallet.com/payment/";
    protected $_paymentSandboxURL="https://payment-sandbox.portwallet.com/payment/";


    /**
     * Your own reference number for this purchase. It is returned to you along
     * with the authorisation results by whatever method you have chosen for
     * being informed (email and / or Payment Responses).
     */
    const ORDER_ID = 'order_id';

    /**
     * A decimal number giving the cost of the purchase in terms of the major
     * currency unit e.g. 12.56 would mean 12 pounds and 56 pence if the
     * currency were GBP (Pounds Sterling). Note that the decimal separator
     * must be a dot (.), regardless of the typical language convention for the
     * chosen currency. The decimal separator does not need to be included if
     * the amount is an integral multiple of the major currency unit. Do not
     * include other separators, for example between thousands.
     */
    const AMOUNT = 'amount';

    /**
     * 3 letter ISO code for the currency of this payment.
     */
    const CURRENCY = 'currency';


    /**
     * Order description (Note)
     */
    const PRODUCT_DESCRIPTION = 'product_description';


    /* Your portwallet app_key. Application key
        Character limit (Max): 32 char
    */

    const API_KEY = 'app_key';

    const SECRET_KEY= 'secret_key';

    const TOKEN= 'token';
    const TIMESTAMP='timestamp';

    const FUNCTION_CALL="call";
    const ORDER_REF="reference";
    const VALIDITY="validity";

    /**
     * The shopper's full name, including any title, personal
     * name and family name.
     * Note that if you do not pass through a name, and use
     * Payment Responses, the name that the cardholder
     * enters on the payment page is returned to you as the
     * value of name in the Payment Responses message.
     * Also note that if you are sending a test submission you
     * can specify the type of response you want from our
     * system by entering REFUSED, AUTHORISED, ERROR or
     * CAPTURED as the value in the name parameter. You
     * can also generate an AUTHORISED response by using a
     * real name, such as, J. Bloggs.
     */
    const NAME = 'name';

    /**
     * The first line of the shopper's address. Encode newlines
     * as "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper
     */
    const ADDRESS = 'address';

    const STREET = 'street';


    /**
     * The town or city. Encode newlines as "&#10;" (the
     * HTML entity for ASCII 10, the new line character).
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper.
     */
    const CITY = 'city';

    /**
     * The shopper’s region/county/state. Encode newlines as
     * "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     */
    const STATE = 'state';

    /**
     * The shopper's postcode.
     * Note that at your request we can assign mandatory
     * status to this parameter. That is, if it is not supplied in
     * the order details then the shopper must enter it in the
     * payment pages.
     */
    const ZIPCODE = 'zipcode';

    /**
     * The shopper's country, as 2-character ISO code,
     * uppercase.
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper.
     */
    const COUNTRY = 'country';

    /**
     * The shopper's telephone number.
     */
    const TELEPHONE = 'phone';

    /**
     * The shopper's email address.
     */
    const EMAIL = 'email';

    /**
     * Getway return url.
     * string (required) Example: http://www.yoursite.com
     * Redirect URL for after payment.http://www.yoursite.com
     */
    const REDIRECT_URL = 'redirect_url';

    const IPN_URL = 'ipn_url';


    /**
     * The shopper's ship_to_name.
     */
    const SHIP_TO_NAME = 'ship_to_name';

    /**
     * The shopper's ship_to_Email.
     */
    const SHIP_TO_EMAIL = 'ship_to_email';


    /**
     * The shopper's ship to PHONE.
     */
    const SHIP_TO_PHONE = 'ship_to_phone';


    /**
     * The shopper's ship_to_address.
     */
    const SHIP_TO_ADDRESS = 'ship_to_address';

    /**
     * The shopper's ship_to_city.
     */
    const SHIP_TO_CITY = 'ship_to_city';

    /**
     * The shopper's ship_to_state.
     */
    const SHIP_TO_STATE = 'ship_to_state';

    /**
     * The shopper's ship_to_code.
     */
    const SHIP_TO_ZIPCODE = 'ship_to_zipcode';

    /**
     * The shopper's ship_to_country.
     */
    const SHIP_TO_COUNTRY = 'ship_to_country';


    /**
     * The URL to process the response from gateway
     */
   /* const PAYMENT_CALLBACK = 'MC_callback';*/

    /**
     * Response url
     */
    const RESPONSE_URL = 'portwallet/htmlRedirect/response';

    /* String (optional) Example: http://www.yoursite.com/ipn
     This is ipn url. Where the system will notify when the payment was made.
    Tt will notify only on success/failed/refund payment.http://www.yoursite.com/ipn
    Character limit (Max): 250 char
    */

    const IPN_RESPONSE = 'portwallet/htmlRedirect/ipn';


    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlHelper;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /*
      EonInfosys\PortWallet\Helper\Data;
    */
    protected $helperData;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlHelper
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlHelper,
        Data $helperData,
        ResolverInterface $localeResolver
    ) {
        $this->config = $config;
        $this->urlHelper = $urlHelper;
        $this->localeResolver = $localeResolver;
        $this->helperData=$helperData;
    }



    private function getConfigaration($storeId)
    {
        $config = array();

        $app_key = $this->config->getValue('app_key', $storeId);
        $secret_key = $this->config->getValue('secret_key', $storeId);
        $debug = $this->config->getValue('debug', $storeId);
        $test = $this->config->getValue('sandbox_flag', $storeId);
        $time=time();

        if ($app_key && $secret_key) {
            $key=$secret_key.$time;
            $config['app_key'] = $app_key;
            $config['secret_key'] =  $secret_key;
            $config['timestamp'] =  $time;
            $config['token'] = md5($key);
            $config['debug'] = $debug;
            $config['test'] = $test;
            $config['authorization'] = "Bearer ".base64_encode($app_key.':'.md5($secret_key.time()));
        }
        return $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $storeId = $order->getStoreId();
        $payment = $paymentDO->getPayment();

        $config = $this->getConfigaration($storeId);
        $product_description = "";
        $isDiscount=false;
        $isEmi=false;

        $orderId=$order->getId();

        $orderIncrementedId = $order->getOrderIncrementId();

        $requestArray=array();

        $orderData=[
            //self::AMOUNT=>sprintf('%.2F', $order->getGrandTotalAmount()),
            self::AMOUNT=>(double)($order->getGrandTotalAmount()),
            self::CURRENCY=>"BDT",
            self::REDIRECT_URL=>$this->getGetwayReturnUrl(),
            self::IPN_URL=>$this->getIPNUrl(),
            self::ORDER_REF=>$orderIncrementedId,
            self::VALIDITY=>1000,
        ];

        $requestArray['order']=$orderData;


       /* $i = 0;
        foreach ($order->getItems() as $item) {
            $i++;
            if ($product_description) $product_description .= ",";
            $product_description .= $item->getName();
        }
        $product_description = substr($product_description, 0, 150);

        $productData['name']=$product_description;
        $productData['description']=$product_description;*/

        $productData['name']="Order #".$orderIncrementedId;
        $productData['description']="Order from ";


        $requestArray['product']=$productData;


        $address = $order->getBillingAddress();
        $zipcode = null;
        $billingData=array();
        if (!empty($address)) {
            $name = substr($this->getName($address), 0, 50);
            $city = substr($address->getCity(), 0, 50);
            $_street = $address->getStreetLine1() . " " . $address->getStreetLine2();
            $_street = substr($_street, 0, 200);
            $region = substr($address->getRegionCode(), 0, 50);
            $zipcode = substr($address->getPostcode(), 0, 50);
            if(empty($zipcode)) $zipcode=1000;

            $billingData[self::NAME]=$name;
            $billingData[self::EMAIL]=$address->getEmail();
            $billingData[self::TELEPHONE]=$address->getTelephone();
            $billingData[self::ADDRESS]=$bAddress=[
                self::STREET =>$_street,
                self::CITY =>$city,
                self::STATE =>$city,
                self::ZIPCODE =>$zipcode,
                self::COUNTRY =>$address->getCountryId()
            ];
            $requestArray['billing']['customer']=$billingData;
        }

        $shippingAddress = $order->getShippingAddress();
        if (!empty($shippingAddress)) {

            $ship_to_name = substr($this->getName($shippingAddress), 0, 50);
            $ship_to_city = substr($shippingAddress->getCity(), 0, 50);
            $ship_to_address = $shippingAddress->getStreetLine1() . " " . $shippingAddress->getStreetLine2();
            $ship_to_address = substr($ship_to_address, 0, 200);
            $ship_to_state = substr($shippingAddress->getRegionCode(), 0, 50);
            $ship_to_zipcode = substr($shippingAddress->getPostcode(), 0, 50);
            if(empty($ship_to_zipcode)) $ship_to_zipcode=1000;

            $shippingData[self::NAME] = $ship_to_name;
            $shippingData[self::EMAIL] = $shippingAddress->getEmail();
            $shippingData[self::TELEPHONE] = $shippingAddress->getTelephone();
            $shippingData[self::ADDRESS] = $sAddress = [
                self::STREET => $ship_to_address,
                self::CITY => $ship_to_city,
                self::STATE => $ship_to_city,
                self::ZIPCODE => $ship_to_zipcode,
                self::COUNTRY => $shippingAddress->getCountryId()
            ];

            $requestArray['shipping']['customer']=$shippingData;
        }

        if($isDiscount){
            $discount['enable']=1;
            $discount['codes']=array('eid18','10%Ebl');
            $requestArray['discount']=$discount;
        }

        if($isEmi){
            $emi['enable']=1;
            $emi['tenors']=array(3,6,9,12);
            $requestArray['emi']=$emi;
        }
       /* $customs=array('var1'=>'value1','var2'=>'value2');
        $requestArray['customs']=$customs;*/


        $requestUrl = $this->invoiceRequestUrl($config);

        $response = $this->getInvoiceIdFormGetway(json_encode($requestArray), $requestUrl, $config);



        $_result = array();
        $_invoiceData = array();

        if ($response) {
            $_result['response'] = $response;
            $data = json_decode($response);

            if (isset($data->result) && $data->result == 'success') {

                $invoice_id = $data->data->invoice_id;
                $redirectURL = $data->data->action->url;
                $_result['result'] = $data->result;
                $_result['redirect_url'] = $redirectURL;

                $status = $data->data->order->status;
                $_invoiceData['order_id']=$orderId;
                $_invoiceData['invoice']=$invoice_id;
                $_invoiceData['status']=$status;
                $_invoiceData['invoice_response']=$response;
                $this->helperData->saveInvoiceDetails($_invoiceData);

                $paymentInfo = $payment->getAdditionalInformation();
                $paymentInfo['request'] = $response;
                $payment->setTransactionId($invoice_id);
                $payment->setAdditionalInformation($paymentInfo)->save();



            } elseif (isset($data->error)) {
                //print_r($data->error);
                $_result['error'] = $data->error->cause;
                $_result['description'] = $data->error->explanation;
            }
        }

        return $_result;

    }


    public function getGetwayReturnUrl()
    {
        return $this->urlHelper->getUrl(self::RESPONSE_URL);
    }

    public function getIPNUrl()
    {
        return $this->urlHelper->getUrl(self::IPN_RESPONSE);
    }

    public function getGetwayCancelUrl()
    {
        return $this->urlHelper->getUrl(self::RESPONSE_URL);
    }

    public function getGetwayFailureUrl()
    {
        return $this->urlHelper->getUrl(self::RESPONSE_URL);
    }


    protected function parseRequest($requestarray)
    {
        $request = "";
        if (empty($requestarray))
            return "";

        foreach ($requestarray as $fieldName => $fieldValue) {
            if ($request)
                $request .= "&";
            $request .= $fieldName . "=" . urlencode($fieldValue);
        }
        return $request;
    }

    public function invoiceRequestUrl($config)
    {
        $gatewayUrl = $this->getGatewayUrl($config);
        return $gatewayUrl;
    }


    public function getGatewayUrl($config)
    {

        if ($config['test']) {
            return $this->sandbox_endpointUrl;
        } else {
            return $this->live_endpointUrl;
        }
    }


    /**
     * Get full customer name
     *
     * @param AddressAdapterInterface $address
     * @return string
     */
    private function getName(AddressAdapterInterface $address)
    {
        $name = '';
        if ($address->getPrefix()) {
            $name .= $address->getPrefix() . ' ';
        }
        $name .= $address->getFirstname();
        if ($address->getMiddlename()) {
            $name .= ' ' . $address->getMiddlename();
        }
        $name .= ' ' . $address->getLastname();
        if ($address->getSuffix()) {
            $name .= ' ' . $address->getSuffix();
        }
        return $name;
    }

    public function getInvoiceIdFormGetway($requestBody, $requestUrl, $config)
    {
       return $response = $this->helperData->sendTransaction($requestUrl, 'POST', $requestBody, $config);
    }

   /* protected function sendTransaction($url, $method = 'POST', $body, $config)
    {
        $authorization = $config['authorization'];

        $headers = array(
            "Authorization:" . $authorization,
            "Content-Type: application/json"
        );
        //print_r($body);

        $curl = curl_init($url);
        // curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);

        $info = curl_getinfo($curl);
        $response = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        curl_close($curl);
        return $result;

        /*print_r($response);
        if (200 === $response || 201==$response) {

                return $result;
        } else {
            throw new CommandException('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            //die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            //$errors = curl_error($curl);
            return false;
        }
    }*/




}
