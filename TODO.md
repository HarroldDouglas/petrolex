Pour chaque tâche, tu dois absolument agir en tant que senior! et pour chaque tâche, tu créeras une branche nommée claude-nomdelafeature, puis tu checkout, dès que tu as fini, tu commit, puis tu reviens sur la dev avant de prendre la prochaine tâche, ainsi de suite! sauf si une tâche a besoin dune autre, c là où tu peux checkout à partir de la branche en question! poru chaque feature il faut mettre à jour les tests si necessaire. 
Je compte sur toi pr agir comme un senior, garde nos standards, code bien propre stp, pas de commentaires en français uniquement en anglais si important, SOLID principles, logique simple et comprehensible, code lisible et optimisé, testable! Pour chaque tâche, il faut executer phpstan


## Dans BottleProduct, changé bottle_with_content_price par full_price!

## il faut un endpoint qui va renvoyer les banières publicitaires! actu tu vas faire un peu comme on a fait pr les termes et conditions, créé un fichier de config, définir des urls statiques pr le moment pr 2 banières pr le moment, et faire un endpoint selon nos normes pr retourner ça proprement, inspire toi de nos standards! puis redige le test endpoint fonctionnel, exactemnet coe les autres

## ajouter dans le endpoint qui renvoie les types de livraison, les frais, nous avons ça quelque part, soit dans lenum, soit dans la bd, faut verifier!

## Ajouter un champ category sur chaque produit portant un libellé plus description de la catégorie qui sera affichée sur la page d'accueil! le type (bottle ou accessory) n'est pas suffisant, il faut un champs de plus qui sera un label utilisé depuis le mobile, tu pourras le mettre depuislenum, c le label qui sera utilisé côté mobile, tu mettras "Bouteilles à gaz domestiques" et "Accessoires de sécurité et distributions"

## il faut t'assurer que pour les villes en bd, on les retourne avec leur quartier et tous les détails d'un quartier!

## L'Endpoint de récupération d'un centre de distribution devrait renvoyé directement les produits de ce centre de distribution tout en conservant le Endpoint qui renvoi les produits pour un centre de distribution donnée car il sera utile aussi. donc tu vas juste mettre à jour le endpoint d'un centre de distribution

## le endpoint qui renvoie le closest centre de distribution devrait pouvoir recevoir aussi l'id d'un quartier, donc ça peut recevoir les coordonnées de géolocalisation ou l'id d'un quartier! ensuite on verifie s'il ya des centres de distribution qui snot dans la mm municipalité que ce quartier ou ses coordonnées, puis on cherche le plus proche tout simplement!

## nous devons modifier la bd pr que l'api retourne les produits de façon plus flexible, genre on aura un champs specificités, qui sera un array qui aura la clé nom et valeur! par exemple dans le cas des bouteilles, specificités sera: [ "height"(on va devoir traduire car ce name sera directement affiché cote mobile) --- 12, "weight" ---- 9KG]! on devra faire ça pr les bouteilles et les accessoires, je sens que ça peut impliquer de bien modifier la bd pr rendre les éléments coe ça très flexible, mais pas le choix, tu vas le faire proprement et partout! 

## Verifier un peu la logique si un produit est en rupture, est ce que c'est renvoyé ou bien ?

## [CRITIQUE] Implémenter la validation de stock à la création de commande
La méthode `validateStock` dans `CreateOrderRequest::withValidator()` est commentée (ligne 88). Aucune vérification de stock n'est faite : on peut commander 50 régulateurs alors qu'il n'y en a que 30 en stock. Il faut :
- Implémenter `validateStock()` dans `app/Http/Api/Requests/Order/CreateOrderRequest.php`
- Pour chaque item, vérifier le stock dans la table pivot `product_category_distribution_center`
- Pour les bouteilles : vérifier `stock_filled` (bottle_with_content) ou `stock` (content/recharge)
- Pour les accessoires : vérifier `stock`
- Décommenter l'appel `$this->validateStock($validator)` ligne 88
- Annuler les 2 commandes de test créées (CMD-202602-0016 et CMD-202602-0017)

## Créer l'interface web pour le retour des bouteilles vides
Le backend existe déjà (API: `POST /api/orders/{order}/scan-empty-bottle`, service: `OrderService::handleEmptyBottleReturn()`). Il manque l'interface web (Livewire) pour que le responsable du centre puisse scanner/saisir manuellement les bouteilles vides retournées par le livreur.
- Créer un composant Livewire avec scan (Quagga) + saisie manuelle (comme `OrderScanBottles`)
- Ajouter un bouton "Lier les bouteilles vides" sur la page détails de la commande (à côté de "Lier des bouteilles pour la livraison")
- L'action ne doit s'afficher QUE si les bouteilles vides n'ont pas déjà été liées (vérifier si `empty_bottle_id` est null sur les `OrderBottleScans` de la commande). Si le livreur l'a déjà fait via l'app mobile, le bouton ne doit pas apparaître.
