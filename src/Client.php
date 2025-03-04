<?php
/**
 * Created by PhpStorm.
 * User: Johan
 * Date: 2016-01-17
 * Time: 20:06
 */

namespace HelmutSchneider\Swish;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 * @package HelmutSchneider\Swish
 */
class Client
{
    const SWISH_PRODUCTION_URL = 'https://cpc.getswish.net/swish-cpcapi/api/v1';
    const SWISH_TEST_URL = 'https://mss.cpc.getswish.net/swish-cpcapi/api/v1';
    const CONTENT_TYPE_JSON = 'application/json';

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Client constructor.
     * @param ClientInterface $client
     * @param string $baseUrl
     */
    function __construct(ClientInterface $client, string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $method HTTP-method
     * @param string $endpoint
     * @param array $options guzzle options
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ValidationException
     */
    protected function sendRequest(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $this->baseUrl . $endpoint, array_merge([
                'headers' => [
                    'Content-Type' => self::CONTENT_TYPE_JSON,
                    'Accept' => self::CONTENT_TYPE_JSON,
                ],
            ], $options));
        }
        catch (ClientException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case 403:
                case 422:
                    throw new ValidationException($e->getResponse());
            }
            throw $e;
        }
    }

    /**
     * @param string[] $body
     * @return string[]
     */
    protected function filterRequestBody(array $body): array
    {
        $filtered = $body;
        foreach ($filtered as $key => $value) {
            if (empty($filtered[$key])) {
                unset($filtered[$key]);
            }
        }
        return $filtered;
    }

    /**
     * @param PaymentRequest $request
     * @return CreatePaymentRequestResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function createPaymentRequest(PaymentRequest $request): CreatePaymentRequestResponse
    {
        $response = $this->sendRequest('POST', '/paymentrequests', [
            'json' => $this->filterRequestBody((array) $request),
        ]);

        return new CreatePaymentRequestResponse(
            Util::getObjectIdFromResponse($response),
            $response->getHeaderLine('PaymentRequestToken'),
        );
    }

    /**
     * @param string $id Payment request id
     * @return PaymentRequest
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function getPaymentRequest(string $id): PaymentRequest
    {
        $response = $this->sendRequest('GET', '/paymentrequests/' . $id);

        return new PaymentRequest(
            json_decode((string) $response->getBody(), true)
        );
    }

    /**
     * @param Refund $refund
     * @return string refund id
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function createRefund(Refund $refund): string
    {
        $response = $this->sendRequest('POST', '/refunds', [
            'json' => $this->filterRequestBody((array) $refund),
        ]);

        return Util::getObjectIdFromResponse($response);
    }
	
	/**
     * @param string $id Refund id
     * @return Refund
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function getRefund(string $id): Refund
    {
        $response = $this->sendRequest('GET', '/refunds/' . $id);

        return new Refund(
            json_decode((string) $response->getBody(), true)
        );
    }
	
	/**
     * @param Payout $refund
     * @return string payout id
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function createPayout(Payout $payout): string
    {
        $response = $this->sendRequest('POST', '/payouts', [
            'json' => $this->filterRequestBody((array) $payout),
        ]);

        return Util::getObjectIdFromResponse($response);
    }
	
	/**
     * @param string $id Payout id
     * @return Payout
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ValidationException
     */
    public function getPayout(string $id): Payout
    {
        $response = $this->sendRequest('GET', '/payouts/' . $id);

        return new Payout(
            json_decode((string) $response->getBody(), true)
        );
    }

    /**
     * @param string|bool $rootCert path to the swish CA root cert chain, or boolean true to use the
     *                                    operating system CA bundle.
     *                                    forwarded to guzzle's "verify" option.
     * @param string|string[] $clientCert path to a .pem-bundle containing the client side cert
     *                                    and it's corresponding private key. If the private key is
     *                                    password protected, pass an array ['PATH', 'PASSWORD'].
     *                                    forwarded to guzzle's "cert" option.
     * @param string $baseUrl url to the swish api
     * @param ?object $handler guzzle http handler
     * @return Client
     */
    public static function make(
        $rootCert,
        $clientCert,
        string $baseUrl = self::SWISH_PRODUCTION_URL,
        ?object $handler = null
    ): Client {
        $config = [
            'verify' => $rootCert,
            'cert' => $clientCert,
            'handler' => HandlerStack::create(new CurlHandler()),
        ];
        if ($handler) {
            $config['handler'] = $handler;
        }
        $guzzle = new \GuzzleHttp\Client($config);
        return new Client($guzzle, $baseUrl);
    }

}
