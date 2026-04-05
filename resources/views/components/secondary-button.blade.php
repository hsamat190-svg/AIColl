<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2.5 bg-white border border-violet-200 rounded-xl text-sm font-semibold text-violet-900 normal-case tracking-normal shadow-sm hover:bg-violet-50/80 hover:border-violet-300 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/40 focus:ring-offset-2 focus:ring-offset-white disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
