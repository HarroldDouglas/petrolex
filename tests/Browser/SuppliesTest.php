<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AuthenticatesUsers;
use Tests\DuskTestCase;

class SuppliesTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /**
     * Test that a user can view the supplies list.
     */
    public function test_user_can_view_supplies_list(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $browser->visit('/supplies')
                ->waitForText('Liste des approvisionnements', 10)
                ->assertSee('Liste des approvisionnements');
        });
    }

    /**
     * Test that a user can create a new supply.
     */
    public function test_user_can_create_supply(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $browser->visit('/supplies/create')
                ->waitForText('Ajouter un approvisionnement', 10)
                ->assertSee('Ajouter un approvisionnement');

            // TODO: Fill out the form and submit
            // Example: ->type('field_name', 'value')
            //          ->press('Submit Button Text')
            //          ->waitForLocation('/supplies', 10)
            //          ->assertSee('Supply created successfully');
        });
    }

    /**
     * Test that a user can edit an existing supply.
     */
    public function test_user_can_edit_supply(): void
    {
        // TODO: Create a supply first to have something to edit
        // $supply = Supply::factory()->create();

        $this->browse(function (Browser $browser) {
            // $this->loginAsAdmin($browser);
            // $browser->visit('/supplies/' . $supply->id . '/edit')
            //         ->assertSee('Modifier l'Approvisionnement');

            // TODO: Fill out the form and submit
        });
    }

    /**
     * Test that a user can view details of an existing supply.
     */
    public function test_user_can_view_supply_details(): void
    {
        // TODO: Create a supply first
        // $supply = Supply::factory()->create();

        $this->browse(function (Browser $browser) {
            // $this->loginAsAdmin($browser);
            // $browser->visit('/supplies/' . $supply->id . '/details')
            //         ->assertSee('Détails de l'Approvisionnement');
        });
    }

    /**
     * Test that a user can register products for a supply.
     */
    public function test_user_can_register_products_for_supply(): void
    {
        // TODO: Create a supply first
        // $supply = Supply::factory()->create();

        $this->browse(function (Browser $browser) {
            // $this->loginAsAdmin($browser);
            // $browser->visit('/supplies/' . $supply->id . '/register-products')
            //         ->assertSee('Enregistrer les Produits');
        });
    }

    /**
     * Test that a user can scan bottles for a supply.
     */
    public function test_user_can_scan_bottles_for_supply(): void
    {
        // TODO: Create a supply first
        // $supply = Supply::factory()->create();

        $this->browse(function (Browser $browser) {
            // $this->loginAsAdmin($browser);
            // $browser->visit('/supplies/' . $supply->id . '/scan-bottles')
            //         ->assertSee('Scanner les Bouteilles');
        });
    }

    /**
     * Test that a user can delete an existing supply.
     */
    public function test_user_can_delete_supply(): void
    {
        // TODO: Create a supply first
        // $supply = Supply::factory()->create();

        $this->browse(function (Browser $browser) {
            // $this->loginAsAdmin($browser);
            // $browser->visit('/supplies')
            //         ->assertSee('Supply to delete') // Find the supply in the list
            //         ->press('Delete Button for Supply') // Click delete button
            //         ->acceptDialog() // Accept confirmation dialog
            //         ->assertDontSee('Supply to delete'); // Assert it's gone
        });
    }
}
