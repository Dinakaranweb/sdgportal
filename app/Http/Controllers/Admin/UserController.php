<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdministrator();

        return view('admin.users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeAdministrator();

        return view('admin.users.form', [
            'user' => new User(['role' => 'editor', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Account created.');
    }

    public function edit(User $user): View
    {
        $this->authorizeAdministrator();

        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdministrator();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        // The last active administrator must keep their access.
        if ($user->isAdministrator() && ($data['role'] !== 'administrator' || ! $data['is_active'])) {
            $others = User::where('role', 'administrator')->where('is_active', true)->whereKeyNot($user->id)->count();

            if ($others === 0) {
                return back()->withErrors(['role' => 'At least one active administrator is required.'])->withInput();
            }
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdministrator();

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['email' => 'You cannot delete the account you are signed in with.']);
        }

        if ($user->isAdministrator() && User::where('role', 'administrator')->whereKeyNot($user->id)->count() === 0) {
            return back()->withErrors(['email' => 'At least one administrator is required.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Account deleted.');
    }

    protected function authorizeAdministrator(): void
    {
        abort_unless(request()->user()?->isAdministrator(), 403, 'Only administrators can manage accounts.');
    }
}
