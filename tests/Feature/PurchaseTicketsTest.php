<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;
use App\Concert;
use Carbon\Carbon;

use App\Exceptions\NotEnoughtTicketsException;

class PurchaseTicketsTests extends TestCase
{
	use DatabaseMigrations;


    protected function setUp() :void {

        parent::setUp();

        $this->paymentGeteway = new FakePaymentGateway;

        $this->app->instance(PaymentGateway::class,$this->paymentGeteway);
    }


    private function orderTicket($concert, $params){

        return $this->json('POST', "/concerts/{$concert->id}/orders",$params);

    }

    private function assertValidationError($field, $response){


        $response->assertStatus(422);
        $this->assertArrayHasKey($field,$response->decodeResponseJson()['errors'] );

    }

	public function test_customer_can_purchase_concert_tickets_to_a_piblished_concert(){
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => '3250',]);

        $concert->addTickets(3);

        $response = $this->orderTicket($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);

        // $response->assertSee('The Red Chord');
        $response->assertStatus(201);
        $this->assertEquals(9750, $this->paymentGeteway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
	}


    public function test_cannot_purchase_more_tickets_than_remain(){


        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create();

        $concert->addTickets(50);

        $response = $this->orderTicket($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);

        $response->assertStatus(422);

        $order = $concert->orders->where('email', 'john@example.com')->first();

        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGeteway->totalCharges());

        $this->assertEquals(50, $concert->ticketRemaining());

    }


    public function test_email_is_required_to_purshase_tickets(){


        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert,[
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);



        $this->assertValidationError('email', $response);


    }

    public function test_email_must_be_validate_to_purshase_tickets(){

        // $this->withoutExceptionHandling();


        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert,[
            'email' => 'not-e-mail',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);


        $this->assertValidationError('email', $response);


    }


    public function test_ticket_quantity_is_required_to_purshase_tickets(){

        // $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert,[
            'email' => 'aaa@ee.re',
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);


        $this->assertValidationError('ticket_quantity', $response);


    }


    public function test_ticket_quantity_must_be_at_least_one_to_purshase_tickets(){

        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert,[
            'email' => 'aaa@ee.re',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);


        $this->assertValidationError('ticket_quantity', $response);


    }


    public function test_payment_token_is_required_to_purshase_tickets(){

        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert,[
            'email' => 'aaa@ee.re',
            'ticket_quantity' => 0,
        ]);


        $this->assertValidationError('payment_token', $response);

    }


    public function test_cannot_purchase_tickets_to_an_unpublished_concert(){

        $concert = factory(Concert::class)->states('unpublished')->create();

        $concert->addTickets(3);

        $response = $this->orderTicket($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGeteway->getValidTestToken(),
        ]);

        $response->assertStatus(404);

        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGeteway->totalCharges());

    }

    public function test_an_order_is_not_created_if_payment_fails(){

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => '3250',]);

        $concert->addTickets(3);

        $response = $this->orderTicket($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $response->assertStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNull($order);



    }



	
}