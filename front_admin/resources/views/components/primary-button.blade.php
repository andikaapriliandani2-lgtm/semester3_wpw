<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-xl border border-transparent bg-[#173f35] px-4 py-2 font-semibold text-xs uppercase tracking-widest text-white shadow-sm transition duration-150 ease-in-out hover:bg-[#286b59] focus:bg-[#286b59] focus:outline-none focus:ring-2 focus:ring-[#f4b942] focus:ring-offset-2 active:bg-[#102e27]']) }}>
    {{ $slot }}
</button>
