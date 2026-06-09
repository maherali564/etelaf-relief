<x-filament::page>
    <x-filament::card>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
            <div>
                <label style="font-size:0.85rem;font-weight:600;margin-bottom:4px">From</label>
                <input type="date" wire:model.live="dateFrom" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.9rem">
            </div>
            <div>
                <label style="font-size:0.85rem;font-weight:600;margin-bottom:4px">To</label>
                <input type="date" wire:model.live="dateTo" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.9rem">
            </div>
            <div>
                <label style="font-size:0.85rem;font-weight:600;margin-bottom:4px">Period</label>
                <select wire:model.live="period" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.9rem">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
        </div>
    </x-filament::card>

    @php $stats = $this->getStats(); @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem">
        @foreach($stats as $stat)
        <x-filament::card>
            <div style="display:flex;flex-direction:column;gap:4px">
                <span style="font-size:0.75rem;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">{{ $stat->getLabel() }}</span>
                <span style="font-size:1.5rem;font-weight:700;color:#0d6b4f">{{ $stat->getValue() }}</span>
                <span style="font-size:0.8rem;color:#94a3b8">{{ $stat->getDescription() }}</span>
            </div>
        </x-filament::card>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <x-filament::card>
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem">Donation Trend ({{ ucfirst($this->period) }})</h3>
            <canvas id="trendChart" height="200"></canvas>
        </x-filament::card>

        <x-filament::card>
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem">By Payment Method</h3>
            <canvas id="methodChart" height="200"></canvas>
        </x-filament::card>
    </div>

    <x-filament::card>
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem">By Project</h3>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
                <thead>
                    <tr style="border-bottom:2px solid #e2e8f0">
                        <th style="padding:8px 12px;text-align:left">Project</th>
                        <th style="padding:8px 12px;text-align:right">Total</th>
                        <th style="padding:8px 12px;text-align:right">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @php $projectData = $this->getProjectBreakdown(); @endphp
                    @forelse($projectData as $row)
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px">{{ $row['project'] }}</td>
                        <td style="padding:8px 12px;text-align:right;font-weight:600">${{ number_format($row['total'], 0) }}</td>
                        <td style="padding:8px 12px;text-align:right">{{ $row['count'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding:20px;text-align:center;color:#94a3b8">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::card>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('livewire:init', function () {
        function renderCharts() {
            var chartData = @js($this->getChartData());
            var methodData = @js($this->getMethodBreakdown());

            var trendCtx = document.getElementById('trendChart');
            if (trendCtx && window.trendChart) window.trendChart.destroy();
            if (trendCtx) {
                window.trendChart = new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Amount ($)',
                            data: chartData.amounts,
                            backgroundColor: '#0d6b4f',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            var methodCtx = document.getElementById('methodChart');
            if (methodCtx && window.methodChart) window.methodChart.destroy();
            if (methodCtx) {
                window.methodChart = new Chart(methodCtx, {
                    type: 'doughnut',
                    data: {
                        labels: methodData.map(function(m) { return m.method; }),
                        datasets: [{
                            data: methodData.map(function(m) { return m.total; }),
                            backgroundColor: ['#0d6b4f', '#2563eb', '#d97706', '#dc2626', '#7c3aed', '#0891b2'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        var total = ctx.dataset.data.reduce(function(a,b) { return a+b; }, 0);
                                        var pct = ((ctx.parsed / total) * 100).toFixed(1);
                                        return ctx.label + ': $' + Number(ctx.parsed).toLocaleString() + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        renderCharts();
        document.addEventListener('livewire:updated', function() { renderCharts(); });
    });
    </script>
    @endpush
</x-filament::page>
