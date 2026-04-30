<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Components;

use Carbon\Carbon;
use Illuminate\View\Component;
use Illuminate\View\View;

class DateRange extends Component
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
        public string $format = 'D MMMM',
        public string $spacer = ' → ',
    ) {}

    public function render(): View
    {
        $showYear = ! $this->start->isSameYear(now()) || ! $this->end->isSameYear(now());
        $startFormat = $this->format.($showYear ? ' YYYY' : '');
        $endFormat = $this->format.' YYYY';

        if ($this->start->isSameYear($this->end)) {
            $startFormat = $this->format;
            if ($this->start->isSameMonth($this->end)) {
                $startFormat = 'D';
            }
        }

        return view('public::components.date-range', [
            'startFormat' => $startFormat,
            'endFormat' => $endFormat,
        ]);
    }
}
