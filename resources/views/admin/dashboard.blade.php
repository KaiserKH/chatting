<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white p-5 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Messages</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalMessages }}</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Storage Used</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($usedBytes / 1024 / 1024, 2) }} MB</p>
                    <p class="text-xs text-gray-500">of 5120 MB limit</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-sm">
                <h3 class="font-semibold text-lg text-gray-900">Global Feature Toggles</h3>
                <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                    @csrf
                    @php
                        $settings = [
                            'e2ee_enabled' => 'Enable End-to-End Encryption',
                            'chat_history_enabled' => 'Enable Chat History Saving',
                            'media_upload_enabled' => 'Enable Media Uploads',
                            'friend_only_messaging_enabled' => 'Enable Friend-only Messaging',
                            'registration_enabled' => 'Enable User Registration',
                            'email_verification_enabled' => 'Enable Email Verification',
                        ];
                    @endphp
                    @foreach($settings as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="settings[{{ $key }}]" value="1" @checked(\App\Models\AdminSetting::bool($key, in_array($key, ['chat_history_enabled', 'registration_enabled'], true))) class="rounded border-gray-300 text-pink-600">
                            {{ $label }}
                        </label>
                    @endforeach
                    <div class="md:col-span-2">
                        <x-primary-button>Save Toggles</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="flex gap-4 text-sm">
                <a href="{{ route('admin.users.index') }}" class="text-pink-700 hover:text-pink-900">Manage Users</a>
                <a href="{{ route('admin.messages.index') }}" class="text-pink-700 hover:text-pink-900">Moderate Messages</a>
            </div>
        </div>
    </div>
</x-app-layout>
