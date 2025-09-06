<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdvertisingBanner",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Identifiant unique de la bannière",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Titre de la bannière",
 *         example="Promo spéciale gaz domestique"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description de la bannière",
 *         example="Profitez de notre offre spéciale sur les bouteilles de gaz domestique"
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         description="URL de l'image de la bannière",
 *         example="https://example.com/banners/promo-gaz-domestique.jpg"
 *     ),
 *     @OA\Property(
 *         property="action_url",
 *         type="string",
 *         nullable=true,
 *         description="URL d'action optionnelle lors du clic sur la bannière",
 *         example=null
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Statut d'activation de la bannière",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="integer",
 *         description="Priorité d'affichage de la bannière (ordre croissant)",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         description="Date de début d'affichage",
 *         example="2025-01-01"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         description="Date de fin d'affichage",
 *         example="2025-12-31"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AdvertisingBannersData",
 *
 *     @OA\Property(
 *         property="banners",
 *         type="array",
 *         description="Liste des bannières publicitaires actives",
 *         @OA\Items(ref="#/components/schemas/AdvertisingBanner")
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="integer",
 *         description="Nombre total de bannières actives",
 *         example=2
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AdvertisingBannersResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/AdvertisingBannersData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Bannières publicitaires récupérées avec succès."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/app/advertising/banners",
 *     summary="Récupérer les bannières publicitaires",
 *     description="Retourne la liste des bannières publicitaires actives triées par priorité",
 *     operationId="api.app.advertising.banners",
 *     tags={"App"},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Bannières publicitaires récupérées avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/AdvertisingBannersResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Erreur lors de la récupération des bannières publicitaires")
 *             )
 *         )
 *     )
 * )
 */
class GetAdvertisingBannersControllerDoc {}