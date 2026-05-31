<?php

namespace App\View\Components;

use App\Models\RawatInap as ModelsRawatInap;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RawatInap extends Component
{
    public ModelsRawatInap $kamarInap;

    /**
     * Create a new component instance.
     */
    public function __construct(public ModelsRawatInap $rawatInap)
    {
        $this->kamarInap = $rawatInap;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.rawat-inap');
    }
}
