<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailException;

class FakePaymentGatewayTest extends TestCase
{
	public function test_charges_with_a_valid_payment_token_are_successful(){
		$paymentGateway = new FakePaymentGateway;

		$paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

		$this->assertEquals(2500, $paymentGateway->totalCharges());
	}


	public function test_charges_with_an_invalid_payment_token_fail(){

		try {

			$paymentGateway = new FakePaymentGateway;
			$paymentGateway->charge(2500, 'invalid-payment-token');

		} catch (PaymentFailException $e) {
			return ;
			
		}

		$this->fail();


	}

	
}