<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-ui.heading level="h1" size="xl">Users</x-ui.heading>
            <x-ui.text class="mt-1 opacity-60">Create admin and cashier accounts.</x-ui.text>
        </div>

        <x-ui.modal id="create-user-modal" heading="Create User" width="md">
            <x-slot:trigger>
                <x-ui.button icon="plus">New User</x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Name</x-ui.label>
                    <x-ui.input wire:model="name" placeholder="Full name" />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Email</x-ui.label>
                    <x-ui.input wire:model="email" type="email" placeholder="user@example.com" />
                    <x-ui.error name="email" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Role</x-ui.label>
                    <x-ui.select wire:model="role">
                        <x-ui.select.option value="cashier">Cashier</x-ui.select.option>
                        <x-ui.select.option value="admin">Admin</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="role" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Password</x-ui.label>
                    <x-ui.input wire:model="password" type="password" revealable />
                    <x-ui.error name="password" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Confirm Password</x-ui.label>
                    <x-ui.input wire:model="password_confirmation" type="password" revealable />
                </x-ui.field>
            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="createUser">Create</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    <x-ui.card size="xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-neutral-700">
                        <th class="px-2 py-2">Name</th>
                        <th class="px-2 py-2">Email</th>
                        <th class="px-2 py-2">Role</th>
                        <th class="px-2 py-2">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->users as $user)
                        <tr class="border-b border-gray-200 last:border-b-0 dark:border-neutral-800" wire:key="user-{{ $user->id }}">
                            <td class="px-2 py-2">{{ $user->name }}</td>
                            <td class="px-2 py-2">{{ $user->email }}</td>
                            <td class="px-2 py-2">
                                <x-ui.badge variant="outline" color="{{ $user->role === 'admin' ? 'blue' : 'green' }}">{{ ucfirst($user->role) }}</x-ui.badge>
                            </td>
                            <td class="px-2 py-2">{{ $user->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-6 text-center text-neutral-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
