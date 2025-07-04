<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Support\Pages\AccessoryPage;
use Tests\Browser\Traits\AuthenticatesUsers;
use Tests\DuskTestCase;

class AccessoryTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /**
     * Test l'accès à la liste des accessoires sans authentification (redirection vers login)
     */
    public function test_accessories_list_requires_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès liste accessoires sans authentification...\n";

            $browser->visit('/accessories')
                ->pause(2000)
                ->screenshot('accessories_list_auth_redirect');

            // Vérifier la redirection vers login
            $currentUrl = $browser->driver->getCurrentURL();
            echo 'URL après tentative d\'accès: '.$currentUrl."\n";

            if (str_contains($currentUrl, '/login')) {
                echo "✅ Redirection vers login confirmée\n";
                $browser->assertSee('Se connecter');
                echo "✅ Page de login affichée\n";
            } else {
                echo "❌ Pas de redirection vers login\n";
            }
        });
    }

    /**
     * Test l'accès à la liste des accessoires après authentification
     */
    public function test_authenticated_user_can_access_accessories_list()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès liste accessoires après authentification...\n";

            // Connexion et accès
            $this->loginAsAdmin($browser);

            // Accéder à la liste des accessoires en utilisant le Page Object
            $browser->visit(new AccessoryPage)
                ->screenshot('accessories_list_after_login')
                ->assertSeeAccessoryList($browser);

            echo "✅ Liste des accessoires accessible après authentification\n";
        });
    }

    /**
     * Test l'accès au formulaire de création d'accessoire sans authentification
     */
    public function test_create_accessory_form_requires_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès formulaire création accessoire sans authentification...\n";

            $browser->visit('/accessories/create')
                ->pause(2000)
                ->screenshot('create_accessory_auth_redirect');

            $currentUrl = $browser->driver->getCurrentURL();
            if (str_contains($currentUrl, '/login')) {
                echo "✅ Redirection vers login confirmée\n";
                $browser->assertSee('Se connecter');
            } else {
                echo "❌ Pas de redirection vers login\n";
            }
        });
    }

    /**
     * Test l'accès au formulaire de création d'accessoire après authentification
     */
    public function test_authenticated_user_can_access_create_accessory_form()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès formulaire création accessoire après authentification...\n";

            $this->loginAsAdmin($browser);

            $browser->visit('/accessories/create')
                ->screenshot('create_accessory_form_after_login')
                ->assertSeeCreateForm($browser);

            echo "✅ Formulaire de création d'accessoire accessible après authentification\n";
        });
    }

    /**
     * Test la création d'un nouvel accessoire
     */
    public function test_can_create_new_accessory()
    {
        $this->browse(function (Browser $browser) {
            echo "➕ Test création d'un nouvel accessoire...\n";

            $this->loginAsAdmin($browser);

            $accessoryPage = new AccessoryPage();

            $browser->visit('/accessories/create')
                ->assertSeeCreateForm($browser)
                ->type('@accessory-name-field', 'Test Accessory Name')
                ->type('@accessory-description-field', 'Description for test accessory.')
                ->type('@accessory-price-field', '123.45')
                ->press('@submit-button')
                ->pause(3000) // Attendre la soumission et la redirection
                ->screenshot('accessory_created');

            // Vérifier la redirection vers la liste et la présence du nouvel accessoire
            $browser->assertPathIs('/accessories')
                    ->assertSee('Test Accessory Name');

            echo "✅ Nouvel accessoire créé avec succès\n";
        });
    }

    /**
     * Test l'accès au formulaire d'édition d'accessoire sans authentification
     */
    public function test_edit_accessory_form_requires_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès formulaire édition accessoire sans authentification...\n";

            // Créer un accessoire pour avoir un ID valide pour le test
            $accessory = \App\Models\Accessory::factory()->create();

            $browser->visit('/accessories/edit/' . $accessory->id)
                ->pause(2000)
                ->screenshot('edit_accessory_auth_redirect');

            $currentUrl = $browser->driver->getCurrentURL();
            if (str_contains($currentUrl, '/login')) {
                echo "✅ Redirection vers login confirmée\n";
                $browser->assertSee('Se connecter');
            } else {
                echo "❌ Pas de redirection vers login\n";
            }

            // Nettoyage
            $accessory->delete();
        });
    }

    /**
     * Test l'accès au formulaire d'édition d'accessoire après authentification
     */
    public function test_authenticated_user_can_access_edit_accessory_form()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès formulaire édition accessoire après authentification...\n";

            $this->loginAsAdmin($browser);

            // Créer un accessoire pour avoir un ID valide pour le test
            $accessory = \App\Models\Accessory::factory()->create();

            $browser->visit('/accessories/edit/' . $accessory->id)
                ->screenshot('edit_accessory_form_after_login')
                ->assertSeeEditForm($browser, $accessory->name);

            echo "✅ Formulaire d'édition d'accessoire accessible après authentification\n";

            // Nettoyage
            $accessory->delete();
        });
    }

    /**
     * Test la modification d'un accessoire existant
     */
    public function test_can_edit_existing_accessory()
    {
        $this->browse(function (Browser $browser) {
            echo "✏️ Test modification d'un accessoire existant...\n";

            $this->loginAsAdmin($browser);

            // Créer un accessoire pour le modifier
            $accessory = \App\Models\Accessory::factory()->create([
                'name' => 'Original Name',
                'description' => 'Original Description',
                'price' => 100.00,
            ]);

            $accessoryPage = new AccessoryPage();

            $browser->visit('/accessories/edit/' . $accessory->id)
                ->assertSeeEditForm($browser, $accessory->name)
                ->type('@accessory-name-field', 'Updated Accessory Name')
                ->type('@accessory-description-field', 'Updated Description.')
                ->type('@accessory-price-field', '200.50')
                ->press('@submit-button')
                ->pause(3000) // Attendre la soumission et la redirection
                ->screenshot('accessory_updated');

            // Vérifier la redirection vers la liste et la présence du nom mis à jour
            $browser->assertPathIs('/accessories')
                    ->assertSee('Updated Accessory Name');

            echo "✅ Accessoire modifié avec succès\n";

            // Nettoyage
            $accessory->delete();
        });
    }

    /**
     * Test la suppression d'un accessoire (via la page liste)
     */
    public function test_can_delete_accessory()
    {
        $this->browse(function (Browser $browser) {
            echo "🗑️ Test suppression d'un accessoire...\n";

            $this->loginAsAdmin($browser);

            // Créer un accessoire à supprimer
            $accessory = \App\Models\Accessory::factory()->create([
                'name' => 'Accessory to Delete',
            ]);

            $browser->visit(new AccessoryPage)
                ->assertSee('Accessory to Delete')
                ->screenshot('before_delete');

            // Cliquer sur le bouton de suppression (supposons un bouton avec une classe 'delete-button' ou un lien)
            // Il faudra peut-être ajuster ce sélecteur en fonction de l'implémentation réelle
            $browser->click('button[data-id="' . $accessory->id . '"]') // Exemple: bouton avec data-id
                    ->pause(1000) // Attendre la confirmation si nécessaire
                    ->acceptDialog() // Accepter la boîte de dialogue de confirmation
                    ->pause(3000) // Attendre la suppression et le rechargement de la page
                    ->screenshot('accessory_deleted');

            // Vérifier que l'accessoire n'est plus visible
            $browser->assertDontSee('Accessory to Delete');

            echo "✅ Accessoire supprimé avec succès\n";
        });
    }
}
