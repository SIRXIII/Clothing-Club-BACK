<?php

namespace App\Exports;

use App\Models\Traveler;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TravelersExport implements FromView
{

     protected $travelers;

    public function __construct($travelers)
    {
        $this->travelers = $travelers;

    }

    /**
    * @return \Illuminate\Support\View
    */
     public function view(): View
    {
        return view('exports.travelers', [
            'travelers' => $this->travelers

        ]);
    }
}
