<div class="mx-auto mt-4 max-w-3xl space-y-10 sm:mt-6 sm:space-y-12">
    <div>
        <x-ui.heading>Account Information</x-ui.heading>
        <x-ui.text class="opacity-50">update your account public crendetiels</x-ui.text>
        <div class="grow">
            <form 
                wire:submit="saveChanges"
                class="mt-6 space-y-4 rounded-lg bg-white p-4 dark:bg-neutral-800/10 sm:mt-8 sm:p-6"
            >
                <x-ui.field>
                    <x-ui.label>name</x-ui.label>
                    <x-ui.input wire:model="name" />
                    <x-ui.error name="name" />
                </x-ui.field>
                
                <x-ui.field>
                    <x-ui.label>email address</x-ui.label>
                    <x-ui.input 
                        wire:model="email"
                        type="email"
                        copyable 
                    />
                    <x-ui.error name="email" />
                </x-ui.field>
                <x-ui.button 
                    type="submit"
                >Save changes</x-ui.button>
            </form>
        </div>
    </div>
    
    <div>
        <x-ui.heading>Change password</x-ui.heading>
        <x-ui.text class="opacity-50">Update your security credentials</x-ui.text>
        <form 
            wire:submit="updatePassword"
            class="mt-6 space-y-4 rounded-lg bg-white p-4 dark:bg-neutral-800/10 sm:mt-8 sm:p-6"
        >
            
            <x-ui.field>
                <x-ui.label>Current Password</x-ui.label>
                <x-ui.input 
                    wire:model="current_password"
                    type="password"
                    revealable 
                />
                <x-ui.error name="current_password" />
            </x-ui.field>
            
            <x-ui.field>
                <x-ui.label>New Password</x-ui.label>
                <x-ui.input 
                    wire:model="password"
                    type="password"
                    revealable 
                />
                <x-ui.error name="password" />
            </x-ui.field>
            
            <x-ui.field>
                <x-ui.label>Confirm New Password</x-ui.label>
                <x-ui.input 
                    wire:model="password_confirmation"
                    type="password"
                    revealable
                />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>
            
            <x-ui.button 
                type="submit"
                class="mt-6"
            >
                Change Password
            </x-ui.button>
        </form>
    </div>
</div>