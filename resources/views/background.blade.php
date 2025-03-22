@extends('layout.master')
@section('title', 'Background')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Background</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
									<span>
									  <i class="ph-duotone  ph-briefcase f-s-16"></i> Ui kits
									</span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Background</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Background start -->
        <div class="row gap-4">
            <!-- Background colors start -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5>Background colors</h5>
                        <a aria-controls="button1" aria-expanded="false"
                           data-bs-toggle="collapse" href="#button1">
                            <i class="ti ti-code source" data-source="btn1"></i>
                        </a>
                    </div>

                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn bg-primary text-white b-r-22" type="button">bg-primary
                            </button>
                            <button class="btn bg-secondary text-white b-r-22" type="button">bg-secondary
                            </button>
                            <button class="btn bg-success text-white b-r-22" type="button">bg-success
                            </button>
                            <button class="btn bg-danger text-white b-r-22" type="button">bg-danger</button>
                            <button class="btn bg-warning text-white b-r-22" type="button">bg-warning
                            </button>
                            <button class="btn bg-info text-white b-r-22" type="button">bg-info</button>
                            <button class="btn bg-light text-dark b-r-22" type="button">bg-light</button>
                            <button class="btn bg-dark text-white b-r-22" type="button">bg-dark</button>
                        </div>
                    </div>

                    <pre class="btn1 collapse mt-3" id="button1">
<code class="language-html">&lt;div class="card"&gt;
 &lt;div class="card-header"&gt;
       &lt;h5&gt;Background colors&lt;/h5&gt;
      &lt;/div&gt;
      &lt;div class="card-body d-flex justify-content-between align-items-center"&gt;
       &lt;div class="d-flex gap-2 flex-wrap"&gt;
        &lt;button type="button" class="btn bg-primary text-white b-r-22"&gt;bg-primary&lt;/button&gt;
        &lt;button type="button" class="btn bg-secondary text-white b-r-22"&gt;bg-secondary&lt;/button&gt;
        &lt;button type="button" class="btn bg-success text-white b-r-22"&gt;bg-success&lt;/button&gt;
        &lt;button type="button" class="btn bg-danger text-white b-r-22"&gt;bg-danger&lt;/button&gt;
        &lt;button type="button" class="btn bg-warning text-white b-r-22"&gt;bg-warning&lt;/button&gt;
        &lt;button type="button" class="btn bg-info text-white b-r-22"&gt;bg-info&lt;/button&gt;
        &lt;button type="button" class="btn bg-light text-dark b-r-22"&gt;bg-light&lt;/button&gt;
        &lt;button type="button" class="btn bg-dark text-white b-r-22"&gt;bg-dark&lt;/button&gt;
       &lt;/div&gt;
      &lt;/div&gt;
&lt;/div&gt;</code>
                  </pre>
                </div>
            </div>
            <!-- Background colors end -->
            <!-- Background color shades start -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5 class="txt-ellipsis">Background color shades</h5>
                        <a aria-controls="button2" aria-expanded="false"
                           data-bs-toggle="collapse" href="#button2">
                            <i class="ti ti-code source" data-source="btn2"></i>
                        </a>
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn bg-primary-900 b-r-22" type="button">bg-primary-900</button>
                            <button class="btn bg-primary-800 b-r-22" type="button">bg-primary-800</button>
                            <button class="btn bg-primary-700 b-r-22" type="button">bg-primary-700</button>
                            <button class="btn bg-primary-600 b-r-22" type="button">bg-primary-600</button>
                            <button class="btn bg-primary-500 b-r-22" type="button">bg-primary-500</button>
                            <button class="btn bg-primary-400 b-r-22" type="button">bg-primary-400</button>
                            <button class="btn bg-primary-300 b-r-22" type="button">bg-primary-300</button>
                        </div>
                    </div>
                    <pre class="btn2 collapse mt-3" id="button2">
<code class="language-html">&lt;div class="card"&gt;
     &lt;div class="card-header"&gt;
      &lt;h5&gt;Background color shades&lt;/h5&gt;
     &lt;/div&gt;
     &lt;div class="card-body d-flex justify-content-between align-items-center"&gt;
      &lt;div class="d-flex gap-2 flex-wrap"&gt;
       &lt;button type="button" class="btn bg-primary-900 b-r-22"&gt;bg-primary-900&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-800 b-r-22"&gt;bg-primary-800&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-700 b-r-22"&gt;bg-primary-700&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-600 b-r-22"&gt;bg-primary-600&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-500 b-r-22"&gt;bg-primary-500&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-400 b-r-22"&gt;bg-primary-400&lt;/button&gt;
       &lt;button type="button" class="btn bg-primary-300 b-r-22"&gt;bg-primary-300&lt;/button&gt;
      &lt;/div&gt;
     &lt;/div&gt;
  &lt;/div&gt;</code></pre>
                </div>
            </div>
            <!-- Background color shades end -->
            <!-- Outline Background start -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5> Outline Background</h5>
                        <a aria-controls="button3" aria-expanded="false"
                           data-bs-toggle="collapse" href="#button3">
                            <i class="ti ti-code source" data-source="btn3"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn bg-outline-primary b-r-22" type="button">bg-outline-primary
                            </button>
                            <button class="btn bg-outline-secondary b-r-22" type="button">
                                bg-outline-secondary
                            </button>
                            <button class="btn bg-outline-success b-r-22" type="button">bg-outline-success
                            </button>
                            <button class="btn bg-outline-danger b-r-22" type="button">bg-outline-danger
                            </button>
                            <button class="btn bg-outline-warning b-r-22" type="button">bg-outline-warning
                            </button>
                            <button class="btn bg-outline-info b-r-22" type="button">bg-outline-info
                            </button>
                            <button class="btn bg-outline-light b-r-22" type="button">bg-outline-light
                            </button>
                            <button class="btn bg-outline-dark b-r-22" type="button">bg-outline-dark
                            </button>
                        </div>
                    </div>

                    <pre class="btn3 collapse mt-3" id="button3">
<code class="language-html">&lt;div class="card"&gt;
  &lt;div class="card-header"&gt;
     &lt;h5&gt; Outline Background&lt;/h5&gt;
  &lt;/div&gt;
  &lt;div class="card-body"&gt;
    &lt;div class="d-flex gap-2 flex-wrap"&gt;
     &lt;button type="button" class="btn bg-outline-primary b-r-22"&gt;bg-outline-primary&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-secondary b-r-22"&gt;bg-outline-secondary&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-success b-r-22"&gt;bg-outline-success&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-danger b-r-22"&gt;bg-outline-danger&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-warning b-r-22"&gt;bg-outline-warning&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-info b-r-22"&gt;bg-outline-info&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-light b-r-22"&gt;bg-outline-light&lt;/button&gt;
     &lt;button type="button" class="btn bg-outline-dark b-r-22"&gt;bg-outline-dark&lt;/button&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>
                </div>
            </div>
            <!-- Outline Background end -->
            <!-- Soft Background start -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5> Soft Background</h5>
                        <a aria-controls="button4" aria-expanded="false"
                           data-bs-toggle="collapse" href="#button4">
                            <i class="ti ti-code source" data-source="btn4"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn bg-light-primary b-r-22" type="button">bg-light-primary
                            </button>
                            <button class="btn bg-light-secondary b-r-22" type="button">bg-light-secondary
                            </button>
                            <button class="btn bg-light-success b-r-22" type="button">bg-light-success
                            </button>
                            <button class="btn bg-light-danger b-r-22" type="button">bg-light-danger
                            </button>
                            <button class="btn bg-light-warning b-r-22" type="button">bg-light-warning
                            </button>
                            <button class="btn bg-light-info b-r-22" type="button">bg-light-info</button>
                            <button class="btn bg-light-light b-r-22" type="button">bg-light-light</button>
                            <button class="btn bg-light-dark b-r-22" type="button">bg-light-dark</button>
                            <button class="btn bg-light-link b-r-22" type="button">bg-light-link</button>
                        </div>
                    </div>

                    <pre class="btn4 collapse mt-3" id="button4">
<code class="language-html">&lt;div class="card"&gt;
     &lt;div class="card-header"&gt;
      &lt;h5&gt; Soft Background&lt;/h5&gt;
     &lt;/div&gt;
     &lt;div class="card-body"&gt;
      &lt;div class="d-flex gap-2 flex-wrap"&gt;
       &lt;button type="button" class="btn bg-light-primary b-r-22"&gt;bg-light-primary&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-secondary b-r-22"&gt;bg-light-secondary&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-success b-r-22"&gt;bg-light-success&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-danger b-r-22"&gt;bg-light-danger&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-warning b-r-22"&gt;bg-light-warning&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-info b-r-22"&gt;bg-light-info&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-light b-r-22"&gt;bg-light-light&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-dark b-r-22"&gt;bg-light-dark&lt;/button&gt;
       &lt;button type="button" class="btn bg-light-link b-r-22"&gt;bg-light-link&lt;/button&gt;
      &lt;/div&gt;
     &lt;/div&gt;
  &lt;/div&gt;</code></pre>
                </div>
            </div>
            <!-- Soft Background end -->

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5> Dark Background</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn f-w-600 text-primary-dark bg-primary-300 b-r-22"
                                    type="button">
                                bg-light-primary
                            </button>
                            <button class="btn f-w-600 text-secondary-dark bg-secondary-300 b-r-22"
                                    type="button">
                                bg-light-secondary
                            </button>
                            <button class="btn f-w-600 text-success-dark bg-success-300 b-r-22"
                                    type="button">
                                bg-light-success
                            </button>
                            <button class="btn f-w-600 text-danger-dark bg-danger-300 b-r-22" type="button">
                                bg-light-danger
                            </button>
                            <button class="btn f-w-600 text-warning-dark bg-warning-300 b-r-22"
                                    type="button">
                                bg-light-warning
                            </button>
                            <button class="btn f-w-600 text-info-dark bg-info-300 b-r-22" type="button">
                                bg-light-info
                            </button>
                            <button class="btn f-w-600 text-dark-light bg-light-300 b-r-22" type="button">
                                bg-light-light
                            </button>
                            <button class="btn f-w-600 text-dark-dark bg-dark-300 b-r-22" type="button">
                                bg-light-dark
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!-- Background end -->
    </div>
@endsection

@section('script')
    <!--customizer-->
    <div id="customizer"></div>

@endsection

