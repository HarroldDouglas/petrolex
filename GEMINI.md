# Principes de Développement - Instructions Génériques

## ⚠️ RÈGLES ABSOLUES - NON NÉGOCIABLES

### 1. INTERDICTIONS CATÉGORIQUES
- ❌ **AUCUN commentaire inutile** : Pas de "TODO", "FIXME", "variable renommée", "code ajouté", etc.
- ❌ **AUCUNE hallucination** : Ne JAMAIS inventer de classes, méthodes ou propriétés inexistantes
- ❌ **AUCUNE initiative non demandée** : Faire UNIQUEMENT ce qui est explicitement demandé
- ❌ **AUCUN code déprécié** : Toujours utiliser les versions actuelles et bonnes pratiques
- ❌ **AUCUN path complet** : Importer avec `use`, jamais de `\App\Models\User` dans le code

### 2. OBLIGATIONS PRÉALABLES
```markdown
AVANT CHAQUE CODE, OBLIGATOIREMENT :
1. 🔍 ANALYSER le code existant pour comprendre les patterns
2. 🔍 VÉRIFIER l'existence réelle de toutes les classes utilisées
3. 🔍 IDENTIFIER les conventions de nommage du projet
4. 🔍 COMPRENDRE l'architecture en place
5. ✅ CONFIRMER ma compréhension de la demande

3. PRINCIPES DE DÉVELOPPEMENT SENIOR
Code Propre & Architecture
Fonctions courtes : Maximum 15-20 lignes par méthode
Noms explicites : $userRepository au lieu de $repo ou $data
Une responsabilité : Une classe/méthode = Un objectif précis
Imports propres : Toujours use ClassName puis utiliser ClassName
Typage strict : Toujours typer paramètres et retours
Principes SOLID (Obligatoires)
S - Single Responsibility : Une classe = Une raison de changer
O - Open/Closed : Ouvert à l'extension, fermé à la modification
L - Liskov Substitution : Les sous-classes doivent être substituables
I - Interface Segregation : Interfaces spécifiques plutôt que générales
D - Dependency Inversion : Dépendre d'abstractions, pas de concrétions
Design Patterns & Bonnes Pratiques
Repository Pattern : Pour l'accès aux données
Service Pattern : Pour la logique métier
Factory Pattern : Pour la création d'objets complexes
Strategy Pattern : Pour les algorithmes interchangeables
DRY : Ne pas répéter le code
KISS : Simplicité avant tout
4. STRUCTURE DE RÉPONSE OBLIGATOIRE
## Analyse du Code Existant
- Pattern identifié : [Repository/Service/etc.]
- Classes trouvées : [liste vérifiée]
- Conventions : [nommage, structure]

## Solution Proposée
- Approche : [justification technique]
- Design pattern utilisé : [lequel et pourquoi]

## Code
[Code clean sans commentaires inutiles]

## Validation
Dois-je procéder à autre chose ou avez-vous des ajustements ?

5. QUALITÉ CODE NIVEAU SENIOR
Structure Type Attendue
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Contracts\UserRepositoryInterface;
use App\Exceptions\UserNotFoundException;

final class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function findUserById(int $userId): User
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new UserNotFoundException("User with ID {$userId} not found");
        }
        
        return $user;
    }
}

6. COMMENTAIRES AUTORISÉS (Rare)
/**
 * Complex business logic explanation - WHY, not WHAT
 * Used only for non-obvious business rules
 */

// Only for complex algorithms where the business logic isn't obvious

7. PROCESS DE VALIDATION AUTO
Avant de répondre, JE DOIS vérifier :
✅ Toutes mes classes existent-elles réellement ?
✅ Ai-je respecté les patterns existants ?
✅ Mon code respecte-t-il SOLID ?
✅ Ai-je éliminé tous commentaires inutiles ?
✅ Mes imports sont-ils propres ?
✅ Mon code est-il digne d'un senior ?
✅ Fais-je UNIQUEMENT ce qui est demandé ?

8. EXEMPLES INTERDITS vs AUTORISÉS
❌ CODE INACCEPTABLE
// J'ai créé cette méthode pour gérer les utilisateurs
public function handleUser($data) // Pas de typage
{
    $user = new \App\Models\User(); // Path complet
    $user->name = $data['name']; // Pas de validation
    return $user;
}

✅ CODE ATTENDU
use App\Models\User;
use App\DTOs\CreateUserDTO;

public function createUser(CreateUserDTO $userData): User
{
    return User::create([
        'name' => $userData->name,
        'email' => $userData->email,
    ]);
}


9. GESTION D'ERREURS
Toujours valider les entrées
Lancer des exceptions spécifiques
Gérer les cas d'erreur explicitement
Ne jamais ignorer les erreurs silencieusement
10. PERFORMANCE & SÉCURITÉ
Éviter les N+1 queries
Utiliser les relations Eloquent appropriées
Valider et assainir toutes les entrées
Appliquer le principe du moindre privilège
CONSÉQUENCE DU NON-RESPECT
Le non-respect de ces règles entraînera le rejet de la réponse et la demande de refaire en respectant ces standards.

Objectif Final : Produire un code de qualité production, maintenable, testable et digne d'un développeur senior expérimenté.

TU dois strictement suivre et rigoureusement suivre les standards actuels, si je te donne une tâche sur une vue, avant de créer une vue explore les autres vues et comprend quel layout on utilise! 
pr créer un controller explore les autres controllers et comprend comment on fonctionne! 
pareil pour les autres classes et tout! tout est important , il faut dabord analyser lexistant avant toute chose


    Lorsque je te donne une tâche à faire, tu as obligation stricte de voir comment on fait dans le code avant de faire, c'est        │
    │   non négociable pour éviter dhalluciner! si tu dois créer une vue, tu dois voir comment les autres vues sont dans le système       │
    │   (environ 2) et voir quel layout par exemple est utilisé pour éviter de me sortir des trucs qui nexistent pas, voir les            │
    │   standards, que ce soit pour le nommage, le formattage, et tout autre!