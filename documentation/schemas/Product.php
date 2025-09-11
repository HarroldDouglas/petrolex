<?php

namespace App\Documentation\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductImage",
 *     title="Product Image",
 *     description="Image data for a product with different sizes",
 *
 *     @OA\Property(property="url", type="string", description="URL de l'image originale", example="https://example.com/storage/images/product1.jpg"),
 *     @OA\Property(property="thumb", type="string", description="URL de la miniature (150x150)", example="https://example.com/storage/images/product1-thumb.jpg"),
 *     @OA\Property(property="medium", type="string", description="URL de l'image moyenne (500x500)", example="https://example.com/storage/images/product1-medium.jpg"),
 *     @OA\Property(property="large", type="string", description="URL de la grande image (1200x1200)", example="https://example.com/storage/images/product1-large.jpg"),
 *     @OA\Property(property="is_default", type="boolean", description="Indique si c'est l'image par défaut", example=true)
 * )
 *
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
 *     @OA\Property(property="price", type="string", description="Prix par défaut du produit", example="5000.00"),
 *     @OA\Property(
 *         property="specifications",
 *         type="array",
 *         description="Spécifications techniques du produit (traduites selon la langue de l'utilisateur)",
 *
 *         @OA\Items(ref="#/components/schemas/ProductSpecification")
 *     ),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         description="Images du produit avec différentes tailles",
 *
 *         @OA\Items(ref="#/components/schemas/ProductImage")
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
 *     type="object",
 *
 *     @OA\Property(
 *         property="options",
 *         type="array",
 *         description="Options d'achat disponibles pour l'accessoire",
 *
 *         @OA\Items(ref="#/components/schemas/AccessoryOption")
 *     )
 * )
 * @OA\Schema(
 *     schema="AccessoryOption",
 *     type="object",
 *     description="Option d'achat pour un accessoire",
 *
 *     @OA\Property(property="value", type="string", description="Type d'option", example="default"),
 *     @OA\Property(property="label", type="string", description="Libellé de l'option", example="default"),
 *     @OA\Property(property="price", type="string", description="Prix de l'option", example="500.00"),
 *     @OA\Property(property="is_default", type="boolean", description="Indique si c'est l'option par défaut", example=true)
 * )
 * @OA\Schema(
 *     schema="ProductSpecification",
 *     type="object",
 *     description="Spécification technique d'un produit (localisée selon la langue de l'utilisateur)",
 *
 *     @OA\Property(property="name", type="string", description="Nom de la spécification traduit selon la langue de l'utilisateur (ex: 'Hauteur' en français, 'Height' en anglais)", example="Hauteur"),
 *     @OA\Property(property="value", type="string", description="Valeur de la spécification avec unité combinée", example="40.00 cm")
 * )
 *
 * @OA\Schema(
 *     schema="BottleOption",
 *     type="object",
 *     description="Option d'achat pour une bouteille",
 *
 *     @OA\Property(property="value", type="string", enum={"bottle_with_content", "content"}, description="Type d'option", example="bottle_with_content"),
 *     @OA\Property(property="label", type="string", description="Libellé de l'option (traduit selon la langue)", example="Bouteille avec recharge"),
 *     @OA\Property(property="price", type="string", description="Prix de l'option", example="5000.00"),
 *     @OA\Property(property="is_default", type="boolean", description="Indique si c'est l'option par défaut", example=true)
 * )
 */
class ProductSchema {}
