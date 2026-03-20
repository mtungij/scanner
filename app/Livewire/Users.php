<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Support\Toast;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Users extends Component
{
    public string $name = '';

    public string $email = '';

    public string $role = 'cashier';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,cashier'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        $this->reset(['name', 'email', 'role', 'password', 'password_confirmation']);
        $this->role = 'cashier';
        $this->dispatch('close-modal', id: 'create-user-modal');

        Toast::success('User created successfully.');
    }

    public function getUsersProperty()
    {
        return User::query()
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.users');
    }
}
