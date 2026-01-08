@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-gray-400 focus:border-green-600 focus:ring-green-500 rounded-md shadow-sm']) }}>
