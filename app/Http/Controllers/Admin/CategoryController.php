<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.category.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
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

            'name'  => 'required|unique:categories',
            'image' => 'required|mimes:png,jpg,jpeg,web'
        ]);
        $image  = $request->file('image');
        $slug   = Str::slug($request->name);
        if(isset($image)){
            //make uniqe name for image
            $currentdatetime    = Carbon::now()->toDateString();
            $imagename          = $slug.'-'.$currentdatetime.'-'.uniqid().'.'.$image->getClientOriginalExtension();

            //check category directory is exists
            if(!Storage::disk('public')->exists('category')){

                Storage::disk('public')->makeDirectory('category');
            }

            //resize image for category and upload
            $category   = Image::make($image)->resize(1600,479)->stream();
            Storage::disk('public')->put('category/'.$imagename,$category);

             //check category slider directory is exists
             if(!Storage::disk('public')->exists('category/slider')){

                Storage::disk('public')->makeDirectory('category/slider');
            }
            //resize image for category and upload
            $slider   = Image::make($image)->resize(500,333)->stream();
            Storage::disk('public')->put('category/slider/'.$imagename,$slider);
        }else{
            $imagename  = 'default.png';
        }
        $category           = new Category();
        $category->name     = $request->name;
        $category->slug     = $slug;
        $category->image    = $imagename;
        $category->save();
        Toastr::success('Category Successfully Save :)','Success');
        return redirect()->route('admin.category.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category   = Category::findOrfail($id);
        return view('admin.category.edit',compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'name'  => 'required',
            'image' => 'mimes:png,jpg,jpeg,web'
        ]);
        //get image from form
        $image      = $request->file('image');
        $slug       = Str::slug($request->name);

        $category   = Category::findOrFail($id);
        if(isset($image)){
            $currentdate    = Carbon::now()->toDateString();
            $imagename      = $slug.'-'.$currentdate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            //check category directory is exists
            if(!Storage::disk('public')->exists('category')){
                Storage::disk('public')->makeDirectory('category');
            }
            //delete old image
            if(Storage::disk('public')->exists('category/'.$category->image)){
                 Storage::disk('public')->delete('category/'.$category->image);
            }
            //catagory image resize
            $categoryimage   = Image::make($image)->resize(1600,479)->stream();
            Storage::disk('public')->put('category/'.$imagename,$categoryimage);
            //check category directory is exists
            if(!Storage::disk('public')->exists('category/slider')){
                Storage::disk('public')->makeDirectory('category/slider/');
            }
            //delete old slider image
            if(Storage::disk('public')->exists('category/slider/'.$category->image)){
                Storage::disk('public')->delete('category/slider/'.$category->image);
           }
             //catagory slider image resize
             $slider   = Image::make($image)->resize(500,333)->stream();
             Storage::disk('public')->put('category/slider/'.$imagename,$slider);

        }else{
            $imagename  = $category->image;

        }

        $category->name     = $request->name;
        $category->slug     = $slug;
        $category->image    = $imagename;
        $category->save();
        Toastr::success('Category Successfully Updated','Success');
        return redirect()->route('admin.category.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category   = Category::findOrfail($id);
        if(Storage::disk('public')->exists('category/'.$category->image)){
            Storage::disk('public')->delete('category/'.$category->image);
        }
        if(Storage::disk('public')->exists('category/slider/'.$category->image)){
            Storage::disk('public')->delete('category/slider/'.$category->image);
        }
        $category->delete();
        Toastr::success('Category Successfully Deleted','Success');
        return redirect()->back();
    }
}
