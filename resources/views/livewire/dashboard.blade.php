<div>
    {{-- Navbar --}}
    <div class="navbar bg-base-100 shadow-sm">
        <div class="flex-1">
            <a class="btn btn-ghost text-xl">EnviroHub</a>
        </div>
        <div>
            {{ config('app.version') }}
        </div>
    </div>

    <div class="container mx-auto p-4 max-w-5xl">
        {{-- Selectors --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Home</span>
                </label>
                <select
                    wire:model.live="selectedHomeId"
                    class="select select-bordered w-full"
                >
                    <option value="" disabled>Select a home</option>
                    @foreach ($this->homes as $home)
                        <option value="{{ $home->id }}">{{ $home->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Node</span>
                </label>
                <select
                    wire:model.live="selectedNodeId"
                    class="select select-bordered w-full"
                    @if($this->nodes->isEmpty()) disabled @endif
                >
                    <option value="" disabled>Select a node</option>
                    @foreach ($this->nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Everything below is managed by Alpine --}}
        <div
            wire:ignore
            x-data="chartDashboard(@js($chartData))"
        >
            {{-- Empty state --}}
            <div x-show="!hasData" class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>No sensor data available for the selected node.</span>
            </div>

            {{-- Stat cards --}}
            <div x-show="hasData" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="stat bg-base-100 rounded-box shadow-sm">
                    <div class="stat-title">Latest Temperature</div>
                    <div class="stat-value text-primary text-2xl">
                        <span x-text="latestTemp"></span> °C
                    </div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow-sm">
                    <div class="stat-title">Latest Humidity</div>
                    <div class="stat-value text-secondary text-2xl">
                        <span x-text="latestHumidity"></span> %
                    </div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow-sm">
                    <div class="stat-title">Latest CO₂</div>
                    <div class="stat-value text-accent text-2xl">
                        <span x-text="latestCo2"></span> ppm
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div x-show="hasData" class="grid grid-cols-1 gap-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-base">Temperature (°C)</h2>
                        <div style="height: 300px;">
                            <canvas x-ref="temperatureChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-base">Humidity (%)</h2>
                        <div style="height: 300px;">
                            <canvas x-ref="humidityChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-base">CO₂ (ppm)</h2>
                        <div style="height: 300px;">
                            <canvas x-ref="co2Chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
