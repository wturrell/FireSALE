<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * CI-Merchant Library
 *
 * Copyright (c) 2012 Crescendo Multimedia Ltd
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Merchant Stripe Class
 *
 * Payment processing using Stripe (https://stripe.com/)
 */

class Merchant_stripe extends Merchant_driver
{
	const API_ENDPOINT = 'https://api.stripe.com';

	public $required_fields = array('amount', 'token', 'currency_code', 'reference');

	public $settings = array(
		'api_key' => '',
	);

	public function _process($params)
	{
		$request = array(
			'amount' => (int)($params['amount'] * 100),
			'card' => $params['token'],
			'currency' => strtolower($params['currency_code']),
			'description' => $params['reference'],
		);

		$response = Merchant::curl_helper(self::API_ENDPOINT.'/v1/charges', $request, $this->settings['api_key']);
		if ( ! empty($response['error'])) return new Merchant_response('failed', $response['error']);

		$data = json_decode($response['data']);
		if (isset($data->error))
		{
			return new Merchant_response('declined', $data->error->message);
		}
		else
		{
			return new Merchant_response('authorized', '', $data->id, $data->amount / 100);
		}
	}
}
