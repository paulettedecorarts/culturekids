<?php

namespace App\Livewire\CMS;

use Livewire\Component;
use Livewire\WithFileUploads;

class Site extends Component
{
    use WithFileUploads;

    public $organization;
    public $hero_headline;
    public $hero_subheadline;
    public $mission_text;
    public $contact_email;
    public $contact_phone;
    public $active_tab = 'branding';

    public function mount()
    {
        // Mock data for initial state
        $this->organization = 'Naluwooza Creative Space';
        $this->hero_headline = 'Heritage & Hope for Every Child';
        $this->hero_subheadline = 'Connecting the next generation of Ugandans to the rich stories, songs, and values of our ancestors.';
        $this->mission_text = 'At Naluwooza Creative Space, we believe that education is most powerful when rooted in culture. Our mission is to provide an immersive, digital-first curriculum that celebrates the linguistic and artistic heritage of East Africa.';
        $this->contact_email = 'hello@naluwooza.app';
        $this->contact_phone = '+256 772 123 456';
    }

    public function render()
    {
        return view('livewire.cms.site')
            ->layout('layouts.cms');
    }
}
