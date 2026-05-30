<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-teal-600 dark:hover:text-teal-400 focus:outline-none focus:bg-slate-50 dark:focus:bg-slate-800/50 transition duration-150 ease-in-out font-medium']) }}>
    {{ $slot }}
</a>