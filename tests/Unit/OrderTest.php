<?php


namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use App\Exceptions\NotEnoughtTicketsException;




class OrderTest extends TestCase {

	use DatabaseMigrations;



	public function test_tickets_are_released_when_order_is_cancelled () {
	
		$concert = factory(Concert::class)->create();

		$concert->addTickets(10);

		$order = $concert->orderTickets('janne@example.com', 5);

		$this->assertEquals(5, $concert->ticketRemaining());

		$order->cancel();

		$this->assertEquals(10, $concert->ticketRemaining());
		$this->assertNull(Order::find($order->id));
	
	}

}