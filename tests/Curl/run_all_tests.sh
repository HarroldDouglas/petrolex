#!/bin/bash

# Configuration des couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Compteurs
SUCCESS_COUNT=0
ERROR_COUNT=0

# Fonction pour exécuter un script
execute_script() {
    local script_name="$1"
    local script_args="$2"
    local description="$3"
    
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}🔄 $description${NC}"
    echo -e "${BLUE}▶️  Commande: $script_name $script_args${NC}"
    echo ""
    
    if eval "$script_name $script_args"; then
        echo -e "${GREEN}✅ SUCCESS: $description${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "${RED}❌ FAILED: $description${NC}"
        ((ERROR_COUNT++))
    fi
    
    echo ""
    sleep 2
}

# Fonction pour afficher le résumé
show_summary() {
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}📊 RÉSUMÉ DES TESTS${NC}"
    echo -e "${GREEN}   ✅ Succès: $SUCCESS_COUNT${NC}"
    echo -e "${RED}   ❌ Erreurs: $ERROR_COUNT${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# Aller dans le répertoire du script
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1

echo -e "${GREEN}🚀 DÉMARRAGE DES TESTS API PETROLEX${NC}"
echo -e "${YELLOW}$(date)${NC}"
echo ""

# ==========================================
# 1. AUTHENTIFICATION (OBLIGATOIRE EN PREMIER)
# ==========================================
execute_script \
    "./auth.sh" \
    "" \
    "Authentification utilisateur"

# ==========================================
# 2. TESTS DE LECTURE (GET)
# ==========================================
execute_script \
    "./customers.sh" \
    "" \
    "Récupération de la liste des clients"

execute_script \
    "./customer_addresses.sh" \
    "1" \
    "Récupération du client ID=1"

# ==========================================
# 3. TESTS DE CRÉATION D'ADRESSES
# ==========================================
execute_script \
    "./create_address.sh" \
    "1 \"Maison principale\" \"123 Rue des Palmiers\" \"Bonanjo\" \"Douala\" \"Cameroun\" \"+237612345678\" \"Jean\" \"Dupont\" \"jean.dupont@email.com\" \"Près de la pharmacie centrale\" true" \
    "Création d'adresse complète (maison principale)"

# ==========================================
# 4. TESTS DE CRÉATION D'ADRESSES
# ==========================================
execute_script \
    "./distribution_centers.sh" \
    "" \
    "Exécution des tests de centres de distribution"

# ==========================================
# 5. AUTRES TESTS (Ajouter ici vos autres scripts)
# ==========================================

# Exemple pour d'autres scripts :
# execute_script \
#     "./update_customer.sh" \
#     "1 \"Nouveau nom\" \"nouveau@email.com\"" \
#     "Mise à jour du client"

# execute_script \
#     "./delete_address.sh" \
#     "1 5" \
#     "Suppression d'une adresse"


# Afficher le résumé final
show_summary

# Code de sortie basé sur les résultats
if [ $ERROR_COUNT -eq 0 ]; then
    echo -e "${GREEN}🎉 TOUS LES TESTS ONT RÉUSSI !${NC}"
    exit 0
else
    echo -e "${RED}⚠️  CERTAINS TESTS ONT ÉCHOUÉ !${NC}"
    exit 1
fi
