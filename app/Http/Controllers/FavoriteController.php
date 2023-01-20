<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function add($id){

        $user = Auth::user();
        $isFavorite = $user->favorite_posts->where('pivot.post_id',$id)->count();

        if($isFavorite  == 0){

            $user->favorite_posts()->attach($id);
            Toastr::success('Post Successfully added to your favorite list :)','Success');
            return redirect()->back();
        }else{
            $user->favorite_posts()->detach($id);
            Toastr::success('Post successfully removed from your favorite List :)','Success');
            return redirect()->back();
        }

    }
}
