<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use Flux\Flux;
use Livewire\Component;

class LocationManager extends Component
{
    public $locations;

    public $short;

    public $long;

    public $editingLocationId;

    public $showModal = false;

    // Delete Modal State
    public $showDeleteModal = false;

    public $deleteLocationId = null;

    public $deleteLocationExpected = '';

    public $deleteVerificationInput = '';

    public function mount()
    {
        $this->loadLocations();
    }

    public function loadLocations()
    {
        $this->locations = Location::all();
    }

    public function resetFields()
    {
        $this->short = '';
        $this->long = '';
        $this->editingLocationId = null;
    }

    public function openModal($id = null)
    {
        $this->resetFields();
        if ($id) {
            $this->editingLocationId = $id;
            $location = Location::find($id);
            $this->short = $location->short;
            $this->long = $location->long;
        }
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'short' => 'required|unique:locations,short,'.$this->editingLocationId,
            'long' => 'required',
        ]);

        Location::updateOrCreate(
            ['id' => $this->editingLocationId],
            [
                'short' => $this->short,
                'long' => $this->long,
            ]
        );

        $this->showModal = false;
        $this->loadLocations();
        Flux::toast(text: 'Location saved successfully.', variant: 'success');
    }

    public function confirmDelete($id)
    {
        $location = Location::find($id);

        if (! $location) {
            Flux::toast(text: 'Location not found.', variant: 'danger');

            return;
        }

        if ($location->nodes()->exists()) {
            Flux::toast(text: 'Cannot delete location that has nodes assigned to it.', variant: 'danger');

            return;
        }

        $this->deleteLocationId = $location->id;
        $this->deleteLocationExpected = $location->short;
        $this->deleteVerificationInput = '';
        $this->showDeleteModal = true;
    }

    public function executeDelete()
    {
        if ($this->deleteVerificationInput !== $this->deleteLocationExpected) {
            $this->addError('deleteVerificationInput', 'Location short name does not match.');

            return;
        }

        $location = Location::find($this->deleteLocationId);

        if (! $location) {
            $this->showDeleteModal = false;
            Flux::toast(text: 'Location already deleted.', variant: 'warning');
            $this->loadLocations();

            return;
        }

        $location->delete();
        $this->showDeleteModal = false;
        $this->loadLocations();
        Flux::toast(text: 'Location deleted successfully.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.location-manager');
    }
}
