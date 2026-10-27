<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(ContactRequest $request)
    {
        // Ô "name" trên form lưu vào field full_name; code (LH001...) và status do model tự gán
        Contact::create([
            'full_name' => $request->validated('name'),
            'email'     => $request->validated('email'),
            'phone'     => $request->validated('phone'),
            'message'   => $request->validated('message'),
        ]);

        return redirect('/contact')
            ->with('success', 'Gửi liên hệ thành công! Cửa hàng sẽ liên hệ lại sớm nhất.');
    }
}
