<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.form', ['register' => false]);
    }

    public function registerForm()
    {
        return view('auth.form', ['register' => true]);
    }

    public function login(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak sesuai.']);
        }
        $request->session()->regenerate();
        $request->session()->forget('builder');
        $this->record($request->user(), 'login');

        return redirect()->route($request->user()->role === 'admin' ? 'admin.index' : 'builder');
    }

    public function register(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed', 'consent' => 'accepted',
        ], ['consent.accepted' => 'Setujui penyimpanan data untuk membuat akun.']);
        // Role is never accepted from registration input.
        $user = User::create(collect($data)->only(['name', 'email', 'password'])->all());
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('builder');
        $this->record($user->fresh(), 'registered');

        return redirect()->route('builder');
    }

    public function logout(Request $request)
    {
        $this->record($request->user(), 'logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('notice', 'Anda berhasil keluar. Draft tetap tersimpan di akun Anda.');
    }

    public function heartbeat(Request $request)
    {
        if (! $request->user()->activities()->where('created_at', '>=', now()->subMinute())->exists()) {
            $this->record($request->user(), 'heartbeat');
        }

        return response()->noContent();
    }

    private function record(User $user, string $action): void
    {
        if ($user->role === 'user') {
            $user->activities()->create(['action' => $action, 'created_at' => now()]);
        }
    }
}
