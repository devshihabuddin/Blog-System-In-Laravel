<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
     public function store(Request $request){

        $this->validate($request,[
            'email' => 'required|email|unique:subscribers'
        ]);
        $subscribe  = new Subscriber();
        $subscribe->email = $request->email;
        $subscribe->save();
        Toastr::success('Successfully Subscribe :)','Success');
        return redirect()->back();
     }
}
