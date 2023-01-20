<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(){
        $posts   = Auth::user()->posts;
        return view('author.comments',compact('posts'));
    }

    public function destroy($id){


        $Comment    = Comment::findOrFail($id);
        if($Comment->post->user->id == Auth::id()){
            $Comment->delete();
            Toastr::success('Comment Successfully Deleted','Success');
        }else{

            Toastr::error('You are not authorized to delete this comment','Error');
        }
        return redirect()->back();

    }
}
