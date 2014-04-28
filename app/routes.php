<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('form');
});


Route::post('/',function(){

	//定义验证规则
	$rules = array(
		'link' => 'required|url'
	);

	//运行验证
	$validation = Validator::make(Input::all(),$rules);

	//如果验证失败，则返回主页面并提示错误信息
	if($validation->fails()) {
		return Redirect::to('/')
				->withInput()
				->withErrors($validation);
	} else {
		//在数据库中存在是否有已经存在数据
		$link = Link::where('url','=',Input::get('link'))
			->first();
		//如果存在，则把数据输出到视图中
		if($link) {
			return Redirect::to('/')
				->withInput()
				->with('link',$link->hash);
		//如果没有则创建数据
		} else {
			//首先创建一个新的hash值
			do {
				$newHash = Str::random(6);
			} while(Link::where('hash','=',$newHash)->count() > 0);

			//然后把数据存入到数据中对应的字段中
			Link::create(array(
				'url'	=> Input::get('link'),
				'hash'	=> $newHash
			));

			//最后把hash传递给视图
			return Redirect::to('/')
				->withInput()
				->with('link',$newHash); 
		}
	}


});

Route::get('{hash}',function($hash) {
	//我们会根据hash的值，来查询数据库中对应的链接并保存在$link变量中
	$link = Link::where('hash','=',$hash)
		->first();
	//如果存在，则跳转到对应的链接
	if($link) {
		return Redirect::to($link->url);
	//如果没有，则返回相关的错误信息
	} else {
		return Redirect::to('/')
			->with('message','失效的链接');
	}
})->where('hash', '[0-9a-zA-Z]{6}');
