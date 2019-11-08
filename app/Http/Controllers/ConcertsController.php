<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Concert;

class ConcertsController extends Controller
{
    public function show($id){

    	$concert = Concert::published()->findOrFail($id);
    	return view('concerts.show', ["concert" => $concert]);
    }

    public function show2(){

    	echo "fff";
    }
}
