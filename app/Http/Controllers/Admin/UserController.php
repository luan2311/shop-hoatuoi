<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('searchString') ?? $request->input('q');
        $query = User::query();

        if (!empty($search)) {
            $escaped = '%' . addcslashes($search, '\\%_') . '%';
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', $escaped)
                  ->orWhere('username', 'like', $escaped)
                  ->orWhere('email', 'like', $escaped);
            });
        }

        $users = $query->orderBy('name')->orderBy('_id')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['same:password'],
            'name' => ['required', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên.',
            'password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
            'name.required' => 'Vui lòng nhập họ tên / tên hiển thị.',
        ]);

        $roles = $request->input('roles', ['User']);
        if (empty($roles)) {
            $roles = ['User'];
        }

        $user = new User([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'name' => $request->input('name'),
        ]);
        $user->is_active = true;
        $user->roles = $roles;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản mới thành công!');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ((string)$user->getKey() === (string)Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể thay đổi trạng thái tài khoản của chính mình.');
        }

        $user->is_active = !($user->is_active ?? true);
        $user->save();

        $msg = $user->is_active ? 'Đã kích hoạt tài khoản thành công!' : 'Đã vô hiệu hóa tài khoản thành công!';
        return redirect()->route('admin.users.index')->with('success', $msg);
    }
}
