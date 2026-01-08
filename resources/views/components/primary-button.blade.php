<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 !bg-green-600 border border-transparent rounded-md font-semibold text-xs !text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2']) }} style="background-color: #059669 !important; color: #ffffff !important;">
    {{ $slot }}
</button>
