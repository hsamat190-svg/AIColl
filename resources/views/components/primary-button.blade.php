<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 border border-transparent rounded-xl text-sm font-semibold text-white normal-case tracking-normal bg-gradient-to-r from-violet-600 to-fuchsia-600 shadow-md shadow-violet-500/25 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/50 focus:ring-offset-2 focus:ring-offset-[#f3f1f9] active:brightness-95 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
