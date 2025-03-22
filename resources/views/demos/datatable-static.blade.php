@extends('layout.master')

@section('title', 'DataTable Static Demo')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Data Table</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-table f-s-16"></i> Table
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Data Table</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Breadcrumb end -->

        <!-- Data Table start -->
        <div class="row">
            <!-- Default Datatable start -->
            <div class="col-12">
                <div class="card ">
                    <div class="card-header">
                        <h5>Default Datatable</h5>
                        <p>DataTables has most features enabled by default, so all you need to do to use it with your own
                            tables is to call the construction function: <code>$().DataTable();</code>. </p>
                    </div>
                    <div class="card-body p-0">
                        <div class="app-datatable-default overflow-auto">
                            <livewire:data-table :headers="[
                                'name' => 'Name',
                                'position' => 'Position',
                                'office' => 'Office',
                                'age' => 'Age',
                                'start_date' => 'Start Date',
                                'salary' => 'Salary',
                            ]" :rows="[
                                [
                                    'name' => 'Tiger Nixon',
                                    'position' => 'System Architect',
                                    'office' => 'Edinburgh',
                                    'age' => 61,
                                    'start_date' => '2011/04/25',
                                    'salary' => '$320,800',
                                ],
                                [
                                    'name' => 'Garrett Winters',
                                    'position' => 'Accountant',
                                    'office' => 'Tokyo',
                                    'age' => 63,
                                    'start_date' => '2011/07/25',
                                    'salary' => '$170,750',
                                ],
                                [
                                    'name' => 'Ashton Cox',
                                    'position' => 'Junior Technical Author',
                                    'office' => 'San Francisco',
                                    'age' => 66,
                                    'start_date' => '2009/01/12',
                                    'salary' => '$86,000',
                                ],
                                [
                                    'name' => 'Cedric Kelly',
                                    'position' => 'Senior Javascript Developer',
                                    'office' => 'Edinburgh',
                                    'age' => 22,
                                    'start_date' => '2012/03/29',
                                    'salary' => '$433,060',
                                ],
                                [
                                    'name' => 'Airi Satou',
                                    'position' => 'Accountant',
                                    'office' => 'Tokyo',
                                    'age' => 33,
                                    'start_date' => '2008/11/28',
                                    'salary' => '$162,700',
                                ],
                                [
                                    'name' => 'Brielle Williamson',
                                    'position' => 'Integration Specialist',
                                    'office' => 'New York',
                                    'age' => 61,
                                    'start_date' => '2012/12/02',
                                    'salary' => '$372,000',
                                ],
                                [
                                    'name' => 'Herrod Chandler',
                                    'position' => 'Sales Assistant',
                                    'office' => 'San Francisco',
                                    'age' => 59,
                                    'start_date' => '2012/08/06',
                                    'salary' => '$137,500',
                                ],
                                [
                                    'name' => 'Rhona Davidson',
                                    'position' => 'Integration Specialist',
                                    'office' => 'Tokyo',
                                    'age' => 55,
                                    'start_date' => '2010/10/14',
                                    'salary' => '$327,900',
                                ],
                                [
                                    'name' => 'Tiger Nixon',
                                    'position' => 'System Architect',
                                    'office' => 'Edinburgh',
                                    'age' => 61,
                                    'start_date' => '2011/04/25',
                                    'salary' => '$320,800',
                                ],
                                [
                                    'name' => 'Garrett Winters',
                                    'position' => 'Accountant',
                                    'office' => 'Tokyo',
                                    'age' => 63,
                                    'start_date' => '2011/07/25',
                                    'salary' => '$170,750',
                                ],
                                [
                                    'name' => 'Ashton Cox',
                                    'position' => 'Junior Technical Author',
                                    'office' => 'San Francisco',
                                    'age' => 66,
                                    'start_date' => '2009/01/12',
                                    'salary' => '$86,000',
                                ],
                                [
                                    'name' => 'Cedric Kelly',
                                    'position' => 'Senior Javascript Developer',
                                    'office' => 'Edinburgh',
                                    'age' => 22,
                                    'start_date' => '2012/03/29',
                                    'salary' => '$433,060',
                                ],
                                [
                                    'name' => 'Airi Satou',
                                    'position' => 'Accountant',
                                    'office' => 'Tokyo',
                                    'age' => 33,
                                    'start_date' => '2008/11/28',
                                    'salary' => '$162,700',
                                ],
                                [
                                    'name' => 'Brielle Williamson',
                                    'position' => 'Integration Specialist',
                                    'office' => 'New York',
                                    'age' => 61,
                                    'start_date' => '2012/12/02',
                                    'salary' => '$372,000',
                                ],
                                [
                                    'name' => 'Herrod Chandler',
                                    'position' => 'Sales Assistant',
                                    'office' => 'San Francisco',
                                    'age' => 59,
                                    'start_date' => '2012/08/06',
                                    'salary' => '$137,500',
                                ],
                                [
                                    'name' => 'Rhona Davidson',
                                    'position' => 'Integration Specialist',
                                    'office' => 'Tokyo',
                                    'age' => 55,
                                    'start_date' => '2010/10/14',
                                    'salary' => '$327,900',
                                ],
                                [
                                    'name' => 'Colleen Hurst',
                                    'position' => 'Javascript Developer',
                                    'office' => 'San Francisco',
                                    'age' => 39,
                                    'start_date' => '2009/09/15',
                                    'salary' => '$205,500',
                                ],
                                [
                                    'name' => 'Sonya Frost',
                                    'position' => 'Software Engineer',
                                    'office' => 'Edinburgh',
                                    'age' => 23,
                                    'start_date' => '2008/12/13',
                                    'salary' => '$103,600',
                                ],
                                [
                                    'name' => 'Jena Gaines',
                                    'position' => 'Office Manager',
                                    'office' => 'London',
                                    'age' => 30,
                                    'start_date' => '2008/12/19',
                                    'salary' => '$90,560',
                                ],
                            ]" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- Default Datatable end -->
        </div>
        <!-- Data Table end -->
    </div>
@endsection
