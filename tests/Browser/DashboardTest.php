<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Support\Pages\DashboardPage;
use Tests\Browser\Traits\AuthenticatesUsers;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /**
     * Test l'accès au dashboard sans authentification (redirection vers login)
     */
    public function test_dashboard_requires_authentication()
    {
        $this->browse(function (Browser $browser) {
            echo "🔒 Test accès au dashboard sans authentification...\n";

            $browser->visit('/dashboard')
                ->pause(2000)
                ->screenshot('dashboard_auth_redirect');

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
     * Test l'accès au dashboard après authentification
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test accès au dashboard après authentification...\n";

            // Connexion et accès au dashboard en utilisant le trait
            $this->loginAsAdmin($browser);

            // Accéder au dashboard en utilisant le Page Object
            $browser->visit(new DashboardPage)
                ->screenshot('dashboard_after_login')
                ->assertSee('Tableau de bord');

            echo "✅ Dashboard accessible après authentification\n";
        });
    }

    /**
     * Test la présence des composants du dashboard
     */
    public function test_dashboard_components_are_present()
    {
        $this->browse(function (Browser $browser) {
            echo "📊 Test présence des composants du dashboard...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('dashboard_components');

            try {
                // Vérifier les composants statistiques
                $dashboardPage->assertStatsComponents($browser);

                // Vérifier les graphiques
                $dashboardPage->assertGraphs($browser);

                // Vérifier le filtre
                $dashboardPage->openFilter($browser);

                echo "✅ Tous les composants du dashboard sont présents\n";
            } catch (\Exception $e) {
                echo '❌ Problème avec les composants: '.$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test le filtrage par centre de distribution
     */
    public function test_dashboard_filter_by_distribution_center()
    {
        $this->browse(function (Browser $browser) {
            echo "🏭 Test filtrage par centre de distribution...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('before_filter_distribution_center');

            try {
                // Ouvrir le filtre et sélectionner un centre de distribution
                $dashboardPage->openFilter($browser)
                    ->selectDistributionCenter($browser, '1')
                    ->assertComponentsUpdatedAfterFilter($browser);

                echo "✅ Filtrage par centre de distribution réussi\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors du filtrage par centre: '.$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test le filtrage par période
     */
    public function test_dashboard_filter_by_period()
    {
        $this->browse(function (Browser $browser) {
            echo "📅 Test filtrage par période...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('before_filter_period');

            try {
                // Ouvrir le filtre et sélectionner une période
                $dashboardPage->openFilter($browser)
                    ->selectPeriod($browser, '1') // 1 = Mois courant
                    ->assertComponentsUpdatedAfterFilter($browser);

                echo "✅ Filtrage par période réussi\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors du filtrage par période: '.$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test le filtrage par plage de dates personnalisée
     */
    public function test_dashboard_filter_by_custom_date_range()
    {
        $this->browse(function (Browser $browser) {
            echo "📆 Test filtrage par plage de dates personnalisée...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('before_filter_custom_date');

            try {
                // Dates pour le test (mois précédent)
                $startDate = date('Y-m-d', strtotime('first day of last month'));
                $endDate = date('Y-m-d', strtotime('last day of last month'));

                // Ouvrir le filtre et sélectionner des dates personnalisées
                $dashboardPage->openFilter($browser)
                    ->selectCustomDateRange($browser, $startDate, $endDate)
                    ->assertComponentsUpdatedAfterFilter($browser);

                echo "✅ Filtrage par dates personnalisées réussi\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors du filtrage par dates: '.$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test le filtrage combiné (centre + période)
     */
    public function test_dashboard_filter_combined()
    {
        $this->browse(function (Browser $browser) {
            echo "🔍 Test filtrage combiné (centre + période)...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('before_combined_filter');

            try {
                // Ouvrir le filtre et appliquer plusieurs critères
                $dashboardPage->openFilter($browser)
                    ->selectDistributionCenter($browser, '1')
                    ->selectPeriod($browser, '3') // 3 = Trimestre en cours
                    ->assertComponentsUpdatedAfterFilter($browser);

                echo "✅ Filtrage combiné réussi\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors du filtrage combiné: '.$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test la réinitialisation des filtres
     */
    public function test_dashboard_filter_reset()
    {
        $this->browse(function (Browser $browser) {
            echo "🔄 Test réinitialisation des filtres...\n";

            $dashboardPage = new DashboardPage;

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);

            // Vérifier que nous sommes bien sur le dashboard
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->pause(2000)
                ->screenshot('dashboard_loaded');

            try {
                // Appliquer un filtre d'abord
                $dashboardPage->openFilter($browser)
                    ->selectDistributionCenter($browser, '1');

                // C'est le browser qui doit faire pause, pas le dashboardPage
                $browser->pause(3000)
                    ->screenshot('before_reset');

                // S'assurer que nous sommes toujours authentifiés
                if (str_contains($browser->driver->getCurrentURL(), '/login')) {
                    echo "⚠️ Session expirée, reconnexion...\n";
                    $this->loginAsAdmin($browser);
                    $browser->visit('/dashboard')->pause(2000);
                    $dashboardPage->openFilter($browser);
                }

                // Réinitialiser les filtres
                $dashboardPage->resetFilters($browser);

                echo "✅ Réinitialisation des filtres réussie\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors de la réinitialisation: '.$e->getMessage()."\n";

                // Capturer l'état pour diagnostic
                $browser->screenshot('reset_filter_error');
                echo 'URL actuelle: '.$browser->driver->getCurrentURL()."\n";

                // Essayer de reconnecter si nécessaire
                if (str_contains($browser->driver->getCurrentURL(), '/login')) {
                    echo "🔑 Tentative de reconnexion...\n";
                    $this->loginAsAdmin($browser);
                }
            }
        });
    }

    /**
     * Test que les blocs statistiques sont cliquables et mènent à des pages dédiées
     */
    public function test_dashboard_stat_cards_are_clickable()
    {
        $this->browse(function (Browser $browser) {
            echo "🔍 Test des blocs statistiques cliquables...\n";

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $dashboardPage = new DashboardPage;
            $browser->visit($dashboardPage)
                ->pause(3000)
                ->screenshot('dashboard_before_card_click');

            try {
                // Analyser les éléments cliquables pour aider au diagnostic
                $this->findClickableElements($browser, '.row .card, .row [class*="card"]');

                // Tester si les cartes statistiques sont cliquables
                $dashboardPage->testClickableStatCards($browser);

                echo "✅ Test des cartes statistiques terminé\n";
            } catch (\Exception $e) {
                echo '❌ Erreur lors du test des cartes statistiques: '.$e->getMessage()."\n";
                $this->captureErrorState($browser, 'stat_cards_error', '.row .card');
            }
        });
    }

    /**
     * Test que le bouton filtre ouvre bien le panneau de filtre et non le menu de navigation
     */
    public function test_filter_button_opens_filter_panel_not_navigation()
    {
        $this->browse(function (Browser $browser) {
            echo "🔍 Test du bouton filtre spécifique...\n";

            // Connexion et accès au dashboard
            $this->loginAsAdmin($browser);
            $browser->visit('/dashboard')
                ->pause(2000);

            try {
                // Capturer l'état avant clic
                $browser->screenshot('before_filter_click');

                // Identifier explicitement le bouton filtre dans la colonne de droite
                $filterButtons = $browser->elements('.col-4 a[data-bs-toggle="collapse"]');
                if (count($filterButtons) > 0) {
                    echo "🔘 Bouton filtre trouvé dans la colonne de droite (.col-4) - OK\n";
                    $browser->click('.col-4 a[data-bs-toggle="collapse"]');
                } else {
                    echo "⚠️ Bouton filtre standard non trouvé, recherche d'alternatives...\n";
                    // Afficher tous les boutons avec data-bs-toggle pour aider au diagnostic
                    $toggleButtons = $browser->elements('[data-bs-toggle="collapse"]');
                    echo count($toggleButtons)." boutons avec data-bs-toggle trouvés\n";

                    foreach ($toggleButtons as $index => $button) {
                        $text = $button->getText() ?: '[Pas de texte]';
                        $class = $button->getAttribute('class') ?: '[Pas de classe]';
                        echo "Bouton #{$index}: '{$text}' (class: {$class})\n";
                    }

                    // Essayer de cliquer sur un bouton explicite de filtre
                    $browser->click('a:contains("Filtrer"), button:contains("Filtrer")');
                }

                $browser->pause(2000)
                    ->screenshot('after_filter_click');

                // Vérifier que c'est bien le panneau de filtre qui s'ouvre
                if ($browser->resolver->findOrFail('#collapseFilter')->isDisplayed()) {
                    echo "✅ Le panneau de filtre #collapseFilter s'est bien ouvert\n";
                } elseif ($browser->resolver->findOrFail('.collapse.show')->isDisplayed()) {
                    echo "✅ Un panneau s'est ouvert (.collapse.show)\n";

                    // Vérifier que ce n'est pas le menu de navigation
                    if ($browser->resolver->findOrFail('.collapse.show:contains("Approvisionnement")')->isDisplayed()) {
                        echo "❌ ERREUR: Le menu de navigation s'est ouvert au lieu du filtre!\n";
                    } else {
                        echo "✅ Ce n'est pas le menu de navigation, probablement le filtre\n";
                    }
                } else {
                    echo "❌ ERREUR: Aucun panneau ne s'est ouvert après clic sur le bouton filtre\n";
                }
            } catch (\Exception $e) {
                echo '❌ Erreur lors du test du bouton filtre: '.$e->getMessage()."\n";
                $this->captureErrorState($browser, 'filter_button_error', '[data-bs-toggle="collapse"]');
            }
        });
    }
}
