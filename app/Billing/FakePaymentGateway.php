<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway {

	private $charges;


	public function __construct(){
		$this->charges = collect();
	}


	public function getValidTestToken(){
		return 'valid-token';
	}

	public function charge($amount, $token){

		if ($token !== $this->getValidTestToken()) {
			throw new PaymentFailException;
			
		}
		$this->charges[] = $amount;
	}

	public function totalCharges(){
		// print_r($this->charges);
		return $this->charges->sum();
		
	}


}