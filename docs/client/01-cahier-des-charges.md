# Isogaz — Cahier des charges fonctionnel

**Projet :** Isogaz (Petrolex) — plateforme de distribution de gaz domestique
**Périmètre :** Cameroun (Douala, extensible multi-villes)
**Version du document :** 1.0 — 18/08/2026
**Version applicative de référence :** v1.7.0

---

## 1. Objet du projet

Isogaz digitalise la chaîne complète de distribution de bouteilles de gaz domestique :
de la réception des bouteilles chez le fournisseur jusqu'à la livraison au client final,
en incluant le paiement Mobile Money et la traçabilité individuelle de chaque bouteille consignée.

La plateforme se compose de trois produits :

| Produit | Utilisateurs | Technologie |
|---|---|---|
| **Application mobile Client** | Particuliers, ménages | Flutter (Android / iOS) |
| **Application mobile Livreur / Manager** | Livreurs, gestionnaires de centre | Flutter (Android) |
| **Panneau d'administration web** | Direction, administrateurs, comptables | Laravel + Livewire (`isogaz.net`) |

Le tout s'appuie sur une **API REST unique** qui centralise la logique métier.

---

## 2. Problème métier adressé

La distribution de gaz au Cameroun repose sur un modèle de **consigne** : le client
n'achète pas la bouteille, il l'échange. Cela impose de savoir en permanence :

1. **Où est chaque bouteille physique** — chez le fournisseur, en centre, chez un livreur, chez un client ?
2. **Combien coûte le gaz dans cette ville** — les prix varient d'une ville à l'autre.
3. **Le paiement est-il réellement encaissé** — les passerelles Mobile Money sont asynchrones.
4. **La livraison a-t-elle eu lieu** — et la bouteille vide a-t-elle bien été récupérée ?

Ces quatre questions structurent l'ensemble des exigences ci-dessous.

---

## 3. Acteurs du système

| Rôle | Code système | Accès | Responsabilités |
|---|---|---|---|
| **Super Administrateur** | `SUPER_ADMIN` | Web (tout) | Configuration globale, gestion des rôles et permissions |
| **Administrateur** | `ADMIN` | Web | Gestion des centres, produits, utilisateurs, commandes |
| **Gestionnaire** | `MANAGER` | Web | Supervision opérationnelle multi-centres |
| **Gestionnaire de centre** | `CENTER_MANAGER` | Web + Mobile | Pilotage d'un centre : stock, approvisionnements, affectation des livreurs |
| **Responsable gaz** | `GAS_MANAGER` | Web | Gestion du parc de bouteilles et des mouvements |
| **Comptable** | `ACCOUNTANT` | Web | Consultation des paiements, rapports financiers |
| **Livreur** | `DELIVERY_PERSON` | Mobile Livreur | Prise en charge, livraison, scans, encaissement |
| **Client** | `CUSTOMER` | Mobile Client | Commande, paiement, suivi, historique |

> **Règle de sécurité structurante :** le panneau web est **strictement réservé aux rôles staff**.
> Un compte client authentifié ne peut en aucun cas atteindre une page d'administration.

---

## 4. Objets métier

### 4.1 Géographie
Hiérarchie à quatre niveaux : **Pays → Ville → Commune (municipalité) → Quartier**.
Chaque quartier porte obligatoirement des coordonnées GPS (latitude/longitude) — elles
alimentent le calcul du centre de distribution le plus proche et la carte de suivi.

### 4.2 Centre de distribution
Point de stockage et de départ des livraisons. Rattaché à un quartier, donc à une ville.
Porte son propre stock de bouteilles, son catalogue de produits actifs et ses livreurs affectés.

### 4.3 Produit
Deux natures de produits :
- **Bouteille** (`BOTTLE`) — déclinée par type : **9 Kg** (référence), 12 Kg, 15 Kg…
  Deux modes de vente :
  - **Complète** (`FULL`) — le client n'a pas de bouteille, il paie la consigne + le gaz.
  - **Recharge** (`RECHARGE`) — le client échange sa bouteille vide, il ne paie que le gaz.
- **Accessoire** (`ACCESSORY`) — détendeurs, tuyaux, réchauds, etc.

### 4.4 Bouteille physique
Chaque bouteille est un objet unique identifié par un **code-barres**. Elle possède un état
à tout instant :

| Statut | Signification |
|---|---|
| `IN_STOCK` | En stock dans un centre |
| `WITH_DELIVERY_PERSON` | Chargée par un livreur, en tournée |
| `WITH_CLIENT` | Consignée chez un client |
| `PENDING_RECEPTION` | Annoncée par le fournisseur, pas encore scannée |
| `RETURNED_TO_SUPPLIER` | Renvoyée au fournisseur pour remplissage |
| `LOST_STOLEN` | Perdue ou volée |

### 4.5 Commande
Une commande regroupe une ou plusieurs lignes (produits), est rattachée à un client, une
adresse de livraison et un centre de distribution. Cycle de vie :

`PENDING` → `PAID` → `PROCESSING` → `DELIVERED`
avec les sorties possibles `CANCELLED` et `FAILED`.

### 4.6 Paiement
Un paiement est rattaché à une commande. Méthodes supportées :
`MTN_MONEY`, `ORANGE_MONEY`, `WALLET` (portefeuille interne), `CREDIT_CARD`.
États : `PENDING` → `PROCESSING` → `PAID` / `FAILED` / `REFUNDED`.

### 4.7 Portefeuille client (Wallet)
Solde interne crédité notamment lors des remboursements (annulation de commande).
Il est **consommé automatiquement** à la création d'une commande :
- si le solde couvre la totalité → la commande passe directement en `PAID` ;
- s'il ne couvre qu'une partie → le reste est réclamé via Mobile Money.

### 4.8 Approvisionnement (Supply)
Réception d'un lot de bouteilles pleines depuis le fournisseur vers un centre.
États : `IN_PROGRESS` → `COMPLETED` / `CANCELLED`.

---

## 5. Cas d'utilisation

### 5.1 Client mobile

| # | Cas d'utilisation | Description |
|---|---|---|
| UC-C01 | **S'inscrire** | Création de compte avec numéro de téléphone, vérification par code OTP |
| UC-C02 | **Se connecter** | Connexion par téléphone/mot de passe + OTP ; renvoi d'OTP possible |
| UC-C03 | **Réinitialiser son mot de passe** | Procédure « mot de passe oublié » |
| UC-C04 | **Gérer son profil** | Modifier ses informations, changer son mot de passe |
| UC-C05 | **Gérer ses adresses de livraison** | Créer et modifier plusieurs adresses rattachées à un quartier |
| UC-C06 | **Consulter le catalogue** | Voir les produits disponibles dans le centre le plus proche, **au prix de sa ville** |
| UC-C07 | **Localiser un centre** | Trouver le centre de distribution le plus proche de son adresse |
| UC-C08 | **Passer une commande** | Choisir produits, quantité, mode (complète / recharge), type de livraison |
| UC-C09 | **Choisir le mode de livraison** | Livraison **normale** ou **express** (frais différenciés) |
| UC-C10 | **Payer sa commande** | MTN MoMo, Orange Money ou solde du portefeuille |
| UC-C11 | **Suivre sa livraison** | Position du livreur en temps réel sur carte (WebSocket) |
| UC-C12 | **Annuler une commande** | Tant que la livraison n'a pas démarré ; remboursement sur le portefeuille |
| UC-C13 | **Consulter son historique** | Liste des commandes passées et leur statut |
| UC-C14 | **Télécharger sa facture** | Facture PDF de la commande |
| UC-C15 | **Noter la livraison** | Laisser un commentaire / une évaluation après livraison |
| UC-C16 | **Consulter les CGU et la politique de confidentialité** | Pages légales servies par l'API |
| UC-C17 | **Contacter le support** | Coordonnées de contact |
| UC-C18 | **Demander la suppression de son compte** | Conformité stores (Google Play / App Store) |

### 5.2 Livreur mobile

| # | Cas d'utilisation | Description |
|---|---|---|
| UC-L01 | **Se connecter** | Authentification dédiée au canal livreur |
| UC-L02 | **Consulter ses commandes affectées** | Liste des livraisons du jour |
| UC-L03 | **Voir le détail d'une commande** | Client, adresse, produits, montant, mode de paiement |
| UC-L04 | **Démarrer la livraison** | Bascule en tournée, activation du suivi GPS |
| UC-L05 | **Transmettre sa position** | Envoi périodique des coordonnées, diffusé au client |
| UC-L06 | **Scanner la bouteille vide reprise** | Enregistrement de la consigne récupérée chez le client |
| UC-L07 | **Clôturer la livraison** | Marquage `DELIVERED`, fin du suivi |
| UC-L08 | **Vérifier une bouteille** | Consultation de l'état d'une bouteille par son code-barres |

### 5.3 Gestionnaire de centre (mobile + web)

| # | Cas d'utilisation | Description |
|---|---|---|
| UC-M01 | **Consulter les approvisionnements** | Lots attendus et en cours de réception |
| UC-M02 | **Scanner les bouteilles reçues** | Réception physique, une bouteille ne peut être reçue deux fois |
| UC-M03 | **Retirer une bouteille d'un approvisionnement** | Correction d'erreur de scan |
| UC-M04 | **Superviser les commandes du centre** | Vue temps réel de l'activité |
| UC-M05 | **Affecter un livreur à une commande** | Attribution manuelle |
| UC-M06 | **Scanner les bouteilles pleines confiées au livreur** | Contrôle de sortie du dépôt — réservé à l'encadrement, un livreur ne peut pas l'effectuer |
| UC-M07 | **Clôturer un approvisionnement** | Action manuelle qui fait entrer les bouteilles en stock |
| UC-M08 | **Suivre les livreurs en temps réel** | Position des tournées en cours sur carte |

### 5.4 Administration web

| # | Cas d'utilisation | Description |
|---|---|---|
| UC-A01 | **Tableau de bord** | Indicateurs : commandes, chiffre d'affaires, parc bouteilles, livraisons |
| UC-A02 | **Gérer la géographie** | Communes et quartiers (avec coordonnées GPS obligatoires) |
| UC-A03 | **Gérer les centres de distribution** | Création, produits actifs, livreurs rattachés |
| UC-A04 | **Gérer le catalogue** | Catégories, types de bouteilles, accessoires |
| UC-A05 | **Gérer les prix par ville** | Surcharge tarifaire ville par ville sur chaque produit |
| UC-A06 | **Gérer les utilisateurs** | Comptes staff, activation, désactivation |
| UC-A07 | **Gérer les rôles et permissions** | Attribution fine des droits (~50 permissions unitaires) |
| UC-A08 | **Gérer les approvisionnements** | Création des lots, suivi des réceptions |
| UC-A09 | **Gérer le parc de bouteilles** | Inventaire, historique des mouvements de chaque bouteille |
| UC-A10 | **Suivre les commandes** | Recherche, détail, annulation, réaffectation, impression de ticket |
| UC-A11 | **Suivre les livraisons en temps réel** | Carte des livreurs en tournée |
| UC-A12 | **Consulter les rapports** | Rapport de transactions, statistiques par période et par centre |
| UC-A13 | **Consulter les journaux mobiles** | Erreurs remontées par les applications Flutter |
| UC-A14 | **Piloter les versions d'application** | Version minimale requise par plateforme, mise à jour forcée |
| UC-A15 | **Gérer les bannières publicitaires** | Contenus promotionnels affichés dans l'app cliente |

---

## 6. Règles de gestion

| # | Règle |
|---|---|
| RG-01 | **Le prix affiché au client est celui de sa ville.** Le système lit d'abord la table des prix par ville ; à défaut il retombe sur le prix de base du type de bouteille. La ville est déduite du centre : centre → quartier → commune → ville. |
| RG-02 | **Le prix validé à la commande doit être celui affiché.** Toute divergence est rejetée côté serveur. |
| RG-02b | **Les types de bouteilles sont définis globalement**, mais un produit n'est proposé au client que si le centre est autorisé à le vendre **et** qu'il en a le stock physique, issu des approvisionnements. |
| RG-03 | **Toutes les lignes d'une commande doivent relever de la même commune.** Pas de commande éclatée géographiquement. |
| RG-04 | **Le portefeuille est prioritaire.** Il est consommé automatiquement à la création de la commande ; le reliquat seul part en Mobile Money. |
| RG-05 | **Un paiement Mobile Money n'est jamais validé sur simple retour de la passerelle.** La confirmation passe par un contrôle actif du statut auprès de l'opérateur. |
| RG-06 | **Une bouteille ne peut pas être réceptionnée dans deux approvisionnements simultanément.** |
| RG-06b | **Le scan des bouteilles pleines sortantes est réservé à l'encadrement** (gestionnaire de centre, responsable gaz, gestionnaire, administrateur). Un livreur ne peut pas se servir lui-même : c'est le contrôle de sortie du dépôt. |
| RG-06c | **La clôture d'un approvisionnement est une action manuelle.** C'est elle qui fait passer les bouteilles reçues en stock disponible ; tant qu'elle n'est pas faite, le stock n'est pas vendable. |
| RG-06d | **Un livreur peut être rattaché à plusieurs centres**, chaque rattachement pouvant être activé ou désactivé indépendamment sans perdre l'historique. |
| RG-07 | **Une bouteille pleine ne peut pas être scannée comme bouteille vide reprise**, et une même bouteille vide ne peut pas être associée deux fois à la même ligne de commande. |
| RG-08 | **Une bouteille inconnue scannée comme reprise est créée automatiquement** dans le stock du centre de la commande, en statut « en stock, vide ». |
| RG-09 | **Une commande n'est annulable que tant que la livraison n'a pas démarré.** Le remboursement est crédité sur le portefeuille client. |
| RG-10 | **Les montants MTN sont transmis en entier sans décimale** — le franc CFA n'a pas de centimes. |
| RG-11 | **Toute donnée géographique exposée au mobile doit être non nulle** — les applications Flutter sont fortement typées et échouent sur une valeur nulle inattendue. |
| RG-12 | **Le panneau web est fermé aux clients.** Seuls les rôles staff y accèdent. |
| RG-13 | **La connexion et la vérification OTP sont limitées en fréquence** pour prévenir les attaques par force brute. |
| RG-14 | **Un client ne peut consulter que ses propres ressources.** L'accès transverse est réservé au staff. |

---

## 7. Exigences non fonctionnelles

| Domaine | Exigence |
|---|---|
| **Disponibilité** | Service accessible 24/7 ; interruptions limitées aux fenêtres de déploiement (mode maintenance, quelques dizaines de secondes) |
| **Performance** | Réponses API sous la seconde en usage nominal ; suivi GPS diffusé en temps réel via WebSocket |
| **Sécurité** | Authentification par jeton (Laravel Sanctum), OTP, limitation de fréquence, cloisonnement des données par rôle |
| **Traçabilité** | Historique complet des mouvements de chaque bouteille et de chaque transaction financière |
| **Langues** | Interface client en français ; architecture multilingue en place (fr / en) |
| **Devise** | Franc CFA (XAF), sans décimale |
| **Compatibilité mobile** | Android et iOS ; contrôle de version minimale avec mise à jour forcée possible |
| **Observabilité** | Journalisation serveur + remontée des erreurs des applications mobiles vers l'administration |
| **Sauvegarde** | Base de données sauvegardée avant toute opération de réinitialisation |

---

## 8. Périmètre exclu (version actuelle)

- Paiement par carte bancaire : structure présente, non activée en production.
- Facturation automatique récurrente / abonnement gaz.
- Gestion multi-devises.
- Marketplace multi-fournisseurs (un seul circuit d'approvisionnement).
