<?php
/**
 * Created by PhpStorm.
 * User: a.abd
 * Date: 1/20/2020
 * Time: 8:41 PM
 */

namespace App\Http\Controllers;

use App\Post;

class PostsController extends Controller
{


    public function show($slug){
        $post = Post::where('slug',$slug)->get();
        dd($post);
//         return view('post',['post'=>$post]);


    }
}