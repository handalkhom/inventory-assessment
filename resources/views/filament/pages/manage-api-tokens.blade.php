<x-filament-panels::page>
    <div class="space-y-4">
        <p>Use the button above to generate a new API token. For security reasons, tokens can only be viewed once immediately after creation.</p>
    </div>

    @if ($generatedToken)
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm space-y-2">
            <h3 class="font-medium">API Token Generated</h3>
            <p class="text-sm text-gray-500">Please copy your new API token. For your security, it won't be shown again.</p>
            
            <div
                x-data="{ token: @js($generatedToken), copied: false }"
                class="flex items-center space-x-2 mt-4"
            >
                <div class="flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" x-model="token" readonly />
                    </x-filament::input.wrapper>
                </div>

                <x-filament::button
                    color="gray"
                    @click="
                        navigator.clipboard.writeText(token);
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied" class="text-green-500" x-cloak>Copied!</span>
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
