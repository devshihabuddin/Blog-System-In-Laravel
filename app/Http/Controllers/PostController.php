<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PostController extends Controller
{
    public function index(){
        $posts = Post::latest()->approved()->published()->paginate(6);
        return view('posts',compact('posts'));
    }
   public function postDetails($slug){

    $post        = Post::where('slug',$slug)->approved()->published()->first();
    $randomposts = Post::approved()->published()->take(3)->inRandomOrder()->get();
    $blogKey     = 'blog_'.$post->id;
    if(!session::has($blogKey)){
        $post->increment('view_count');
        session::put($blogKey,1);
    }
    return view('post',compact('post','randomposts'));
   }

   public function postByCategory($slug){
        $category = Category::where('slug',$slug)->first();
        $posts    = $category->posts()->approved()->published()->get();
        return view('category_posts',compact('category','posts'));
   }

   public function postByTag($slug){
       $tag   = Tag::where('slug',$slug)->first();
       $posts = $tag->posts()->approved()->published()->get();
       return view('tag_posts',compact('tag','posts'));
   }
}
