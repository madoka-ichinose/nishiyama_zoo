<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        // 既にログイン済みで管理者ならダッシュボードへ
        if (Auth::check() && (auth()->user()->is_admin ?? false)) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        // 通常の web ガードでログイン試行
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // 管理者でなければ即ログアウト
            if (!(auth()->user()->is_admin ?? false)) {
                Auth::logout();
                return back()->withErrors(['email' => '管理者権限がありません。']);
            }
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
                     ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
