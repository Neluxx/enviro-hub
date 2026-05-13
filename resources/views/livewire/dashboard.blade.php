<div class="h-screen flex flex-col overflow-hidden">
    <header class="flex-none border-b border-zinc-800/80">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between gap-6">
            <div class="flex items-baseline gap-3">
                <span class="text-green-400 font-mono text-base tracking-tight">EnviroHub</span>
                <span class="text-xs text-zinc-500 font-mono">{{ config('app.version') }}</span>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-[10px] uppercase tracking-[0.18em] text-zinc-500" for="node-select">Node</label>
                <select
                    id="node-select"
                    wire:model.live="selectedNodeId"
                    class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:border-green-500/60 disabled:opacity-50"
                    @if($this->nodes->isEmpty()) disabled @endif
                >
                    <option value="" disabled>Select node</option>
                    @foreach ($this->nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </header>

    <main class="flex-1 min-h-0 max-w-6xl w-full mx-auto px-6 py-6">
        <div
            wire:ignore
            x-data="chartDashboard(@js($chartData))"
            class="h-full flex flex-col gap-4"
        >
            <div x-show="!hasData" class="flex-1 border border-zinc-800 rounded-lg flex items-center justify-center">
                <p class="text-zinc-500 text-sm">No sensor data available for the selected node.</p>
            </div>

            <div x-show="hasData" class="flex-none grid grid-cols-1 sm:grid-cols-3 gap-3">
                <template x-for="metric in metrics" :key="metric.key">
                    <button
                        type="button"
                        @click="select(metric.key)"
                        :class="metric.key === selectedMetric
                            ? 'border-zinc-700 bg-zinc-900'
                            : 'border-zinc-900 bg-zinc-950 hover:border-zinc-800 hover:bg-zinc-900/60'"
                        class="text-left border rounded-lg p-5 transition cursor-pointer"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[10px] uppercase tracking-[0.18em] text-zinc-500"
                                  x-text="metric.label"></span>
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="metric.key === selectedMetric ? metric.dotClass : 'bg-zinc-700'"></span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="font-mono text-3xl tabular-nums tracking-tight"
                                  :class="metric.key === selectedMetric ? metric.textClass : 'text-zinc-100'"
                                  x-text="latest[metric.key]"></span>
                            <span class="text-xs text-zinc-500" x-text="metric.unit"></span>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="hasData" class="flex-1 min-h-0 border border-zinc-800 rounded-lg flex flex-col">
                <div class="flex-none flex items-center justify-between px-5 py-3 border-b border-zinc-800">
                    <h2 class="text-sm text-zinc-300" x-text="currentMetric.label + ' — last 24 hours'"></h2>
                    <span class="font-mono text-xs text-zinc-500" x-text="currentMetric.unit"></span>
                </div>
                <div class="flex-1 min-h-0 p-4 relative">
                    <canvas x-ref="chart"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>
