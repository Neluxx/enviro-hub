<?php

namespace App\Livewire;

use App\Models\Home;
use App\Models\Node;
use App\Models\SensorData;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public ?int $selectedHomeId = null;
    public ?int $selectedNodeId = null;

    public function mount(): void
    {
        $firstHome = Home::first();

        if ($firstHome) {
            $this->selectedHomeId = $firstHome->id;
            $firstNode = $firstHome->nodes()->first();

            if ($firstNode) {
                $this->selectedNodeId = $firstNode->id;
            }
        }
    }

    public function updatedSelectedHomeId($value): void
    {
        $this->selectedHomeId = $value ? (int) $value : null;
        $firstNode = $this->selectedHomeId
            ? Node::where('home_id', $this->selectedHomeId)->first()
            : null;

        $this->selectedNodeId = $firstNode?->id;
        $this->dispatchChartData();
    }

    public function updatedSelectedNodeId($value): void
    {
        $this->selectedNodeId = $value ? (int) $value : null;
        $this->dispatchChartData();
    }

    #[Computed]
    public function homes()
    {
        return Home::orderBy('title')->get();
    }

    #[Computed]
    public function nodes()
    {
        if (! $this->selectedHomeId) {
            return collect();
        }

        return Node::where('home_id', $this->selectedHomeId)->orderBy('title')->get();
    }

    #[Computed]
    public function sensorData()
    {
        if (! $this->selectedNodeId) {
            return collect();
        }

        return SensorData::where('node_id', $this->selectedNodeId)
            ->orderBy('measured_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function chartData(): array
    {
        $data = $this->sensorData;

        $labels = $data->map(fn (SensorData $d) => $d->measured_at->format('H:i d/m'))->values()->toArray();

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
