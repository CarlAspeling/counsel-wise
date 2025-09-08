<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Surname -->
        <div class="mt-4">
            <x-input-label for="surname" :value="__('Surname')" />
            <x-text-input id="surname" class="block mt-1 w-full" type="text" name="surname" :value="old('surname')" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('surname')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- HPCSA Registration Number -->
        <div class="mt-4">
            <x-input-label for="hpcsa_number" :value="__('HPCSA Number')" />
            <x-text-input id="hpcsa_number" class="block mt-1 w-full" type="text" name="hpcsa_number"
                          :value="old('hpcsa_number')" required autocomplete="hpcsa_number" />
            <x-input-error :messages="$errors->get('hpcsa_number')" class="mt-2" />
        </div>

        <!-- Account Type -->
        <div class="mt-4">
            <x-input-label for="account_type" :value="__('Account Type')" />
            <select id="account_type" name="account_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md
  shadow-sm" required>
                <option value="">Select Account Type</option>
                <option value="counsellor_free" {{ old('account_type') == 'counsellor_free' ? 'selected' : '' }}>
                    Counsellor (Free)
                </option>
                <option value="counsellor_paid" {{ old('account_type') == 'counsellor_paid' ? 'selected' : '' }}>
                    Counsellor (Paid)
                </option>
                <option value="student_rc" {{ old('account_type') == 'student_rc' ? 'selected' : '' }}>Student Registered Counsellor
                </option>
            </select>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
