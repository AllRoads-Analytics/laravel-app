<?php

namespace App\Http\Livewire;

use App\Models\Funnel;
use Livewire\Component;
use App\Models\Organization;

class Funnels extends Component
{
    // =========================================================================
    // Props.
    // =========================================================================

    /** @var Organization */
    public $Organization;

    // =========================================================================
    // Data.
    // =========================================================================

    public $funnels;
    public $options_host;
    public $selected_host;


    // =========================================================================
    // Component implementation.
    // =========================================================================

    public function render() {
        return view('livewire.funnels');
    }

    public function mount() {
        $this->updateFunnels();

        $this->options_host = $this->Organization->Funnels()
            ->groupBy('hostname')
            ->pluck('hostname');
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function updateFunnels() {
        $this->funnels = $this->Organization->Funnels()
            ->where(array_filter([
                'hostname' => $this->selected_host,
            ]))
            ->orderBy('name')
            ->get();
    }
}
