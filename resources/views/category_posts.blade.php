@extends('layouts.frontend.app')

@section('title','Category_Posts')

@push('css')
    <link href="{{ asset('assets/frontend/css/category/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/frontend/css/category/responsive.css') }}" rel="stylesheet">
    <style>
        .slider{
            height: 400px;
            width: 100%;
            background-image: url({{ asset('storage/category/'.$category->image)}});
            background-size: cover;
        }
        .favorite_posts{
            color: blue;
        }
    </style>
@endpush

@section('content')
    <div class="slider display-table center-text">

    </div><!-- slider -->


         <h4 class="center-text"><b>{{ $category->name }}</b></h4>



    <section class="blog-area section">
        <div class="container">

            <div class="row">
                @if ($posts->count() > 0)
                    @forelse($posts as $post)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100">
                                <div class="single-post post-style-1">

                                    <div class="blog-image"><img src="{{asset('storage/post/'.$post->image)}}" alt="{{ $post->title }}"></div>

                                    <a class="avatar" href="{{route('author.profile',$post->user->username)}}"><img src="{{ Storage::disk('public')->url('profile/'.$post->user->image) }}" alt="Profile Image"></a>

                                    <div class="blog-info">

                                        <h4 class="title"><a href="{{route('post.details',$post->slug)}}"><b>{{ $post->title }}</b></a></h4>

                                        <ul class="post-footer">

                                            <li>
                                                @guest
                                                <a href="#" onclick="toastr.info('To add favorite list. You need to login first.','Info')">
                                                    <i class="ion-heart"></i>
                                                    {{$post->favorite_to_users->count()}}
                                                </a>

                                                @else
                                                <a href="#" onclick="document.getElementById('favorite-form-{{ $post->id }}').submit();"
                                                    {{-- for color heart icon --}}
                                                    class="{{ !Auth::user()->favorite_posts->where('pivot.post_id',$post->id)->count() == 0 ? 'favorite_posts' : ''}}">
                                                    <i class="ion-heart"></i>
                                                    {{$post->favorite_to_users->count()}}
                                                </a>
                                                <form id="favorite-form-{{ $post->id }}" action="{{route('post.favorite',$post->id)}}" method="POST" style="display: none">
                                                    @csrf
                                                </form>

                                                @endguest
                                            </li>
                                            <li><a href="#"><i class="ion-chatbubble"></i>{{ $post->comments->count() }}</a></li>
                                            <li><a href="#"><i class="ion-eye"></i>{{ $post->view_count }}</a></li>

                                        </ul>

                                    </div><!-- blog-info -->
                                </div><!-- single-post -->
                            </div><!-- card -->
                        </div><!-- col-lg-4 col-md-6 -->

                    @endforeach

                @else
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100">
                        <div class="single-post post-style-1">
                            <div class="blog-info">
                                <h4 class="title"><b>No Post On This Category</b></h4>
                            </div><!-- blog-info -->
                        </div><!-- single-post -->
                    </div><!-- card -->
                </div><!-- col-lg-4 col-md-6 -->

                @endif

            </div><!-- row -->



        </div><!-- container -->
    </section><!-- section -->

@endsection

@push('js')

@endpush
