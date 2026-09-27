@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#28745f] focus:ring-[#28745f]']) }}>
