@extends('layout.master')
@section('title', 'Nouveau produit')
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title"> Nouveau produit</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Produits
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Nouveau produit</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('products.list') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row app-form g-3" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Informations de base -->
                            <div class="col-md-12">
                                <h5 class="mb-3">Informations de base</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="product_name" class="form-label">Nom du produit</label>
                                <input type="text" class="form-control" id="product_name" name="name"
                                    placeholder="Entrez le nom du produit" required>
                            </div>

                            <div class="col-md-12">
                                <label for="description" class="form-label">Description du produit</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Entrez la description du produit" required></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="product_images" class="form-label">Images du produit</label>
                                <input type="file" class="form-control" id="product_images" name="images[]" multiple
                                    placeholder="Sélectionnez des images" required>
                                <small class="text-muted">Vous pouvez sélectionner plusieurs images.</small>
                            </div>

                            <!-- Section des variations -->
                            <div class="col-md-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Variations du produit</h5>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-variation">
                                        <i class="ti ti-plus"></i> Ajouter une variation
                                    </button>
                                </div>
                                <div id="variations-container">
                                    <div class="row variation-row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="variations[0][name]"
                                                placeholder="Nom de la variation" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" class="form-control" name="variations[0][price]"
                                                placeholder="Prix" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-variation">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons de soumission -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">
                                        <i class="ti ti-x"></i> Annuler
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="ti ti-device-floppy"></i> Enregistrer le produit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@section('script')
    <script>
        $(document).ready(function() {
            let variationCount = 1;

            // Add new variation
            $('#add-variation').click(function() {
                const newRow = `
                    <div class="row variation-row mb-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="variations[${variationCount}][name]" placeholder="Nom de la variation" required>
                        </div>
                        <div class="col-md-5">
                            <input type="number" class="form-control" name="variations[${variationCount}][price]" placeholder="Prix" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-variation">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#variations-container').append(newRow);
                variationCount++;
            });

            // Remove variation (using event delegation for dynamically added elements)
            $('#variations-container').on('click', '.remove-variation', function() {
                // Don't delete if it's the last row
                if ($('.variation-row').length > 1) {
                    $(this).closest('.variation-row').remove();
                } else {
                    // Clear fields instead if it's the last row
                    $(this).closest('.variation-row').find('input').val('');
                }
            });
        });
    </script>
@endsection
@endsection
