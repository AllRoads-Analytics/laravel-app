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

    // =========================================================================
    // Component implementation.
    // =========================================================================

    public function render() {
        return view('livewire.funnels');
    }

    public function mount() {
        $this->updateFunnels();
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function updateFunnels() {
        $this->funnels = $this->Organization->Funnels()
            ->orderBy('name')
            ->get();
    }
}
