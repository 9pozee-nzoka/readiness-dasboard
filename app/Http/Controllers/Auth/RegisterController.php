<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('auth.register', compact('departments'));
    }

    public function showClientRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.register-client');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Role is always set to employee by default — admin will assign the real role on approval
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::Employee,   // placeholder until admin approves
            'department_id' => $data['department_id'],
            'declared_level' => $data['declared_level'],
            'is_approved' => false,
        ]);

        // Do NOT log them in — they must wait for admin approval
        return redirect()->route('login')
            ->with('success', 'Account created! Your access is pending administrator approval. You will be notified once approved.');
    }

    public function registerClient(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'organization' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Create client user - approved by default since they're external
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::Client,
            'is_approved' => true, // Clients are auto-approved
        ]);

        // Log them in immediately
        Auth::login($user);

        return redirect()->route('bookings.create')
            ->with('success', 'Account created successfully! You can now submit your booking.');
    }
}
