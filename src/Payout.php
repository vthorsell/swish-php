<?php

namespace HelmutSchneider\Swish;

/**
 * Class PaymentRequest
 * @package HelmutSchneider\Swish
 */
class Payout
{

	/**
     * @var string
     */
    public $id = '';
	
    /**
     * @var string
     */
    public $payoutInstructionUUID = '';

    /**
     * @var string
     */
    public $payerPaymentReference = '';

    /**
     * @var string
     */
    public $callbackUrl = '';

    /**
     * @var string
     */
    public $payerAlias = '';

    /**
     * @var string
     */
    public $payeeAlias = '';
	
	/**
	 * @var string
	 */
	public $payeeSSN = '';

    /**
     * @var string
     */
    public $amount = '';

    /**
     * @var string
     */
    public $currency = 'SEK';
	
	/**
	 * @var string
	 */
	public $payoutType = 'PAYOUT';
	
    /**
     * @var string
     */
    public $message = '';

    /**
     * @var string
     */
    public $status = '';

    /**
     * @var string
     */
    public $dateCreated = '';

    /**
     * @var string
     */
    public $datePaid = '';

    /**
     * @var string
     */
    public $errorCode = '';

    /**
     * @var string
     */
    public $errorMessage = '';

    /**
     * @var string
     */
    public $additionalInformation = '';

    /**
     * PaymentRequest constructor.
     * @param string[] $data
     */
    function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

}
