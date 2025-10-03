# Payment Provider Test Interface

Interface de test pour les fournisseurs de paiement MTN Money et Orange Money.

## 🎯 Objectif

Cette interface permet de tester les APIs des fournisseurs de paiement mobile de façon simple et visuelle, avec des logs en temps réel et des scénarios de test prédéfinis.

## 📁 Structure

```
public/test/payment/
├── index.html          # Interface principale de test
├── payment-test.js     # Logique JavaScript et simulation
└── README.md          # Documentation (ce fichier)
```

## 🚀 Utilisation

1. **Accéder à l'interface**: Ouvrir `public/test/payment/index.html` dans un navigateur
2. **Sélectionner le fournisseur**: MTN Money ou Orange Money
3. **Entrer les informations**: Numéro de téléphone et montant
4. **Lancer le test**: Cliquer sur "Initier le Paiement"
5. **Observer les logs**: Console en temps réel en bas de page

## 🧪 Numéros de Test

### MTN Money
- **Succès**: `677000001` - Transaction réussie immédiatement
- **Attente**: `677000002` - Transaction en attente puis confirmation/échec
- **Échec**: `677000003` - Transaction échouée avec message d'erreur

### Orange Money
- **Succès**: `699000001` - Transaction réussie immédiatement
- **Attente**: `699000002` - Transaction en attente puis confirmation/échec
- **Échec**: `699000003` - Transaction échouée avec message d'erreur

## 🎨 Fonctionnalités

### Interface
- **Formulaire simple**: Dropdown fournisseur + champ numéro + montant
- **Mode de test**: Sandbox/Production
- **Validation basique**: Champs requis et montant minimum

### Console de Logs
- **Logs temps réel**: Affichage de toutes les étapes
- **Types de messages**: Info (bleu), Succès (vert), Erreur (rouge), Attention (orange)
- **Horodatage**: Timestamp sur chaque entrée
- **Défilement automatique**: Les nouveaux logs apparaissent en bas

### Statistiques
- **Compteurs visuels**: Succès, En attente, Échecs
- **Mise à jour temps réel**: Incrémentation automatique
- **Reset possible**: Bouton "Vider les Logs"

## 🔧 Simulation

### Scénarios Automatiques
- **Test numbers**: Comportement prédictible basé sur le numéro
- **Random scenarios**: Comportement aléatoire pour les autres numéros
- **Délais réalistes**: 2s pour l'initiation, 5s pour les confirmations

### Réponses Simulées
```javascript
// Succès
{
  success: true,
  transactionId: "TXN_1696320000_ABC123",
  confirmationCode: "CONF12AB",
  status: "completed"
}

// Échec
{
  success: false,
  error: "Solde insuffisant",
  errorCode: "ERR_0001",
  status: "failed"
}
```

## 🎯 Intégration Future

Cette interface est conçue pour être facilement adaptée aux vraies APIs:

1. **Endpoints**: Modifier `PAYMENT_CONFIG.endpoints` dans `payment-test.js`
2. **Authentification**: Ajouter les headers/tokens nécessaires
3. **Format de données**: Adapter les payloads aux spécifications des APIs
4. **Gestion d'erreurs**: Mapper les codes d'erreur réels

## 🛠️ Développement

### Ajout de Nouveaux Fournisseurs
1. Ajouter l'option dans le `<select>` HTML
2. Ajouter l'endpoint dans `PAYMENT_CONFIG.endpoints`
3. Ajouter les numéros de test dans `PAYMENT_CONFIG.testNumbers`
4. Étendre la logique de `simulatePaymentResponse()`

### Personnalisation des Logs
Modifier la classe `PaymentConsole` pour:
- Changer les formats d'affichage
- Ajouter de nouveaux types de messages
- Implémenter la persistence des logs
- Exporter les logs en fichier

## 📱 Responsive Design

L'interface est optimisée pour:
- **Desktop**: Layout complet avec sidebar
- **Tablet**: Colonnes adaptées
- **Mobile**: Stack vertical avec formulaire simplifié

## 🔒 Sécurité

⚠️ **Important**: Cette interface est uniquement pour les tests!
- Ne jamais utiliser en production avec de vrais numéros
- Pas d'authentification implémentée
- Les données ne sont pas persistées
- Utiliser uniquement en environnement de développement