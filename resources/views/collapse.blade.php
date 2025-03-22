@extends('layout.master')
@section('title', 'Collapse')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Collapse</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
									<span>
									  <i class="ph-duotone  ph-briefcase f-s-16"></i> Ui kits
									</span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Collapse</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Collapse start -->
        <div class="row">
            <!-- collapse 1 -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5>Basic collapse</h5>
                        <a aria-controls="collapse1" aria-expanded="false"
                           data-bs-toggle="collapse" href="#collapse1">
                            <i class="ti ti-code source" data-source="coll1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <p class="d-inline-flex flex-wrap gap-1">
                            <a aria-controls="collapseExample" aria-expanded="false"
                               class="btn btn-light-primary b-r-22" data-bs-toggle="collapse"
                               href="#collapseExample"
                               role="button">
                                Link with href
                            </a>
                            <button aria-controls="collapseExample" aria-expanded="false"
                                    class="btn btn-light-primary b-r-22"
                                    data-bs-target="#collapseExample" data-bs-toggle="collapse"
                                    type="button">
                                Button with data-bs-target
                            </button>
                        </p>
                        <div class="collapse" id="collapseExample">
                            <div class="card card-body dashed-1-secondary">
                                Some placeholder content for the collapse component. This panel is hidden by
                                default but revealed when the user activates the relevant trigger.
                            </div>
                        </div>
                    </div>

                    <pre class="coll1 collapse mt-3" id="collapse1">
  <code class="language-html">

&lt;div class="card"&gt;
 &lt;div class="card-header"&gt;
   &lt;h5&gt;Basic collapse&lt;/h5&gt;
 &lt;/div&gt;
 &lt;div class="card-body"&gt;
   &lt;p class="d-inline-flex gap-1"&gt;
     &lt;a class="btn btn-primary b-r-22" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"&gt;
      Link with href
     &lt;/a&gt;
     &lt;button class="btn btn-primary b-r-22" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"&gt;
      Button with data-bs-target
     &lt;/button&gt;
    &lt;/p&gt;
    &lt;div class="collapse" id="collapseExample"&gt;
     &lt;div class="card card-body"&gt;
      Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
     &lt;/div&gt;
    &lt;/div&gt;
 &lt;/div&gt;
&lt;/div&gt;

  </code>
</pre>
                </div>
            </div>


            <!-- collapse 2 -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5>Horizontal </h5>
                        <a aria-controls="collapse2" aria-expanded="false"
                           data-bs-toggle="collapse" href="#collapse2">
                            <i class="ti ti-code source" data-source="coll2"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <p>
                            <button aria-controls="collapseWidthExample" aria-expanded="false"
                                    class="btn btn-light-primary b-r-22"
                                    data-bs-target="#collapseWidthExample" data-bs-toggle="collapse"
                                    type="button">
                                Toggle width collapse
                            </button>
                        </p>
                        <div>
                            <div class="collapse collapse-horizontal" id="collapseWidthExample">
                                <div class="card card-body dashed-1-secondary w-280">
                                    This is some placeholder content for a horizontal collapse. It's hidden
                                    by default and shown when triggered.
                                </div>
                            </div>
                        </div>
                    </div>

                    <pre class="coll2 collapse mt-3" id="collapse2">
  <code class="language-html">

&lt;div class="card"&gt;
 &lt;div class="card-header"&gt;
   &lt;h5&gt;Horizontal &lt;/h5&gt;
 &lt;/div&gt;
 &lt;div class="card-body"&gt;
       &lt;button class="btn btn-primary b-r-22" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWidthExample" aria-expanded="false" aria-controls="collapseWidthExample"&gt;
        Toggle width collapse
       &lt;/button&gt;
      &lt;/p&gt;
      &lt;div style="min-height: 120px;"&gt;
       &lt;div class="collapse collapse-horizontal" id="collapseWidthExample"&gt;
        &lt;div class="card card-body" style="width: 300px;"&gt;
         This is some placeholder content for a horizontal collapse. It's hidden by default and shown when triggered.
        &lt;/div&gt;
       &lt;/div&gt;
      &lt;/div&gt;
 &lt;/div&gt;
&lt;/div&gt;

  </code>
</pre>
                </div>
            </div>

            <!-- collapse 3 -->
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between code-header card-header">
                        <h5 class="txt-ellipsis">Multiple toggles and targets</h5>
                        <a aria-controls="collapse3" aria-expanded="false"
                           data-bs-toggle="collapse" href="#collapse3">
                            <i class="ti ti-code source" data-source="coll3"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <p class="d-inline-flex flex-wrap gap-1">
                            <a aria-controls="multiCollapseExample1" aria-expanded="false"
                               class="btn btn-light-primary b-r-22" data-bs-toggle="collapse"
                               href="#multiCollapseExample1"
                               role="button">Toggle first element</a>
                            <button aria-controls="multiCollapseExample2" aria-expanded="false"
                                    class="btn btn-light-primary b-r-22"
                                    data-bs-target="#multiCollapseExample2" data-bs-toggle="collapse"
                                    type="button">Toggle second element
                            </button>
                            <button aria-controls="multiCollapseExample1 multiCollapseExample2"
                                    aria-expanded="false" class="btn btn-light-primary b-r-22"
                                    data-bs-target=".multi-collapse" data-bs-toggle="collapse"
                                    type="button">Toggle both
                                elements
                            </button>
                        </p>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="collapse multi-collapse" id="multiCollapseExample1">
                                    <div class="card card-body dashed-1-secondary">
                                        Some placeholder content for the first collapse component of this
                                        multi-collapse example. This panel is hidden by default but revealed
                                        when the user activates the relevant trigger.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="collapse multi-collapse" id="multiCollapseExample2">
                                    <div class="card card-body dashed-1-secondary">
                                        Some placeholder content for the second collapse component of this
                                        multi-collapse example. This panel is hidden by default but revealed
                                        when the user activates the relevant trigger.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <pre class="coll3 collapse mt-3" id="collapse3">
  <code class="language-html">

&lt;div class="card"&gt;
&lt;div class="card-header"&gt;
  &lt;h5&gt;Multiple toggles and targets&lt;/h5&gt;
&lt;/div&gt;
&lt;div class="card-body"&gt;
  &lt;p class="d-inline-flex gap-1"&gt;
    &lt;a class="btn btn-primary b-r-22" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"&gt;Toggle first element&lt;/a&gt;
    &lt;button class="btn btn-primary b-r-22" type="button" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample2" aria-expanded="false" aria-controls="multiCollapseExample2"&gt;Toggle second element&lt;/button&gt;
    &lt;button class="btn btn-primary b-r-22" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2"&gt;Toggle both elements&lt;/button&gt;
   &lt;/p&gt;
   &lt;div class="row"&gt;
    &lt;div class="col"&gt;
     &lt;div class="collapse multi-collapse" id="multiCollapseExample1"&gt;
      &lt;div class="card card-body"&gt;
       Some placeholder content for the first collapse component of this multi-collapse example. This panel is hidden by default but revealed when the user activates the relevant trigger.
      &lt;/div&gt;
     &lt;/div&gt;
    &lt;/div&gt;
    &lt;div class="col"&gt;
     &lt;div class="collapse multi-collapse" id="multiCollapseExample2"&gt;
      &lt;div class="card card-body"&gt;
       Some placeholder content for the second collapse component of this multi-collapse example. This panel is hidden by default but revealed when the user activates the relevant trigger.
      &lt;/div&gt;
     &lt;/div&gt;
    &lt;/div&gt;
   &lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;

  </code>
</pre>
                </div>
            </div>

        </div>
        <!-- Collapse end -->
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

@endsection
