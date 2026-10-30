<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = AdminPagination::perPage($request);
        $search = $request->input('searchString') ?? $request->input('q');
        $query = Contact::query();

        if (!empty($search)) {
            $escaped = '%' . addcslashes($search, '\\%_') . '%';
            $query->where(function ($q) use ($escaped) {
                $q->where('full_name', 'like', $escaped)
                  ->orWhere('email', 'like', $escaped)
                  ->orWhere('phone', 'like', $escaped)
                  ->orWhere('message', 'like', $escaped);
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->orderBy('_id', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact): View
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Đã xóa thông tin liên hệ thành công!');
    }
}
