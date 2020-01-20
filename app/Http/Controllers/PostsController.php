<?php
/**
 * Created by PhpStorm.
 * User: a.abd
 * Date: 1/17/2020
 * Time: 5:38 PM
 */

namespace App\Http\Controllers;


// use Illuminate\Support\Facades\DB;
use DB;
use App\Post;

class PostsController extends Controller
{

    public function show($slug){

//        $posts=[
//            'my-first-post' => 'Hello, this is my first blog post!',
//            'my-second-post' => 'Now , this is my second blog post!'
//        ];

        // $post = DB::table('posts')->where('slug',$slug)->first();
        $post = Post::where('slug',$slug)->first();

//        dd($post);
//        if(! array_key_exists($post,$posts)){
//            abort(404, 'Sorry, that post wan not found.');
//        }
        return view('post',
            [
                'post' => $post
            ]);


    }

}