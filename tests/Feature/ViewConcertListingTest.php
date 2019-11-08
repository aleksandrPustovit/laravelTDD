<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Concert;
use Carbon\Carbon;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testUser_can_view_a_published_concert_list(){
        $concert = factory(Concert::class)->states('published')->create([            'title' => 'The Red Chord',
            'subtitle' => 'with someone',
            'date' => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example',
            'city' => 'laravel',
            'state' => 'ON',
            'zip' => '90210',
            'additional_information' => 'Addition info',
            'published_at' => Carbon::parse('-1 week'),

        ]);


        $response = $this->get('/concerts/'.$concert->id);

        echo "RRR = " . $concert->published_at;

        $response->assertSee('The Red Chord');
        $response->assertSee('with someone');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example');
        $response->assertSee('laravel');
        $response->assertSee('ON');
        $response->assertSee('90210');
        $response->assertSee('Addition info');


    }


     /*@test */

    public function test_user_cannot_view_unpublished_concert_listings(){


        $concert = factory(Concert::class)->state('unpublished')->create();

        $response = $this->get('/concerts/'.$concert->id);

        $response->assertStatus(404);

    }


}
