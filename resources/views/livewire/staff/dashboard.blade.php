<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Dashboard</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Overview of Aduan System</p>
        </div>
        <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Row 1: Tickets by Status + Tickets by Department -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Doughnut: Tickets by Status -->
        <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg flex flex-col">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tickets by Status</h2>
            <div class="flex-1 flex items-center justify-center">
                <div class="relative w-72 h-72">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </flux:card>

        <!-- Horizontal Bar: Tickets by Department -->
        <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg flex flex-col">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tickets by Department
            <div class="flex-1">
                <canvas id="deptChart" style="max-height:320px"></canvas>
            </div>
        </flux:card>

    </div>

    <!-- Row 2: Ticket Tracker -->
    <div class="grid grid-cols-1 gap-4">

        <!-- Ticket Tracker -->
        <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Ticket Tracker</h2>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Ticket #</flux:table.column>
                    <flux:table.column>Subject</flux:table.column>
                    <flux:table.column>Person In Charge</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Days Left</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($slaTickets as $ticket)
                        @php
                            $daysRemaining = $ticket->days_remaining;
                            $isOverdue = $ticket->is_overdue;
                        @endphp
                        <flux:table.row class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50" x-on:click="Livewire.navigate('{{ route('staff.tickets.show', $ticket) }}')">
                            <flux:table.cell class="font-medium text-indigo-600 dark:text-indigo-400">
                                {{ $ticket->ticket_number }}
                            </flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate">
                                {{ $ticket->subject }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $ticket->assignedAgent?->name ?? 'Unassigned' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$ticket->status_color">
                                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($isOverdue)
                                    <flux:badge size="sm" color="red">Overdue ({{ abs($daysRemaining) }}d)</flux:badge>
                                @elseif($daysRemaining <= 2)
                                    <flux:badge size="sm" color="amber">{{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green">{{ $daysRemaining }} days</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-8">
                                <flux:text>No active SLA tickets.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

    </div>

    <script>
        function initDashboardCharts() {
            // Destroy existing Chart instances so re-navigation doesn't error
            ['statusChart', 'deptChart'].forEach(id => {
                const existing = Chart.getChart(id);
                if (existing) existing.destroy();
            });

            Chart.defaults.font.family = "'Inter', sans-serif";

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            const labelColor = isDark ? '#a1a1aa' : '#52525b';

            // 1. Doughnut — Tickets by Status
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($statusChart['labels']),
                    datasets: [{
                        data: @json($statusChart['data']),
                        backgroundColor: @json($statusChart['colors']),
                        borderWidth: 2,
                        borderColor: isDark ? '#18181b' : '#ffffff',
                    }]
                },
                options: {
                    cutout: '50%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: labelColor, padding: 12, boxWidth: 12 }
                        },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                    }
                }
            });

            // 2. Horizontal Bar — Tickets by Department
            new Chart(document.getElementById('deptChart'), {
                type: 'bar',
                data: {
                    labels: @json($deptChart['labels']),
                    datasets: [{
                        label: 'Tickets',
                        data: @json($deptChart['data']),
                        backgroundColor: '#6366f1cc',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { color: labelColor, precision: 0 },
                            grid: { color: gridColor }
                        },
                        y: {
                            ticks: { color: labelColor },
                            grid: { display: false }
                        }
                    }
                }
            });

        }

        document.addEventListener('DOMContentLoaded', initDashboardCharts);
        document.addEventListener('livewire:navigated', initDashboardCharts);
    </script>
</div>
