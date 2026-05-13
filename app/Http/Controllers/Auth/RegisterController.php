<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Role is always set to employee by default — admin will assign the real role on approval
        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => $data['password'],
            'role'           => UserRole::Employee,   // placeholder until admin approves
            'department_id'  => $data['department_id'],
            'declared_level' => $data['declared_level'],
            'is_approved'    => false,
        ]);

        // Do NOT log them in — they must wait for admin approval
        return redirect()->route('login')
            ->with('success', 'Account created! Your access is pending administrator approval. You will be notified once approved.');
    }
}
