<?php

namespace App\Livewire;

use App\Models\Node;
use App\Models\SensorData;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public ?int $selectedNodeId = null;

    public function mount(): void
    {
        $firstNode = Node::orderBy('title')->first();

        if ($firstNode) {
            $this->selectedNodeId = $firstNode->id;
        }
    }

    public function updatedSelectedNodeId($value): void
    {
        $this->selectedNodeId = $value ? (int) $value : null;
        $this->dispatchChartData();
    }

    #[Computed]
    public function nodes()
    {
        return Node::orderBy('title')->get();
    }

    #[Computed]
    public function sensorData()
    {
        if (! $this->selectedNodeId) {
            return collect();
        }

        return SensorData::where('node_id', $this->selectedNodeId)
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at')
            ->get();
    }

    #[Computed]
    public function chartData(): array
    {
        $data = $this->sensorData;

        $labels = $data->map(fn (SensorData $d) => $d->measured_at->format('H:i'))->values()->toArray();

        return [
            'labels' => $labels,
            'temperature' => $data->pluck('temperature')->map(fn ($v) => (float) $v)->values()->toArray(),
            'humidity' => $data->pluck('humidity')->map(fn ($v) => (float) $v)->values()->toArray(),
            'carbon_dioxide' => $data->pluck('carbon_dioxide')->values()->toArray(),
        ];
    }

    private function dispatchChartData(): void
    {
        $this->dispatch('chart-updated', chartData: $this->chartData);
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'chartData' => $this->chartData,
        ])->layout('layouts.app');
    }
}
