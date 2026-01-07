#!/bin/bash
#
# Lance TOUS les tests API en production
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  SUITE COMPLÈTE DE TESTS API ISOGAZ           ║${NC}"
echo -e "${BLUE}║  Production: https://isogaz.afrik-solutions.com║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}\n"

TOTAL_PASSED=0
TOTAL_FAILED=0
TOTAL_TESTS=0

# Test 1: Client API
echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
echo -e "${BLUE}  TEST 1/2: CLIENT API (35 tests)${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════${NC}\n"

if bash "$SCRIPT_DIR/test_customer_api.sh"; then
    echo -e "${GREEN}✅ CLIENT API: PASSED${NC}\n"
    CLIENT_STATUS="PASSED"
else
    echo -e "${YELLOW}⚠️  CLIENT API: SOME FAILURES${NC}\n"
    CLIENT_STATUS="FAILED"
fi

sleep 2

# Test 2: Delivery Person API
echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
echo -e "${BLUE}  TEST 2/2: DELIVERY PERSON API (20 tests)${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════${NC}\n"

if bash "$SCRIPT_DIR/test_delivery_person_api.sh"; then
    echo -e "${GREEN}✅ DELIVERY PERSON API: PASSED${NC}\n"
    DELIVERY_STATUS="PASSED"
else
    echo -e "${YELLOW}⚠️  DELIVERY PERSON API: SOME FAILURES${NC}\n"
    DELIVERY_STATUS="FAILED"
fi

# Final Summary
echo -e "\n${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║           RÉSUMÉ FINAL                         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}\n"

echo -e "Client API (35 tests):          $CLIENT_STATUS"
echo -e "Delivery Person API (20 tests): $DELIVERY_STATUS"
echo -e "${BLUE}─────────────────────────────────────────────────${NC}"
echo -e "TOTAL: 55 tests"

if [ "$CLIENT_STATUS" = "PASSED" ] && [ "$DELIVERY_STATUS" = "PASSED" ]; then
    echo -e "\n${GREEN}🎉 TOUS LES TESTS SONT PASSÉS!${NC}\n"
    exit 0
else
    echo -e "\n${YELLOW}⚠️  Certains tests ont échoué. Voir les détails ci-dessus.${NC}\n"
    exit 1
fi
