<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(){
        $subscribers    = Subscriber::latest()->get();
        return view('admin.subscriber',compact('subscribers'));
    }
    public function destroy($id){

        Subscriber::find($id)->delete();
        Toastr::success('Subscriber successfully Deleted','Success');
        return redirect()->back();

    }
}
