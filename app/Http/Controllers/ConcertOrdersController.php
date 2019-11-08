<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Billing\PaymentGateway;
use App\Billing\PaymentFailException;
use App\Concert;
use App\Exceptions\NotEnoughtTicketsException;

class ConcertOrdersController extends Controller
{

	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway){
		$this->paymentGateway = $paymentGateway ;
	} 

    public function store($concertId){


        $this->validate(request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token' => ['required'],
        ]);



        try {

            $concert = Concert::published()->findOrFail($concertId);

            $order = $concert->orderTickets(request('email'),request('ticket_quantity'));

            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price , request('payment_token'));

            

            return response()->json([],201);
            
        } catch (PaymentFailException $e) {

            $order->cancel();
            return response()->json([],422);
        } catch (NotEnoughtTicketsException $e) {
            return response()->json([],422);
        }
    	
    }
}
