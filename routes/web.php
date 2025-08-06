<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


require __DIR__.'/auth.php';

Route::get('/', function () {
    \App\Models\DiscussionRequest::whereNotNull('engineer_id')->update(['status' => 'completed']);
    if (Auth::check()) {
        return redirect('/admin');
    }else{
        return redirect('/admin/login');
    }
});


Route::get('/login', function () {

})->name('login');
Route::get('/dashboard', function () {
    return view('welcome');
})->name('dashboard');

