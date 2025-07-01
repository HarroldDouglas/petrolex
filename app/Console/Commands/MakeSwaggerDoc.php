<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeSwaggerDoc extends Command
{
    protected $signature = 'make:swagger-doc {controller}';
    protected $description = 'Create Swagger documentation for a controller';

    public function handle()
    {
        $controllerPath = $this->argument('controller');
        $controllerName = class_basename($controllerPath);
        $namespace = 'App\\Http\\Controllers\\'.dirname($controllerPath);

        // Créer le chemin de destination
        $docsPath = base_path("documentation/{$controllerPath}ControllerDoc.php");
        $directory = dirname($docsPath);

        // Créer les répertoires si nécessaire
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Contenu du fichier de documentation
        $stub = <<<EOT
<?php

use OpenApi\Annotations as OA;

/**
 * Documentation for {$controllerName}
 * 
 * @OA\PathItem(
 *     path="/your/endpoint",
 *     @OA\Post(
 *         summary="Endpoint summary",
 *         tags={"YourTag"},
 *         @OA\RequestBody(
 *             required=true,
 *             @OA\JsonContent(ref="#/components/schemas/YourRequestSchema")
 *         ),
 *         @OA\Response(
 *             response=200,
 *             description="Successful operation",
 *             @OA\JsonContent(ref="#/components/schemas/YourResponseSchema")
 *         )
 *     )
 * )
 */
class {$controllerName}ControllerDoc
{
    // Add your Swagger annotations here
}
EOT;

        // Écrire le fichier
        File::put($docsPath, $stub);

        $this->info("Fichier de documentation créé: {$docsPath}");
        $this->line("N'oubliez pas de mettre à jour les annotations Swagger dans ce fichier");
    }
}
