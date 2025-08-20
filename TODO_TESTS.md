# Feuille de Route des Tests d'Intégration

## Test d'intégration pour les services

Ce document liste les services pour lesquels des tests d'intégration doivent être écrits. Ces tests sont de véritables tests d'intégration : ils interagissent directement avec la base de données et les dépendances réelles (repositories), sans aucun mocking. L'objectif est de valider le comportement complet des services dans un environnement proche de la production.

Chaque fois qu'un service est testé et que ses tests passent avec succès, il sera barré dans la liste ci-dessous. Si un nouveau service est ajouté au projet, il devra être ajouté à cette liste.

### Services à tester :

- [x] SupplierDeliveryService
- [x] AccessoryService
- [x] AuthService
- [x] BottleService
- [x] BottleTypeService
- [x] CustomerService
- [x] DashboardService
- [ ] DistributionCenterService
- [ ] OrderService
- [ ] PermissionsService
- [ ] ProductCategoryCityPriceService
- [ ] SharedService
- [ ] SMSService
- [ ] SupplyService
- [x] UserService