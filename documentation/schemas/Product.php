<?php

namespace App\Documentation\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductCategoryData",
 *     title="Product Category Data",
 *     description="Data of a product category",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Bouteille de 6Kg"),
 *     @OA\Property(property="product_type", type="string", enum={"bottle", "accessory"}, example="bottle")
 * )
 *
 * @OA\Schema(
 *     schema="CommonProductProperties",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", description="ID de la catégorie de produit", example=1),
 *     @OA\Property(property="type", type="string", enum={"bottle", "accessory"}, description="Type de produit", example="bottle"),
 *     @OA\Property(property="name", type="string", description="Nom du produit (traduit selon la langue de l'utilisateur)", example="Bouteille de 6Kg"),
 *     @OA\Property(property="description", type="string", description="Description du produit (traduite selon la langue de l'utilisateur)", example="Une bouteille de gaz de 6 kilogrammes."),
 *     @OA\Property(property="category_name", type="string", description="Nom de catégorie traduit pour mobile selon la langue de l'utilisateur", example="Bouteilles à gaz domestiques"),
 *     @OA\Property(property="quantity", type="integer", description="Quantité en stock", example=40),
 *     @OA\Property(
 *         property="specifications",
 *         type="array",
 *         description="Spécifications techniques du produit (traduites selon la langue de l'utilisateur)",
 *
 *         @OA\Items(ref="#/components/schemas/ProductSpecification")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/CommonProductProperties"),
 *         @OA\Schema(
 *             oneOf={
 *                 @OA\Schema(ref="#/components/schemas/BottleProduct"),
 *                 @OA\Schema(ref="#/components/schemas/AccessoryProduct")
 *             }
 *         )
 *     }
 * )
 * @OA\Schema(
 *     schema="BottleProduct",
 *     type="object",
 *
 *     @OA\Property(
 *         property="options",
 *         type="array",
 *         description="Options d'achat disponibles pour la bouteille",
 *
 *         @OA\Items(ref="#/components/schemas/BottleOption")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AccessoryProduct",
 *     type="object"
 * )
 * @OA\Schema(
 *     schema="ProductSpecification",
 *     type="object",
 *     description="Spécification technique d'un produit",
 *
 *     @OA\Property(property="name", type="string", description="Nom de la spécification (traduit selon la langue)", example="hauteur"),
 *     @OA\Property(property="value", type="string", description="Valeur de la spécification", example="40.00"),
 *     @OA\Property(property="unit", type="string", nullable=true, description="Unité de mesure", example="cm")
 * )
 *
 * @OA\Schema(
 *     schema="BottleOption",
 *     type="object",
 *     description="Option d'achat pour une bouteille",
 *
 *     @OA\Property(property="value", type="string", enum={"bottle_with_content", "content"}, description="Type d'option", example="bottle_with_content"),
 *     @OA\Property(property="label", type="string", description="Libellé de l'option (traduit selon la langue)", example="Bouteille avec recharge"),
 *     @OA\Property(property="price", type="string", description="Prix de l'option", example="5000.00")
 * )
 */
class ProductSchema {}
