<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Development-only login controller.
 * Presents a dropdown of all active users — no password required.
 *
 * Accessible only when AUTH_MODE=development (enforced by BlockDevelopmentAuth middleware).
 */
class DevelopmentLoginController extends Controller
{
    public function show()
    {
        $users = User::where("is_active", true)
                     ->orderBy("name")
                     ->get(["id", "name", "username", "domain", "position", "is_global_admin"]);

        return view("auth.dev-login", compact("users"));
    }

    public function login(Request $request)
    {
        $request->validate([
            "user_id" => ["required", "integer", "exists:users,id"],
        ]);

        $user = User::where("id", $request->user_id)
                    ->where("is_active", true)
                    ->firstOrFail();

        Auth::login($user, remember: false);

        $request->session()->regenerate();

        return redirect()->intended(route("dashboard"));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("auth.dev-login");
    }
}
