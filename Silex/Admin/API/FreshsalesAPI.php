<?php

namespace Admin\Service\API;

class FreshsalesAPI
{
	/** @var  \Configuration */
	protected $config;

	/**
	 * CustomerService constructor.
	 * @param \Configuration $config
	 */
	public function __construct(\Configuration $config) {
		$this->config = $config;
	}

    /**
     * Creates account at Freshsales.
     *
     * @param array $data
     * @return mixed
     */
    public function createAccount($data) {
	    $endpoint = '/api/sales_accounts';

        $parameters = array();
        $parameters['sales_account'] = array(
            'name' => $data['COMPANY'],
        );
        $parameters = $this->applyCustomFields('sales_account', $parameters, $data);

	    return $this->callFreshsales($parameters, $endpoint);
    }

    /**
     * Updates an account.
     *
     * @param array $data
     * @param int $accountId
     * @return bool|mixed
     */
    public function updateAccount($data, $accountId) {
        if (!is_numeric($accountId)) {
            return false;
        }

        $endpoint = '/api/sales_accounts/'.$accountId;

        $parameters = array();
        $parameters = $this->applyCustomFields('sales_account', $parameters, $data);

        return $this->callFreshsales($parameters, $endpoint, 'PUT');
    }

    /**
     * Creates contact at Freshsales
     * @param array $data
     * @param null|int $accountID If passed will assign contact to the account.
     * @param bool $withAccount If true will create a new account also.
     * @return mixed
     */
    public function createContact($data, $accountID = NULL, $withAccount = false) {
        $endpoint = '/api/contacts';

        $parameters = array();
        $parameters['contact'] = array(
            'last_name' => $data['CONTACT_NAME'],
            'mobile_number' => $data['CONTACT_PHONE'],
            'email' => $data['CONTACT_EMAIL'],
            'medium' => 'Website', //TODO: recheck this value
        );
        $parameters = $this->applyCustomFields('contact', $parameters, $data);

        if ($accountID !== NULL) {
            $parameters['contact']['sales_account_id'] = $accountID;
        }

        if ($withAccount) {
            $parameters['contact']['sales_account'] = array(
                'name' => $data['COMPANY'],
            );
        }

        $parameters['contact'] = array_merge($parameters['contact'], $data['contact']);

        return $this->callFreshsales($parameters, $endpoint);
    }

    /**
     * Updates a contact.
     *
     * @param array $data
     * @param int $contactId
     * @return bool|mixed
     */
    public function updateContact($data, $contactId) {
	    if (!is_numeric($contactId)) {
	        return false;
        }

        $endpoint = '/api/contacts/'.$contactId;

        $parameters = array();
	    $parameters = $this->applyCustomFields('contact', $parameters, $data);

        return $this->callFreshsales($parameters, $endpoint, 'PUT');
    }

    /**
     * Creates a deal at Freshsales.
     *
     * @param array $data
     * @param int $accountID Account that this deal belongs to.
     * @param int $contactId Contact to be associated with deal.
     * @return mixed
     */
    public function createDeal($data, $accountID, $contactId = NULL) {
        $endpoint = '/api/deals';

        $parameters = array();
        $parameters['deal'] = array(
            'name' => $this->config->getConfig('FreshsalesDealName'),
            'amount' => $this->config->getConfig('FreshsalesDealValue'),
            'sales_account_id' => $accountID,
        );
        $parameters = $this->applyCustomFields('deal', $parameters, $data);

        if ($contactId !== NULL) {
            $parameters['deal']['contacts_added_list'] = array($contactId);
        }

        $parameters['deal'] = array_merge($parameters['deal'], $data['deal']);

        return $this->callFreshsales($parameters, $endpoint);
    }

    /**
     * Get a list of available owners.
     *
     * @return mixed
     */
    public function getOwners() {
        $endpoint = '/api/selector/owners';
        $parameters = array();

        return $this->callFreshsales($parameters, $endpoint, 'GET');
    }

    /**
     * Get a list of available deal products.
     *
     * @return mixed
     */
    public function getDealProducts() {
        $endpoint = '/api/selector/deal_products';
        $parameters = array();

        return $this->callFreshsales($parameters, $endpoint, 'GET');
    }

    /**
     * Get a list of available lead sources.
     *
     * @return mixed
     */
    public function getLeadSources() {
        $endpoint = '/api/selector/lead_sources';
        $parameters = array();

        return $this->callFreshsales($parameters, $endpoint, 'GET');
    }

    /**
     * Get a list of available deal stages.
     *
     * @return mixed
     */
    public function getDealStages() {
        $endpoint = '/api/selector/deal_stages';
        $parameters = array();

        return $this->callFreshsales($parameters, $endpoint, 'GET');
    }

    /**
     * Applies custom fields to Freshsales parameters array.
     *
     * @param string $type A type of Freshsales data.
     * @param array $parameters
     * @param array $data
     * @return mixed
     */
    private function applyCustomFields($type, $parameters, $data) {
	    if (!empty($data[$type]['custom_field'])) {
            foreach ($data[$type]['custom_field'] as $key => $value) {
                $parameters[$type]['custom_field'][$key] = $value;
            }
        }
        return $parameters;
    }

    /**
     * Makes a call to Freshsales API.
     *
     * @param array $parameters
     * @param string $endpoint
     * @param string $request_type possible values: 'POST', 'GET', 'PUT'.
     * @return mixed
     */
    private function callFreshsales($parameters, $endpoint, $request_type = 'POST') {
        $endpoint = 'https://'.$this->config->getConfig('FreshsalesDomain').$endpoint;
        $header = array(
            'Content-Type: application/json',
            'Authorization: Token token='.$this->config->getConfig('FreshsalesAPIKey'),
        );

        $ch = @curl_init();
        if ($request_type === 'POST') {
            $post_json = json_encode($parameters);
            @curl_setopt($ch, CURLOPT_POST, true);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        } elseif ($request_type === 'PUT') {
            $post_json = json_encode($parameters);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        }
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = @curl_exec($ch);
        @curl_close($ch);

        return $response;
	}
}
