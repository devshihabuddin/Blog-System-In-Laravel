<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Notifications\AuthorPostApproved;
use App\Notifications\NewPostNotify;
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
        $posts  = Post::latest()->get();
        return view('admin.post.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags   = Tag::all();
        return view('admin.post.create',compact('categories','tags'));
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
        $post->is_approved = true;
        $post->save();

        $post->categories()->attach($request->categories);
        $post->tags()->attach($request->tags);
        //notify subscriber
        $subscribers    = Subscriber::all();
        foreach($subscribers as $subscriber){
            Notification::route('mail',$subscriber->email)->notify(new NewPostNotify($post));
        }

        Toastr::success('Post Successfully Saved','Success');
        return redirect()->route('admin.post.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.post.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {

        $categories = Category::all();
        $tags       = Tag::all();
        return view('admin.post.edit',compact('post','categories','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
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
        $post->is_approved = true;
        $post->save();

        $post->categories()->sync($request->categories);
        $post->tags()->sync($request->tags);

        Toastr::success('Post Successfully Updated','Success');
        return redirect()->route('admin.post.index');
    }

    //Just divided for pending post
    public function pending(){
        $posts  = Post::where('is_approved',false)->get();
        return view('admin.post.pending',compact('posts'));
    }
    public function approved($id){
        $post   = Post::find($id);
        if($post->is_approved == false){
            $post->is_approved  = true;
            $post->save();
            //notify to user
            $post->user->notify(new AuthorPostApproved($post));
            //notify to subscriber
            $subscribers    = Subscriber::all();
            foreach($subscribers as $subscriber){
                Notification::route('mail',$subscriber->email)->notify(new NewPostNotify($post));
            }
            Toastr::success('Post Successfully Approved :)','Success');

        }else{

            Toastr::info('This Post is alredy Approved','Info');
        }
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
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
