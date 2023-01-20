<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\NewAuthorPost;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts  = Auth::user()->posts()->latest()->get();
        return view('author.post.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags       = Tag::all();
        return view('author.post.create',compact('categories','tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'         => 'required',
            'image'         => 'required|mimes:jpg,jpeg,png,web',
            'categories'    => 'required',
            'tags'          => 'required',
            'body'          => 'required'
        ]);
        $image  = $request->file('image');
        $slug   = Str::slug($request->title);
        if(isset($image)){
            //make uniqe name for image
            $currentdate    = Carbon::now()->toDateString();
            $imagename  = $slug.'-'.$currentdate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            if(!Storage::disk('public')->exists('post')){
                Storage::disk('public')->makeDirectory('post');
            }
            $postImage  = Image::make($image)->resize(1600,1066)->stream();
            Storage::disk('public')->put('post/'.$imagename,$postImage);

        }else{
            $imagename  = 'default.png';
        }

        $post           = new Post();
        $post->user_id  = Auth::id();
        $post->title    = $request->title;
        $post->image    = $imagename;
        $post->slug     = $slug;
        $post->body     = $request->body;
        if(isset($request->status)){
            $post->status  = true;
        }else{
            $post->status  = false;
        }
        $post->is_approved = false;
        $post->save();

        $post->categories()->attach($request->categories);
        $post->tags()->attach($request->tags);

        $user   = User::where('role_id','1')->get();
        Notification::send($user, new NewAuthorPost($post));

        Toastr::success('Post Successfully Saved','Success');
        return redirect()->route('author.post.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(post $post)
    {
        //for user authontiction
        if($post->user_id != Auth::id()){

            Toastr::error('You are not authorized this post','Error');
            return redirect()->back();

        }
        return view('author.post.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(post $post)
    {
        //for user authontiction
        if($post->user_id != Auth::id()){

            Toastr::error('You are not authorized this post','Error');
            return redirect()->back();

        }
        $categories = Category::all();
        $tags       = Tag::all();
        return view('author.post.edit',compact('post','categories','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, post $post)
    {
        //for user authontiction
        if($post->user_id != Auth::id()){

            Toastr::error('You are not authorized this post','Error');
            return redirect()->back();

        }
        $this->validate($request,[
            'title'         => 'required',
            'image'         => 'mimes:jpg,jpeg,png,web',
            'categories'    => 'required',
            'tags'          => 'required',
            'body'          => 'required'
        ]);
        $image  = $request->file('image');
        $slug   = Str::slug($request->title);
        if(isset($image)){
            //make uniqe name for image
            $currentdate    = Carbon::now()->toDateString();
            $imagename      = $slug.'-'.$currentdate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            if(!Storage::disk('public')->exists('post')){
                Storage::disk('public')->makeDirectory('post');
            }
            //delete old image
            if(Storage::disk('public')->exists('post/'.$post->image)){
                Storage::disk('public')->delete('post/'.$post->image);
            }
            $postImage  = Image::make($image)->resize(1600,1066)->stream();
            Storage::disk('public')->put('post/'.$imagename,$postImage);

        }else{
            $imagename  = $post->image;
        }


        $post->user_id  = Auth::id();
        $post->title    = $request->title;
        $post->image    = $imagename;
        $post->slug     = $slug;
        $post->body     = $request->body;
        if(isset($request->status)){
            $post->status  = true;
        }else{
            $post->status  = false;
        }
        $post->is_approved = false;
        $post->save();

        $post->categories()->sync($request->categories);
        $post->tags()->sync($request->tags);

        Toastr::success('Post Successfully Updated','Success');
        return redirect()->route('author.post.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(post $post)
    {
        //for user authontiction
        if($post->user_id != Auth::id()){

            Toastr::error('You are not authorized this post','Error');
            return redirect()->back();

        }

        if(Storage::disk('public')->exists('post/'.$post->image)){

            Storage::disk('public')->delete('post/'.$post->image);
        }
        $post->categories()->detach();
        $post->tags()->detach();
        $post->delete();
        Toastr::success('Post Deleted Successfully','Success');
        return redirect()->back();
    }
}
