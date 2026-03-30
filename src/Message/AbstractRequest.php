<?php

namespace Omnipay\InovioPay\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Class AbstractRequest
 *
 * This is the parent class of all InovioPay requests
 *
 * @date      3/5/18
 * @author    markbonnievestil
 */

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /**
     * Endpoint URL
     *
     * @var string URL
     */
    protected $endpoint = 'https://api.inoviopay.com/payment/pmt_service.cfm';

    /**
     * @var float
     */
    protected $apiVersion = 4.0;

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return AbstractRequest
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return float
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param float $apiVersion
     * @return AbstractRequest
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getReqUsername()
    {
        return $this->getParameter('reqUsername');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReqUsername($value)
    {
        return $this->setParameter('reqUsername', $value);
    }

    /**
     * @return string
     */
    public function getReqPassword()
    {
        return $this->getParameter('reqPassword');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReqPassword($value)
    {
        return $this->setParameter('reqPassword', $value);
    }

    /**
     * @return string
     */
    public function getSiteId()
    {
        return $this->getParameter('siteId');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSiteId($value)
    {
        return $this->setParameter('siteId', $value);
    }

    /**
     * @return string
     */
    public function getMerchAcctId()
    {
        return $this->getParameter('merchAcctId');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setMerchAcctId($value)
    {
        return $this->setParameter('merchAcctId', $value);
    }

    /**
     * @return string
     */
    public function getRequestResponseFormat()
    {
        return $this->getParameter('requestResponseFormat');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setRequestResponseFormat($value)
    {
        return $this->setParameter('requestResponseFormat', $value);
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->getParameter('productId');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setProductId($value)
    {
        return $this->setParameter('productId', $value);
    }

    /**
     * @return mixed
     */
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    /**
     * @return mixed
     */
    public function getTransactorId()
    {
        return $this->getParameter('transactorId');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setTransactorId($value)
    {
        return $this->setParameter('transactorId', $value);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = array(
            'Content-Type'   => 'application/x-www-form-urlencoded',
            'Accept-Charset' => 'iso-8859-1,*,utf-8',
        );

        return $headers;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            $this->getHeaders(),
            http_build_query($data)
        );

        $body = (string) $httpResponse->getBody();

        try {
            $responseData = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidResponseException(
                'Invalid JSON response received from InovioPay.',
                0,
                $exception
            );
        }

        return $this->response = new Response($this, $responseData, $httpResponse->getStatusCode());
    }

    /**
     * Set the common data used in every request.
     *
     * In this gateway a certain amount of data needs to be sent
     * in every request.  This function sets those data into the
     * array and can be extended by child classes.
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('reqUsername', 'reqPassword', 'siteId', 'merchAcctId');

        $data = array(
            'req_username'            => $this->getReqUsername(),
            'req_password'            => $this->getReqPassword(),
            'site_id'                 => $this->getSiteId(),
            'merch_acct_id'           => $this->getMerchAcctId(),
            'request_response_format' => $this->getRequestResponseFormat() ? $this->getRequestResponseFormat() : 'json',
            'request_api_version'     => $this->getApiVersion(),
        );

        return $data;
    }
}
