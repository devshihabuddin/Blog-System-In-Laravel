<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(){
        $authors = User::authors()->withCount('posts')->withCount('favorite_posts')->withCount('comments')->get();
        return view('admin.author',compact('authors'));
    }
    public function destroy($id){
        User::findOrFail($id)->delete();
        Toastr::success('An Author Successfully Deleted','Success');
        return redirect()->back();
    }
}
