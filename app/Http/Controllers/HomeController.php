<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Specialty;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isStaff()) {
            return redirect()->to($user->staffHomeUrl());
        }

        return view('home', [
            'next' => $user->appointments()->with('doctor.specialty')
                ->where('status', 'confirmed')->where('starts_at', '>=', now())
                ->orderBy('starts_at')->first(),
            'specialties' => Specialty::withCount('doctors')->get(),
            'articles' => Article::latest('published_at')->take(3)->get(),
        ]);
    }
}
