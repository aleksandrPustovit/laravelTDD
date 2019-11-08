<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use App\Exceptions\NotEnoughtTicketsException;

class ConsertTest extends TestCase
{

    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function test_can_get_formatted_date(){

        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals("December 1, 2016",  $concert->formatted_date);

    }

    public function test_can_get_formatted_start_time(){

        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('17:00:00'),
        ]);

        $this->assertEquals("5:00pm",  $concert->formatted_start_time);

    }

    public function test_can_get_ticket_price_in_dollars(){
        $concert = factory(Concert::class)->make([
            'ticket_price' => '6750',
        ]);

        $this->assertEquals("67.50",  $concert->ticket_price_in_dollars);
    }


    public function test_concerts_with_a_published_at_date_are_published(){
        $publishedConcertA = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);

        $publishedConcertB = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);

        $publishedConcertC = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($publishedConcertC));
    }

    public function test_can_order_concert_tickets(){

         $concert = factory(Concert::class)->create();
         $concert->addTickets(3);

         $order = $concert->orderTickets('janne@example.com', 3);

         $this->assertEquals('janne@example.com', $order->email);
         $this->assertEquals(3, $order->tickets()->count());
    }

    public function test_can_add_tickets (){

        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketRemaining());

    }

    public function test_tickets_remaining_does_not_include_tickets_associated_with_an_order(){
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $concert->orderTickets('janne@example.com', 30);
        $this->assertEquals(20, $concert->ticketRemaining());
    }

    public function test_trying_purchase_more_tickets_than_remain_throws_an_exception () {
    
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        try {
            $concert->orderTickets('janne@example.com', 11);

        } catch (NotEnoughtTicketsException $e) {

            $order = $concert->orders()->where('email', 'janne@example.com')->first();

            $this->assertNull($order);

            $this->assertEquals(10, $concert->ticketRemaining());
            return;
        }

        $this->fail("orders succeseed even not enoght ticket");
    
    }

    public function test_order_tickets_that_have_already_been_purchased () {
    
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        $concert->orderTickets('janne@example.com', 8);

        


        try {
            $concert->orderTickets('alexi@example.com', 3);

        } catch (NotEnoughtTicketsException $e) {

            $alexiOrder = $concert->orders()->where('email', 'alexi@example.com')->first();

            $this->assertNull($alexiOrder);

            $this->assertEquals(2, $concert->ticketRemaining());
            return;
        }

        $this->fail("orders succeseed even not enoght ticket");
    
    }




        

}
