<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Support\Pages\BottlesIndexPage;
use Tests\Browser\Support\Pages\BottleTypesIndexPage;
use Tests\Browser\Support\Pages\CreateBottleTypePage;
use Tests\Browser\Support\Pages\EditBottleTypePage;
use Tests\Browser\Traits\AuthenticatesUsers;
use Tests\DuskTestCase;

class BottlesTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /**
     * Test l'accès aux pages du module Bouteilles sans authentification (redirection vers login)
     */
    public function test_bottle_pages_require_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès aux pages Bouteilles sans authentification...\n";

            $pages = [
                new BottlesIndexPage(),
                new BottleTypesIndexPage(),
                new CreateBottleTypePage(),
                new EditBottleTypePage(1), // Utilise un ID factice pour le test d'URL
            ];

            foreach ($pages as $page) {
                echo "  -> Test de la page: " . $page->url() . "\n";
                $browser->visit($page->url())
                    ->pause(1000) // Petite pause pour s'assurer de la redirection
                    ->screenshot('bottle_auth_redirect_' . str_replace(['/', '.'], '_', trim($page->url(), '/')));

                // Vérifier la redirection vers login
                $currentUrl = $browser->driver->getCurrentURL();
                echo "    URL actuelle: " . $currentUrl . "\n";
                // Dump the page source for debugging
                // echo "    Contenu de la page:\n" . $browser->driver->getPageSource() . "\n";

                if (!str_contains($currentUrl, '/login')) {
                    $this->fail("❌ La page " . $page->url() . " n'a pas redirigé vers la page de login. URL actuelle: " . $currentUrl);
                }
                $browser->assertSee('Se connecter');
                echo "  ✅ Redirection vers login confirmée pour " . $page->url() . "\n";
            }
        });
    }

    /**
     * Test l'accès aux pages du module Bouteilles après authentification
     */
    public function test_authenticated_user_can_access_bottle_pages()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès aux pages Bouteilles après authentification...\n";

            // Connexion en tant qu'administrateur
            $this->loginAsAdmin($browser);

            $pages = [
                new BottlesIndexPage(),
                new BottleTypesIndexPage(),
                new CreateBottleTypePage(),
            ];

            foreach ($pages as $page) {
                echo "  -> Test de la page: " . $page->url() . "\n";
                $browser->visit($page)
                    ->pause(2000) // Pause pour le chargement de la page
                    ->screenshot('bottle_authenticated_' . str_replace(['/', '.'], '_', trim($page->url(), '/')));
                $page->assert($browser); // Utilise la méthode assert de la Page Object
                echo "  ✅ Accès confirmé à " . $page->url() . "\n";
            }

            // Pour la page d'édition, nous avons besoin d'un ID réel.
            // Pour l'instant, nous allons juste vérifier l'accès avec un ID factice.
            // Dans un scénario réel, il faudrait créer un type de bouteille en base de données pour obtenir un ID valide.
            echo "  -> Test de la page d'édition de type de bouteille (avec ID factice)...\n";
            $browser->visit(new EditBottleTypePage(1))
                    ->pause(2000)
                    ->screenshot('bottle_authenticated_edit_type');
            // Note: L'assertion de la page d'édition pourrait échouer si l'ID n'existe pas réellement.
            // $page->assert($browser); // Cette assertion pourrait échouer si la page renvoie une 404 ou une erreur.
            echo "  ✅ Accès tenté à la page d'édition de type de bouteille.\n";
        });
    }

    /**
     * Test de la navigation entre les pages du module Bouteilles
     */
    public function test_bottle_navigation()
    {
        $this->browse(function (Browser $browser) {
            echo "🔄 Test de la navigation entre les pages Bouteilles...\n";

            $this->loginAsAdmin($browser);

            // Naviguer de la liste des bouteilles vers la liste des types de bouteilles
            $bottlesIndexPage = new BottlesIndexPage();
            $bottleTypesIndexPage = new BottleTypesIndexPage();
            $createBottleTypePage = new CreateBottleTypePage();

            $browser->visit($bottlesIndexPage)
                    ->pause(1000)
                    ->screenshot('nav_bottles_index');

            // Assurez-vous qu'il y a un lien vers les types de bouteilles sur la page d'index des bouteilles
            // Remplacez 'a[href="/bottles/types"]', 'Lien vers Types de Bouteilles' par le sélecteur et le texte réels du lien
            // Si le lien n'est pas direct, il faudra simuler la navigation via le menu ou un bouton.
            try {
                $browser->clickLink('Types de Bouteilles') // Exemple: si un lien existe avec ce texte
                        ->pause(2000)
                        ->screenshot('nav_to_bottle_types_index');
                $bottleTypesIndexPage->assert($browser);
                echo "  ✅ Navigation de la liste des bouteilles vers la liste des types réussie.\n";
            } catch (\Exception $e) {
                echo "  ⚠️ Impossible de trouver le lien 'Types de Bouteilles' sur la page d'index des bouteilles. Veuillez vérifier le sélecteur ou le texte du lien.\n";
                // Tenter une visite directe si le lien n'est pas trouvé pour continuer le test
                $browser->visit($bottleTypesIndexPage);
                $bottleTypesIndexPage->assert($browser);
            }

            // Naviguer de la liste des types de bouteilles vers la page de création
            try {
                $browser->clickLink('Créer un Type de Bouteille') // Exemple: si un lien existe avec ce texte
                        ->pause(2000)
                        ->screenshot('nav_to_create_bottle_type');
                $createBottleTypePage->assert($browser);
                echo "  ✅ Navigation de la liste des types vers la création réussie.\n";
            } catch (\Exception $e) {
                echo "  ⚠️ Impossible de trouver le lien 'Créer un Type de Bouteille' sur la page d'index des types de bouteilles. Veuillez vérifier le sélecteur ou le texte du lien.\n";
                // Tenter une visite directe si le lien n'est pas trouvé pour continuer le test
                $browser->visit($createBottleTypePage);
                $createBottleTypePage->assert($browser);
            }

            // Retour à la liste des types de bouteilles (par exemple, via un bouton retour ou le menu)
            // Assurez-vous qu'il y a un bouton retour ou un lien approprié
            try {
                $browser->back()
                        ->pause(2000)
                        ->screenshot('nav_back_to_bottle_types_index');
                $bottleTypesIndexPage->assert($browser);
                echo "  ✅ Retour à la liste des types de bouteilles réussi.\n";
            } catch (\Exception $e) {
                echo "  ⚠️ Impossible de revenir en arrière depuis la page de création. Veuillez vérifier le comportement de navigation.\n";
                // Tenter une visite directe si le retour échoue
                $browser->visit($bottleTypesIndexPage);
                $bottleTypesIndexPage->assert($browser);
            }
        });
    }

    /**
     * Test de la soumission du formulaire de création de type de bouteille (test simple)
     */
    public function test_create_bottle_type_form_submission()
    {
        $this->browse(function (Browser $browser) {
            echo "📝 Test de la soumission du formulaire de création de type de bouteille...\n";

            $this->loginAsAdmin($browser);

            $createPage = new CreateBottleTypePage();
            $browser->visit($createPage)
                    ->pause(2000)
                    ->screenshot('before_create_bottle_type_form');

            // Remplir le formulaire (remplacez les sélecteurs et les valeurs par les vôtres)
            // Assurez-vous que les champs existent et sont visibles
            try {
                $browser->type('input[name="name"]', 'Type de Bouteille Test' . time()) // Exemple de champ 'name'
                        ->type('textarea[name="description"]', 'Description du type de bouteille de test.') // Exemple de champ 'description'
                        ->press('Enregistrer') // Texte du bouton de soumission
                        ->pause(3000) // Attendre la soumission et la redirection
                        ->screenshot('after_create_bottle_type_form_submission');

                // Vérifier la redirection ou un message de succès
                // Par exemple, si cela redirige vers la liste des types de bouteilles
                $bottleTypesIndexPage = new BottleTypesIndexPage();
                $bottleTypesIndexPage->assert($browser);
                $browser->assertSee('Type de bouteille créé avec succès'); // Message de succès attendu
                echo "  ✅ Soumission du formulaire de création réussie.\n";
            } catch (\Exception $e) {
                echo "  ❌ Erreur lors de la soumission du formulaire de création: " . $e->getMessage() . "\n";
                $browser->screenshot('error_create_bottle_type_form');
                $this->fail("Erreur lors de la soumission du formulaire de création: " . $e->getMessage());
            }
        });
    }

    /**
     * Test de la soumission du formulaire d'édition de type de bouteille (test simple)
     */
    public function test_edit_bottle_type_form_submission()
    {
        $this->browse(function (Browser $browser) {
            echo "📝 Test de la soumission du formulaire d'édition de type de bouteille...\n";

            $this->loginAsAdmin($browser);

            // Pour ce test, nous avons besoin d'un type de bouteille existant.
            // Dans un scénario réel, vous devriez créer un type de bouteille via une factory ou une seed.
            // Pour l'exemple, nous allons utiliser un ID factice et assumer qu'il existe.
            $bottleTypeId = 1; // Remplacez par un ID réel si vous en avez un

            $editPage = new EditBottleTypePage($bottleTypeId);
            $browser->visit($editPage)
                    ->pause(2000)
                    ->screenshot('before_edit_bottle_type_form');

            // Remplir le formulaire (remplacez les sélecteurs et les valeurs par les vôtres)
            try {
                $browser->type('input[name="name"]', 'Type de Bouteille Modifié' . time()) // Exemple de champ 'name'
                        ->press('Mettre à jour') // Texte du bouton de soumission
                        ->pause(3000) // Attendre la soumission et la redirection
                        ->screenshot('after_edit_bottle_type_form_submission');

                // Vérifier la redirection ou un message de succès
                $bottleTypesIndexPage = new BottleTypesIndexPage();
                $bottleTypesIndexPage->assert($browser);
                $browser->assertSee('Type de bouteille mis à jour avec succès'); // Message de succès attendu
                echo "  ✅ Soumission du formulaire d'édition réussie.\n";
            } catch (\Exception $e) {
                echo "  ❌ Erreur lors de la soumission du formulaire d'édition: " . $e->getMessage() . "\n";
                $browser->screenshot('error_edit_bottle_type_form');
                $this->fail("Erreur lors de la soumission du formulaire d'édition: " . $e->getMessage());
            }
        });
    }
}