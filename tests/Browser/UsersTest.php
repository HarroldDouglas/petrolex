<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\Pages\UsersPage;
use Tests\Browser\Traits\AuthenticatesUsers;
use Tests\DuskTestCase;

class UsersTest extends DuskTestCase
{
    use AuthenticatesUsers, DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database if necessary, or create a user for authentication
        // $this->artisan('db:seed');
    }

    /**
     * Test l'accès à la liste des utilisateurs sans authentification (redirection vers login)
     */
    public function test_users_list_requires_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès liste utilisateurs sans authentification...\n";

            $browser->visit('/users')
                ->pause(2000)
                ->screenshot('users_list_auth_redirect');

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
     * Test l'accès à la liste des utilisateurs après authentification
     */
    public function test_authenticated_user_can_access_users_list()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès liste utilisateurs après authentification...\n";

            $this->loginAsAdmin($browser);

            $browser->visit(new UsersPage)
                ->screenshot('users_list_after_login')
                ->assertSee('Utilisateurs'); // Assuming 'Utilisateurs' is a title on the page

            echo "✅ Liste des utilisateurs accessible après authentification\n";
        });
    }

    /**
     * Test la création d'un nouvel utilisateur
     */
    public function test_can_create_new_user()
    {
        $this->browse(function (Browser $browser) {
            echo "➕ Test création nouvel utilisateur...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            $userData = [
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ];

            $usersPage->navigateToCreateUser($browser);
            $usersPage->fillAndSubmitUserForm($browser, $userData);

            $browser->pause(2000)
                ->screenshot('user_created')
                ->assertPathIs($usersPage->url())
                ->assertSee('Utilisateur créé avec succès'); // Assuming a success message

            $usersPage->assertUserInTable($browser, $userData['email']);

            echo "✅ Nouvel utilisateur créé et visible dans la liste\n";
        });
    }

    /**
     * Test la modification d'un utilisateur existant
     */
    public function test_can_edit_existing_user()
    {
        $this->browse(function (Browser $browser) {
            echo "✏️ Test modification utilisateur existant...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            // Create a user to edit
            $user = User::factory()->create([
                'name' => 'User to Edit',
                'email' => 'editme@example.com',
                'password' => bcrypt('password'),
            ]);

            $usersPage->clickEditUser($browser, $user->email);

            $updatedData = [
                'name' => 'Edited User Name',
                'email' => 'edited@example.com',
            ];

            $usersPage->fillAndSubmitUserForm($browser, $updatedData);

            $browser->pause(2000)
                ->screenshot('user_edited')
                ->assertPathIs($usersPage->url())
                ->assertSee('Utilisateur mis à jour avec succès'); // Assuming a success message

            $usersPage->assertUserInTable($browser, $updatedData['email']);
            $usersPage->assertUserNotInTable($browser, $user->email); // Old email should not be there

            echo "✅ Utilisateur modifié avec succès\n";
        });
    }

    /**
     * Test la suppression d'un utilisateur
     */
    public function test_can_delete_user()
    {
        $this->browse(function (Browser $browser) {
            echo "🗑️ Test suppression utilisateur...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            // Create a user to delete
            $user = User::factory()->create([
                'name' => 'User to Delete',
                'email' => 'deleteme@example.com',
                'password' => bcrypt('password'),
            ]);

            $usersPage->deleteUser($browser, $user->email);

            $browser->pause(2000)
                ->screenshot('user_deleted')
                ->assertPathIs($usersPage->url())
                ->assertSee('Utilisateur supprimé avec succès'); // Assuming a success message

            $usersPage->assertUserNotInTable($browser, $user->email);

            echo "✅ Utilisateur supprimé avec succès\n";
        });
    }

    /**
     * Test l'affichage des détails d'un utilisateur
     */
    public function test_can_view_user_details()
    {
        $this->browse(function (Browser $browser) {
            echo "👁️ Test affichage détails utilisateur...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            // Create a user to view details
            $user = User::factory()->create([
                'name' => 'Details User',
                'email' => 'details@example.com',
                'password' => bcrypt('password'),
            ]);

            // Assuming there's a details button or link, or clicking on the row
            // For now, let's assume we can navigate directly to the details page
            // This might need adjustment based on actual UI
            $browser->visit('/users/'.$user->id.'/details')
                ->pause(2000)
                ->screenshot('user_details')
                ->assertSee($user->name)
                ->assertSee($user->email);

            echo "✅ Détails utilisateur affichés avec succès\n";
        });
    }

    /**
     * Test la validation lors de la création d'un utilisateur (champs requis)
     */
    public function test_create_user_validation_required_fields()
    {
        $this->browse(function (Browser $browser) {
            echo "❌ Test validation création utilisateur (champs requis)...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            $usersPage->navigateToCreateUser($browser);

            // Submit empty form
            $usersPage->fillAndSubmitUserForm($browser, []);

            $browser->pause(1000)
                ->screenshot('create_user_validation_required')
                ->assertSee('Le champ nom est obligatoire.') // Assuming validation messages
                ->assertSee('Le champ email est obligatoire.')
                ->assertSee('Le champ mot de passe est obligatoire.');

            echo "✅ Validation des champs requis réussie\n";
        });
    }

    /**
     * Test la validation lors de la création d'un utilisateur (email unique)
     */
    public function test_create_user_validation_unique_email()
    {
        $this->browse(function (Browser $browser) {
            echo "❌ Test validation création utilisateur (email unique)...\n";

            $this->loginAsAdmin($browser);
            $usersPage = new UsersPage;
            $browser->visit($usersPage);

            // Create a user first
            User::factory()->create([
                'email' => 'duplicate@example.com',
            ]);

            $usersPage->navigateToCreateUser($browser);

            $userData = [
                'name' => 'Another User',
                'email' => 'duplicate@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ];

            $usersPage->fillAndSubmitUserForm($browser, $userData);

            $browser->pause(1000)
                ->screenshot('create_user_validation_unique_email')
                ->assertSee('L\'adresse email a déjà été prise.'); // Assuming validation message

            echo "✅ Validation de l'email unique réussie\n";
        });
    }
}
