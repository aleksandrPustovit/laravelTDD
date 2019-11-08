<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Exceptions\NotEnoughtTicketsException;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedDateAttribute(){
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(){
    	return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute(){
    	return number_format($this->ticket_price/100,2);
    }

    public function scopePublished($query){
    	return $query->whereNotNull('published_at');
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function tickets () {
        // var_dump(Ticket::class);
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $amounth) {

        

        $tickets = $this->tickets()->available()->take($amounth)->get();

            // dd($tickets);

        if($tickets->count() < $amounth){
            throw new NotEnoughtTicketsException;
        }

        $order = $this->orders()->create(['email' => $email]);


        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }
        return $order;

    }

    public function addTickets ($quantity) {

        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

    }

    public function ticketRemaining () {

        return $this->tickets()->available()->count();
    }
}
