<x-filament::card>
    <h2 class="text-lg font-bold mb-4">SLA</h2>

    <p>تذاكر متأخرة:
        <strong class="text-red-500">
            {{ \App\Models\Ticket::where('sla_deadline', '<', now())->count() }}
        </strong>
    </p>
</x-filament::card>
