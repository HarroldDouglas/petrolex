<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class StatBlock extends Component
{
    public $bgColor;
    public $textColor;
    public $icon;
    public $title;
    public $subtitle;
    public $value;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $bgColor = 'light-primary',
        $textColor = 'primary',
        $icon = 'currency-circle-dollar',
        $title = 'Titre',
        $subtitle = '',
        $value = '0'
    ) {
        $this->bgColor = $bgColor;
        $this->textColor = $textColor;
        $this->icon = $icon;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dashboard.stat-block');
    }
}
