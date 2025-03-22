@extends('layout.master')
@section('title', 'Setting')
@section('css')
    <!-- glight css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/glightbox/glightbox.min.css') }}">

    <!-- apexcharts css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">

    <!-- Select2 css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Setting</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                                    <span>
                                      <i class="ph-duotone  ph-stack f-s-16 "></i> Apps
                                    </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Profile</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Setting</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- setting-app start -->
        <div class="row">
            <div class="col-lg-4 col-xxl-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="vertical-tab setting-tab">
                            <ul class="nav nav-tabs app-tabs-primary " id="v-bg" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab"
                                            data-bs-toggle="tab" data-bs-target="#profile-tab-pane"
                                            type="button" role="tab" aria-controls="profile-tab-pane"
                                            aria-selected="true"> <i
                                            class="ph-bold  ph-user-circle-gear pe-2"></i>
                                        Profile</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab"
                                            data-bs-target="#activity-tab-pane" type="button" role="tab"
                                            aria-controls="activity-tab-pane" aria-selected="true"> <i
                                            class="ph-bold  ph-alarm pe-2"></i>
                                        Activity</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab"
                                            data-bs-target="#security-tab-pane" type="button" role="tab"
                                            aria-controls="security-tab-pane" aria-selected="false"><i
                                            class="ph-bold  ph-shield-check pe-2"></i>Security</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="privacy-tab" data-bs-toggle="tab"
                                            data-bs-target="#privacy-tab-pane" type="button" role="tab"
                                            aria-controls="privacy-tab-pane" aria-selected="false"><i
                                            class="ph-bold  ph-lock-open pe-2"></i>Privacy</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="notification-tab" data-bs-toggle="tab"
                                            data-bs-target="#notification-tab-pane" type="button" role="tab"
                                            aria-controls="notification-tab-pane" aria-selected="false"><i
                                            class="ph-bold  ph-notification pe-2"></i>Notification</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="subscription-tab" data-bs-toggle="tab"
                                            data-bs-target="#subscription-tab-pane" type="button" role="tab"
                                            aria-controls="subscription-tab-pane" aria-selected="false"><i
                                            class="ph-bold  ph-bell-simple pe-2"></i>Subscription</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="Connection-tab" data-bs-toggle="tab"
                                            data-bs-target="#Connection-tab-pane" type="button" role="tab"
                                            aria-controls="Connection-tab-pane" aria-selected="false"><i
                                            class="ph-bold  ph-graph pe-2"></i>Connection</button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" type="button" id="account_delete"><i
                                            class="ph-bold  ph-trash pe-2"></i>Delete</button>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>Time Spent</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <div id="timeSpent"></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="card hover-effect card-light-primary mt-4">
                            <div class="card-body">
                                <h5>Used space</h5>
                                <p class="mt-2 text-secondary f-s-16">Your team has used 80% of your
                                    available space. Need more?</p>

                                <div class="progress w-100 mt-3 mb-3" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-primary progress-bar-striped"
                                         style="width: 78.5%"> </div>
                                </div>

                                <span class="mt-4">
                                                    <a href="" class="me-3 text-secondary">Dismiss</a>
                                                    <a href="" class="text-d-underline">Upgrade plan</a>
                                                </span>

                            </div>
                        </div>
                        <div class="app-divider-v"></div>
                        <div class="d-flex align-items-center">
                                            <span class="h-45 w-45 d-flex-center bg-warning b-r-50 position-relative">
                                                <img src="{{ asset('../assets/images/avtar/9.png') }}" alt="avtar"
                                                     class="img-fluid b-r-50">
                                                <span
                                                    class="position-absolute top-0 end-0 p-1 bg-success border border-light rounded-circle"></span>
                                            </span>
                            <div class="flex-grow-1 ps-2 log-out-profile">
                                <div class="f-w-600 fs-6"> Ninfa Monaldo</div>
                                <div class="text-secondary f-s-12">Web Developer</div>
                            </div>
                            <div>
                                <a href="{{ route('profile') }}">
                                                <span>
                                                    <i class="ph-bold  ph-arrow-square-out f-s-20"></i>
                                                </span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xxl-9">
                <div class="tab-content">
                    <div aria-labelledby="profile-tab" class="tab-pane fade active show"
                         id="profile-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="card setting-profile-tab">
                            <div class="card-header">
                                <h5>Profile</h5>
                            </div>
                            <div class="card-body">
                                <div class="profile-tab profile-container">
                                    <div class="image-details">
                                        <div class="profile-image"></div>
                                        <div class="profile-pic">
                                            <div class="avatar-upload">
                                                <div class="avatar-edit">
                                                    <input accept=".png, .jpg, .jpeg" id="imageUpload"
                                                           type="file">
                                                    <label for="imageUpload"><i
                                                            class="ti ti-photo-heart"></i></label>
                                                </div>
                                                <div class="avatar-preview">
                                                    <div id="imgPreview">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="person-details">
                                        <h5 class="f-w-600">Ninfa Monaldo
                                            <img alt="instagram-check-mark" height="20"
                                                 src="../assets/images/profile-app/01.png"
                                                 width="20">
                                        </h5>
                                        <p>Web designer &amp; Developer</p>
                                    </div>

                                    <form class="app-form">
                                        <h5 class="mb-2 text-dark f-w-600">User Info</h5>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Username</label>
                                                    <input class="form-control" placeholder="Maria C. Eck"
                                                           type="text">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Email address</label>
                                                    <input class="form-control"
                                                           placeholder="MariaCEck@teleworm.us"
                                                           type="email">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <input class="form-control" placeholder="*******"
                                                           type="password">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Confirm Password</label>
                                                    <input class="form-control" placeholder="*******"
                                                           type="password">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="app-divider-v dotted"></div>
                                            </div>
                                            <h5 class="mb-2 text-dark f-w-600">Personal Info</h5>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <textarea class="form-control"
                                                              placeholder="1098 Asylum Avenu New Haven, CT 06510"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Address 2</label>
                                                    <textarea class="form-control"
                                                              placeholder="51244 Ankunding Villages, Reicheltown, IL 84366"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                           for="inputCity">City</label>
                                                    <input class="form-control" id="inputCity"
                                                           placeholder="oregon" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                           for="inputState">State</label>
                                                    <select class="form-select" id="inputState">
                                                        <option selected>Choose...</option>
                                                        <option>USA</option>
                                                        <option>Canada</option>
                                                        <option>Australia</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputZip">Zip</label>
                                                    <input class="form-control" id="inputZip"
                                                           placeholder="CT 06510" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-4">
                                                <div class="">
                                                    <label class="form-label">language</label>
                                                    <select
                                                        class="select-langauge form-select select-basic"
                                                        name="state">
                                                        <option value="EN">English</option>
                                                        <option value="GU">Gujarati</option>
                                                        <option value="HI">Hindi</option>
                                                        <option value="DU">Dutch</option>
                                                        <option value="FR">French</option>
                                                        <option value="RU">Russian</option>
                                                        <option value="KO">Korean</option>
                                                        <option value="TA">Tamil</option>
                                                        <option value="SP">Spanish</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <button class="btn btn-primary"
                                                            type="submit">Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="activity-tab" class="tab-pane fade" id="activity-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="card equal-card month-timeline">
                            <div class="card-header">
                                <div class="activity-time">
                                    <h5>Activity</h5>
                                    <div class="activity-tab-section">
                                        <ul class="nav nav-tabs tab-light-primary" id="Outline"
                                            role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button aria-controls="today-tab-pane" aria-selected="true"
                                                        class="nav-link active"
                                                        data-bs-target="#today-tab-pane"
                                                        data-bs-toggle="tab"
                                                        id="today-tab" role="tab"
                                                        type="button">Today
                                                </button>

                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button aria-controls="week-tab-pane" aria-selected="false"
                                                        class="nav-link" data-bs-target="#week-tab-pane"
                                                        data-bs-toggle="tab" id="week-tab"
                                                        role="tab"
                                                        type="button">Week
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button aria-controls="month-tab-pane" aria-selected="false"
                                                        class="nav-link"
                                                        data-bs-target="#month-tab-pane"
                                                        data-bs-toggle="tab"
                                                        id="month-tab" role="tab"
                                                        type="button">Month
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="OutlineContent">
                                    <div aria-labelledby="today-tab" class="tab-pane fade show active"
                                         id="today-tab-pane" role="tabpanel" tabindex="0">

                                        <ul class="app-timeline-box activity-timeline">
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                W
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-primary f-s-16 mb-0">Wilson<span
                                                                            class="text-secondary ms-2">added reaction in <span
                                                                                class="badge text-outline-primary me-2">#product website</span>post</span></span>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>09.00AM
                                                    </p>
                                                </div>
                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-info h-35 w-35 d-flex-center b-r-50 icon-direction">
                                <i class="ph-duotone  ph-image f-s-18"></i>
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <p class=" f-s-16 text-info mb-0">2 image files and 2
                                                        videos uploaded</p>

                                                    <div class="app-timeline-info-text timeline-border-box me-2 ms-0 mt-3 p-3">
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <a class="glightbox img-hover-zoom"
                                                                   data-glightbox="type: image; zoomable: true;"
                                                                   href="../assets/images/draggable/02.jpg">
                                                                    <img alt=""
                                                                         class="w-100 rounded" src="../assets/images/draggable/02.jpg">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <a class="glightbox img-hover-zoom"
                                                                   data-glightbox="type: image; zoomable: true;"
                                                                   href="../assets/images/draggable/04.jpg">
                                                                    <img alt=""
                                                                         class="w-100 rounded" src="../assets/images/draggable/04.jpg">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <a class="glightbox img-hover-zoom"
                                                                   data-glightbox="type: image; zoomable: true;"
                                                                   href="../assets/images/draggable/01.jpg">
                                                                    <img alt=""
                                                                         class="w-100 rounded" src="../assets/images/draggable/01.jpg">
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>Updated
                                                        at 12:45 pm
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-success  h-35 w-35 d-flex-center b-r-50">
                                D
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-secondary"><span
                                                                            class="text-success f-s-16 mb-0">Dane Wiza</span> added reaction in <span
                                                                            class="badge text-outline-success me-2">#product website</span>post</span>
                                                    </div>
                                                    <div class="timeline-border-box me-2 ms-0 mt-3">
                                                        <h6 class="mb-0">Need a feature</h6>
                                                        <p class="mb-4 text-secondary">Hello everyone,
                                                            question on email marketing. What are some
                                                            tips/tricks to avoid going to promotion
                                                            spam/ junk for automated marketing emails
                                                            going to promotion spam/ junk for automated
                                                            marketing emails</p>
                                                        <span class="badge text-outline-success me-2 timeline-badge">#🙂❤10Reactions</span>
                                                        <span class="badge text-outline-success me-2">#✨12Replies</span>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>09.00AM
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-danger h-35 w-35 d-flex-center b-r-50">
                                B
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-danger f-s-16 mb-0">Betty Mante <span
                                                                            class="text-secondary ms-2">Request joined <span
                                                                                class="badge text-outline-danger me-2">#reaserchteam</span>groups</span></span>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button class="btn btn-danger"
                                                                type="button">Accept
                                                        </button>
                                                        <button class="btn btn-outline-danger"
                                                                type="button">Rejects
                                                        </button>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>4 days
                                                        ago
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                P
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class=" f-s-16">
                                <span class="text-primary f-s-16 mb-0">Pinkie
                                <span class="text-secondary ms-2">uploaded
                                  <span class="text-dark f-w-600 me-2 ms-2">2</span>attachment <span
                                        class="badge text-outline-primary me-2">#reaserchteam</span></span>
                                </span>
                                                    </div>

                                                    <div class="mt-3">
                                                        <button class="btn btn-primary"
                                                                type="button">Accept
                                                        </button>
                                                        <button class="btn btn-outline-primary"
                                                                type="button">Rejects
                                                        </button>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>4 days
                                                        ago
                                                    </p>
                                                </div>


                                            </li>
                                        </ul>
                                    </div>
                                    <div aria-labelledby="week-tab-pane" class="tab-pane" id="week-tab-pane"
                                         role="tabpanel" tabindex="0">

                                        <ul class="app-timeline-box activity-timeline">
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-success  h-35 w-35 d-flex-center b-r-50">
                                D
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-secondary"><span
                                                                            class="text-success f-s-16 mb-0">Dane Wiza</span> added reaction in <span
                                                                            class="badge text-outline-success me-2">#product website</span>post</span>
                                                    </div>
                                                    <div class="timeline-border-box me-2 ms-0 mt-3">
                                                        <h6 class="mb-0">Need a feature</h6>
                                                        <p class="mb-4 text-secondary">Hello everyone,
                                                            question on email marketing. What are some
                                                            tips/tricks to avoid going to promotion
                                                            spam/ junk for automated marketing emails
                                                            going to promotion spam/ junk for automated
                                                            marketing emails</p>
                                                        <span class="badge text-outline-success me-2 timeline-badge">#🙂❤10Reactions</span>
                                                        <span class="badge text-outline-success me-2">#✨12Replies</span>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>09.00AM
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-danger h-35 w-35 d-flex-center b-r-50">
                                B
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-danger f-s-16 mb-0">Betty Mante <span
                                                                            class="text-secondary ms-2">Request joined <span
                                                                                class="badge text-outline-danger me-2">#reaserchteam</span>groups</span></span>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button class="btn btn-danger"
                                                                type="button">Accept
                                                        </button>
                                                        <button class="btn btn-outline-danger"
                                                                type="button">Rejects
                                                        </button>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>4 days
                                                        ago
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                P
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class=" f-s-16">
                                <span class="text-primary f-s-16 mb-0">Pinkie
                                <span class="text-secondary ms-2">uploaded
                                  <span class="text-dark f-w-600 me-2 ms-2">2</span>attachment <span
                                        class="badge text-outline-primary me-2">#reaserchteam</span></span>
                                </span>
                                                    </div>

                                                    <div class="mt-3">
                                                        <button class="btn btn-primary"
                                                                type="button">Accept
                                                        </button>
                                                        <button class="btn btn-outline-primary"
                                                                type="button">Rejects
                                                        </button>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>4 days
                                                        ago
                                                    </p>
                                                </div>


                                            </li>
                                        </ul>
                                    </div>
                                    <div aria-labelledby="month-tab-pane" class="tab-pane"
                                         id="month-tab-pane"
                                         role="tabpanel" tabindex="0">
                                        <ul class="app-timeline-box activity-timeline">
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-success  h-35 w-35 d-flex-center b-r-50">
                                D
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="f-s-16">
                                                                    <span class="text-secondary"><span
                                                                            class="text-success f-s-16 mb-0">Dane Wiza</span> added reaction in <span
                                                                            class="badge text-outline-success me-2">#product website</span>post</span>
                                                    </div>
                                                    <div class="timeline-border-box me-2 ms-0 mt-3">
                                                        <h6 class="mb-0">Need a feature</h6>
                                                        <p class="mb-4 text-secondary">Hello everyone,
                                                            question on email marketing. What are some
                                                            tips/tricks to avoid going to promotion
                                                            spam/ junk for automated marketing emails
                                                            going to promotion spam/ junk for automated
                                                            marketing emails</p>
                                                        <span class="badge text-outline-success me-2 timeline-badge">#🙂❤10Reactions</span>
                                                        <span class="badge text-outline-success me-2">#✨12Replies</span>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>09.00AM
                                                    </p>
                                                </div>


                                            </li>
                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-info h-35 w-35 d-flex-center b-r-50 icon-direction">
                                <i class="ph-duotone  ph-image f-s-18"></i>
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <p class=" f-s-16 text-info mb-0">2 image files and 2
                                                        videos uploaded</p>

                                                    <div class="app-timeline-info-text timeline-border-box me-2 ms-0 mt-3 p-3">
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <a class="glightbox img-hover-zoom"
                                                                   data-glightbox="type: image; zoomable: true;"
                                                                   href="../assets/images/draggable/02.jpg">
                                                                    <img alt=""
                                                                         class="w-100 rounded" src="../assets/images/draggable/02.jpg">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="img-hover-zoom">
                                                                    <a class="glightbox img-hover-zoom"
                                                                       data-glightbox="type: image; zoomable: true;"
                                                                       href="../assets/images/draggable/04.jpg">
                                                                        <img alt=""
                                                                             class="w-100 rounded" src="../assets/images/draggable/04.jpg">
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="img-hover-zoom">
                                                                    <a class="glightbox img-hover-zoom"
                                                                       data-glightbox="type: image; zoomable: true;"
                                                                       href="../assets/images/draggable/01.jpg">
                                                                        <img alt=""
                                                                             class="w-100 rounded" src="../assets/images/draggable/01.jpg">
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>Updated
                                                        at 12:45 pm
                                                    </p>
                                                </div>


                                            </li>

                                            <li class="timeline-section">
                                                <div class="timeline-icon">
                              <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                P
                              </span>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class=" f-s-16">
                                <span class="text-primary f-s-16 mb-0">Pinkie
                                <span class="text-secondary ms-2">uploaded
                                  <span class="text-dark f-w-600 me-2 ms-2">2</span>attachment <span
                                        class="badge text-outline-primary me-2">#reaserchteam</span></span>
                                </span>
                                                    </div>

                                                    <div class="mt-3">
                                                        <button class="btn btn-primary"
                                                                type="button">Accept
                                                        </button>
                                                        <button class="btn btn-outline-primary"
                                                                type="button">Rejects
                                                        </button>
                                                    </div>
                                                    <p class="f-w-500 mt-2 mb-0">
                                                        <i class="ph ph-clock me-1 align-middle"></i>4 days
                                                        ago
                                                    </p>
                                                </div>


                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="security-tab" class="tab-pane fade" id="security-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="card security-card-content">
                            <div class="card-body">
                                <div class="account-security">
                                    <div class="row align-items-center">
                                        <div class="col-sm-8">
                                            <h5 class="text-primary f-w-600">Account Security</h5>
                                            <p class=" account-discription text-secondary f-s-16 mt-2 mb-0">
                                                your account is valuable to
                                                hackers. to make 2-step verification very secure, use
                                                your phone's built-in security key</p>
                                        </div>
                                        <div class="col-sm-4 account-security-img">
                                            <img alt="" class="w-180"
                                                 src="../assets/images/setting-app/account.png">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row security-box-card align-items-center">
                                    <div class="col-md-3 position-relative">
                                                        <span><img alt="" class="w-35 h-35 anti-code"
                                                                   src="../assets/images/setting-app/google.png"></span>
                                        <p
                                            class="security-box-title text-dark f-w-500 f-s-16 ms-5 security-code">
                                            Authentication</p>
                                    </div>
                                    <div class="col-md-6 security-discription">
                                        <p class=" text-secondary f-s-16">It encompasses various methods
                                            to ensure that the person requesting access is indeed who
                                            they claim to be. Here are the key components and features
                                            of Google Authentication:
                                        </p>
                                        <span class="badge text-light-secondary p-2"> <i
                                                class="ph-fill  ph-check-circle me-1 text-success"></i>secondary</span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-outline-success" type="button">Turn
                                            off
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row security-box-card align-items-center">
                                    <div class="col-md-3 position-relative">
                                                        <span
                                                            class="bg-primary h-35 w-35 d-flex-center b-r-50 anti-code">
                                                            <i class="ph-light  ph-codepen-logo f-s-18"></i></span>
                                        <p
                                            class="security-box-title text-dark f-w-500 f-s-16 ms-5 security-code">
                                            Anti-
                                            Code</p>
                                    </div>
                                    <div class="col-md-6 security-discription">
                                        <p class="text-secondary f-s-16">An anti-phishing code is a
                                            security feature used by various online platforms,<br>
                                            especially in financial and cryptocurrency services,
                                        </p>
                                        <span class="badge text-light-secondary p-2"> <i
                                                class="ph-fill  ph-x-circle me-1 text-primary"></i>secondary</span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-primary" type="button">Turn On</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row security-box-card align-items-center">
                                    <div class="col-md-3 position-relative">
                                                        <span
                                                            class="bg-success h-35 w-35 d-flex-center b-r-50 anti-code">
                                                            <i class="ph-light  ph-file-archive f-s-18"></i></span>
                                        <p
                                            class="security-box-title text-dark f-w-500 f-s-16 ms-5 security-code">
                                            Whitelist
                                        </p>
                                    </div>
                                    <div class="col-md-6 security-discription">
                                        <p class="text-secondary f-s-16">An anti-phishing code is a
                                            security feature used by various online platforms,<br>
                                            especially in financial and cryptocurrency services,
                                        </p>

                                    </div>
                                    <div class="col-md-3 text-end">
                                        <p>In development</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card security-card-content">
                            <div class="card-body">
                                <div class="account-security">
                                    <div class="row align-items-center">
                                        <div class="col-sm-9">
                                            <h5 class="text-primary f-w-600">Devices and active
                                                sessions</h5>
                                            <p class="account-discription text-secondary f-s-16 mt-3">
                                                your account is valuable to
                                                hackers. to make 2-step verifivcation very secure, use
                                                your phone's built-in security key</p>
                                        </div>
                                        <div class="col-sm-3 account-security-img">
                                            <img alt="" class="w-150"
                                                 src="../assets/images/setting-app/device.png">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-xxl-6">
                                <ul class="active-device-session active-device-list" id="shareMenuLeft">
                                    <li>
                                        <div class="card share-menu-active">
                                            <div class="card-body">
                                                <div class="device-menu-item" draggable="false">
                                                                    <span class="device-menu-img">
                                                                        <i class="ph-duotone  ph-laptop f-s-40 text-success"></i>
                                                                    </span>
                                                    <div class="device-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Apple Mac
                                                            10.15.7</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">
                                                            switzerland 201.36.24.108</p>
                                                    </div>
                                                    <div class="device-menu-icons">

                                                                        <span
                                                                            class="badge text-light-secondary p-2 f-s-16">
                                                                            <i
                                                                                class="ph-fill  ph-check-circle me-1 text-success"></i>Online</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="device-menu-item " draggable="false">
                                                                    <span class="device-menu-img">
                                                                        <i class="ph-duotone  ph-device-mobile f-s-40 text-primary"></i>
                                                                    </span>
                                                    <div class="device-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Apple Iphone ios
                                                            15.0.2</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">
                                                            Ukrain
                                                            176.38.19.14</p>
                                                    </div>
                                                    <div class="device-menu-icons">

                                                                        <span
                                                                            class="badge text-light-secondary p-2 f-s-16">
                                                                            <i
                                                                                class="ph-fill  ph-x-circle me-1 text-primary"></i>Offline</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="device-menu-item " draggable="false">
                                                                    <span class="device-menu-img">
                                                                        <i class="ph-duotone  ph-device-mobile f-s-40 text-primary"></i>
                                                                    </span>
                                                    <div class="device-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Apple Iphone ios
                                                            15.0.2</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">Afrika
                                                            176.49.19.13</p>
                                                    </div>
                                                    <div class="device-menu-icons">

                                                                        <span
                                                                            class="badge text-light-secondary p-2 f-s-16">
                                                                            <i
                                                                                class="ph-fill  ph-x-circle me-1 text-primary"></i>Offline</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                            <div class="col-lg-12 col-xxl-6">
                                <ul class="active-device-session  active-device-list"
                                    id="shareMenuRight">
                                    <li>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="device-menu-item " draggable="false">
                                                                    <span class="device-menu-img">
                                                                        <i class="ph-duotone  ph-device-mobile f-s-40 text-primary"></i>
                                                                    </span>
                                                    <div class="device-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Apple Mac
                                                            10.15.7</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">
                                                            Amerika 201.136.24.108</p>
                                                    </div>
                                                    <div class="device-menu-icons">

                                                                        <span
                                                                            class="badge text-light-secondary p-2 f-s-16">
                                                                            <i
                                                                                class="ph-fill  ph-x-circle me-1 text-primary"></i>Offline</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="device-menu-item " draggable="false">
                                                                    <span class="device-menu-img">
                                                                        <i class="ph-duotone  ph-device-mobile f-s-40 text-primary"></i>
                                                                    </span>
                                                    <div class="device-menu-content">
                                                        <h6 class="mb-0">Windows 10</h6>
                                                        <p class="mb-0 text-secondary">
                                                            portuguese 176.38.19.14</p>
                                                    </div>
                                                    <div class="device-menu-icons">

                                                                        <span
                                                                            class="badge text-light-secondary p-2 f-s-16">
                                                                            <i
                                                                                class="ph-fill  ph-x-circle me-1 text-primary"></i>Offline</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <div class="card security-card-content">
                            <div class="card-body">
                                <div class="account-security mb-2">
                                    <div class="row align-items-center">
                                        <div class="col-sm-9">
                                            <h5 class="text-primary f-w-600">Change Password</h5>
                                            <p class="account-discription text-secondary f-s-16 mt-3">To
                                                change your password,
                                                please fill in the fields below. your password must
                                                contain at least 8 caracter, it must also include at
                                                least one upper case letter, one lower case letter, one
                                                number and one special character.</p>
                                        </div>
                                        <div class="col-sm-3 account-security-img">
                                            <img alt="" class="w-150"
                                                 src="../assets/images/setting-app/password.png">
                                        </div>
                                    </div>
                                </div>

                                <div class="app-form">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label class="form-label" for="password">Current
                                                Password</label>
                                            <div class="input-group input-group-password mb-3">
                                                                <span class="input-group-text b-r-left"><i
                                                                        class="ph-bold  ph-lock f-s-20"></i></span>
                                                <input aria-label="Amount (to the nearest dollar)"
                                                       class="form-control"
                                                       id="password" placeholder="********"
                                                       type="password">
                                                <!-- <span class="input-group-text b-r-right"><i
                                                        class="ph ph-eye-slash f-s-20 eyes-icon1" onclick="togglePasswordVisibility()"></i></span> -->
                                                <span class="input-group-text b-r-right"><i
                                                        class="ph ph-eye-slash f-s-20 eyes-icon "
                                                        id="showPassword"></i></span>

                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <label class="form-label" for="password">New
                                                Password</label>
                                            <div class="input-group input-group-password mb-3">
                                                                <span class="input-group-text b-r-left"><i
                                                                        class="ph-bold  ph-lock f-s-20"></i></span>
                                                <input aria-label="Amount (to the nearest dollar)"
                                                       class="form-control"
                                                       id="password1" placeholder="********"
                                                       type="password">
                                                <span class="input-group-text b-r-right"><i
                                                        class="ph ph-eye-slash f-s-20 eyes-icon1 "
                                                        id="showPassword1"></i></span>

                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <label class="form-label" for="password">Confirm
                                                Password</label>
                                            <div class="input-group input-group-password mb-3">
                                                                <span class="input-group-text b-r-left"><i
                                                                        class="ph-bold  ph-lock f-s-20"></i></span>
                                                <input aria-label="Amount (to the nearest dollar)"
                                                       class="form-control"
                                                       id="password2" placeholder="********"
                                                       type="password">
                                                <span class="input-group-text b-r-right"><i
                                                        class="ph ph-eye-slash f-s-20 eyes-icon2"
                                                        id="showPassword2"></i></span>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                    <div aria-labelledby="privacy-tab" class="tab-pane fade" id="privacy-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="card equal-card privacy-card">
                            <div class="card-header">
                                <h5>Privacy </h5>
                            </div>
                            <div class="card-body">
                                <h6>ACCESS</h6>
                                <div class="row">
                                    <div class="col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item">
                                                <label class="form-check-label" for="inlineCheckbox1">
                                                                    <span class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class="privacy-img bg-success h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph-bold  ph-lock f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Private</span>
                                                                            <span class="mb-0 text-secondary f-s-16"> only users you choose can access</span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input checked
                                                       class="form-check-input form-check- b-r-100 w-25 h-25"
                                                       id="inlineCheckbox1" name="option"
                                                       type="radio" value="option1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item ">
                                                <label class="form-check-label" for="inlineCheckbox2">
                                                                    <span
                                                                        class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class="privacy-img  bg-primary h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph ph-globe f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Public</span>
                                                                            <span class="mb-0 text-secondary f-s-16">anyone
                                                                                with the link can access </span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input class="form-check-input b-r-100 w-25 h-25"
                                                       id="inlineCheckbox2" name="option" type="radio"
                                                       value="option2">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h6>USERS</h6>
                                <div class="row">
                                    <div class=" col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item">
                                                <label class="form-check-label" for="inlineCheckbox3">
                                                                    <span
                                                                        class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class="privacy-img  bg-primary h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph-bold  ph-lock f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">users in the table
                                                                            </span>
                                                                            <span class="mb-0 text-secondary f-s-16">
                                                                                users in the table can sign in</span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input checked
                                                       class="form-check-input b-r-100 w-25 h-25"
                                                       id="inlineCheckbox3" name="useroption"
                                                       type="radio" value="useroption1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item">
                                                <label class="form-check-label" for="inlineCheckbox4">
                                                                    <span
                                                                        class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class=" privacy-img bg-secondary h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph-bold  ph-lock f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500"> production team
                                                                            </span>
                                                                            <span class="mb-0 text-secondary f-s-16">
                                                                                team member can sign in
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input checked
                                                       class="form-check-input b-r-100 w-25 h-25"
                                                       id="inlineCheckbox4" name="useroption"
                                                       type="radio" value="useroption2">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item">
                                                <label class="form-check-label" for="inlineCheckbox5">
                                                                    <span
                                                                        class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class="privacy-img bg-info h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph-bold  ph-lock f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Anyone from domain</span>
                                                                            <span class="mb-0 text-secondary f-s-16">
                                                                                users
                                                                                with your eamil domain</span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input checked
                                                       class="form-check-input b-r-100 w-25 h-25"
                                                       id="inlineCheckbox5" name="useroption"
                                                       type="radio" value="useroption3">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" col-xxl-6">
                                        <div class="setting-privacy-card form-selectgroup">
                                            <div class="select-item">
                                                <label class="form-check-label" for="inlineCheckbox6">
                                                                    <span
                                                                        class="d-flex align-items-center position-relative">
                                                                        <span
                                                                            class="privacy-img bg-danger h-40 w-40 d-flex-center b-r-50 position-absolute">
                                                                            <i class="ph-bold  ph-lock f-s-18"></i>
                                                                        </span>
                                                                        <span class="privacy-content ms-5 me-2">
                                                                            <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Any email in table</span>
                                                                            <span class="mb-0 text-secondary f-s-16">Anyone
                                                                                included email in a table</span>
                                                                        </span>
                                                                    </span>
                                                </label>

                                                <input checked
                                                       class="form-check-input b-r-100 w-25 h-25"
                                                       id="inlineCheckbox6" name="useroption"
                                                       type="radio" value="useroption4">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" col-xxl-12">
                                        <div class="publishe-card">
                                            <div>
                                                <h6 class="m-0">Publishing</h6>
                                                <p class="mb-0 f-s-16 text-secondary">your project is
                                                    published</p>
                                            </div>
                                            <div>
                                                <button class="btn btn-light-primary"
                                                        type="button">Unpublish
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="notification-tab" class="tab-pane fade" id="notification-tab-pane"
                         role="tabpanel" tabindex="0">

                        <div class="card">
                            <div class="card-header">
                                <h5>Notification</h5>
                            </div>
                            <div class="card-body">
                                <div class="notification-content">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="notification-email">
                                                <h6>By Email</h6>
                                                <div class="select-item">
                                                    <input class="form-check-input form-check-primary w-25 h-25"
                                                           id="checkbox-email" type="checkbox"
                                                           value="option1">
                                                    <label class="form-check-label"
                                                           for="checkbox-email">
                                                                        <span
                                                                            class="d-flex align-items-center position-relative">

                                                                            <span class="ms-3 privacy-content">
                                                                                <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Comments</span>
                                                                                <span class="text-secondary mb-0"> notified
                                                                                    posts on comment
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="select-item">
                                                    <input class="form-check-input form-check-primary w-25 h-25"
                                                           id="checkbox-email1" type="checkbox"
                                                           value="option2">
                                                    <label class="form-check-label"
                                                           for="checkbox-email1">
                                                                        <span class="d-flex align-items-center">

                                                                            <span class="ms-3 privacy-content">
                                                                                <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Condidates</span>
                                                                                <span class="text-secondary mb-0"> notified
                                                                                    candidate applies </span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="select-item">
                                                    <input class="form-check-input form-check-primary w-25 h-25"
                                                           id="checkbox-email2" type="checkbox"
                                                           value="option3">
                                                    <label class="form-check-label"
                                                           for="checkbox-email2">
                                                                        <span class="d-flex align-items-center">

                                                                            <span class="ms-3 privacy-content">
                                                                                <span class="mb-0 text-dark txt-ellipsis-1 f-s-16 f-w-500">Offers</span>
                                                                                <span class="text-secondary mb-0"> notified
                                                                                    accepts or rejects</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-lg-6 mt-lg-0 mt-3">
                                            <div class="notification-push">
                                                <h6 class="mb-0">Push Notification</h6>
                                                <p class="mb-0">This are delivered via sms to your
                                                    mobile phone.</p>

                                                <div class="d-flex align-items-center gap-1 mt-3">
                                                    <input class="form-check-input form-check-primary f-s-18 mb-1 m-1"
                                                           id="radio_default1" name="flexRadioDefault"
                                                           type="radio">
                                                    <label class="form-check-label"
                                                           for="radio_default1">
                                                        <span class="mb-0 f-s-16 f-w-500"> Everything </span>
                                                    </label>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 mt-3">
                                                    <input class="form-check-input form-check-primary f-s-18 mb-1 m-1"
                                                           id="radio_default2" name="flexRadioDefault"
                                                           type="radio">
                                                    <label class="form-check-label"
                                                           for="radio_default2">
                                                        <span class="mb-0 f-s-16 f-w-500"> Same as a email</span>
                                                    </label>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 mt-3">
                                                    <input class="form-check-input form-check-primary f-s-18 mb-1 m-1"
                                                           id="radio_default3" name="flexRadioDefault"
                                                           type="radio">
                                                    <label class="form-check-label"
                                                           for="radio_default3">
                                                        <span class="mb-0 f-s-16 f-w-500"> No push notification</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="app-divider-v"></div>

                                    <div class="col-12">
                                        <ul class="notified-contet share-menu-list">

                                            <li>
                                                <div class="share-menu-item mb-4">
                                                                    <span
                                                                        class="share-menu-img text-outline-primary h-45 w-45 d-flex-center b-r-10">
                                                                        <i
                                                                            class="ph-bold  ph-device-mobile-speaker f-s-30"></i>
                                                                    </span>
                                                    <div class="share-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Mobile push
                                                            notification</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">
                                                            Receive the all
                                                            notifications via your mobile app</p>
                                                    </div>
                                                    <div class="form-check form-switch d-flex mt-1">

                                                        <input checked
                                                               class="form-check-input  form-check-primary ms-3 fs-3 me-3"
                                                               id="basic-switch-6" type="checkbox">
                                                        <label class="form-check-label pt-2"
                                                               for="basic-switch-6"></label>
                                                    </div>
                                                </div>


                                            </li>
                                            <li>
                                                <div class="share-menu-item mb-4">
                                                                    <span
                                                                        class="share-menu-img text-outline-success h-45 w-45 d-flex-center b-r-10">
                                                                        <i class="ph-bold  ph-desktop f-s-30"></i>
                                                                    </span>
                                                    <div class="share-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">desktop push
                                                            notification</h6>
                                                        <p class="mb-0 txt-ellipsis-1 text-secondary">
                                                            Receive the all
                                                            notifications via your desktop app</p>
                                                    </div>
                                                    <div class="form-check form-switch d-flex mt-1">

                                                        <input checked
                                                               class="form-check-input  form-check-primary ms-3 fs-3 me-3"
                                                               id="basic-switch-4" type="checkbox">
                                                        <label class="form-check-label pt-2"
                                                               for="basic-switch-4"></label>
                                                    </div>
                                                </div>


                                            </li>
                                            <li>
                                                <div class="share-menu-item mb-4">
                                                                    <span
                                                                        class="share-menu-img text-outline-danger h-45 w-45 d-flex-center b-r-10">
                                                                        <i class="ph-bold  ph-watch f-s-30"></i>
                                                                    </span>
                                                    <div class="share-menu-content">
                                                        <h6 class="mb-0 txt-ellipsis-1">Smartwatch push
                                                            notification
                                                        </h6>
                                                        <p class="mb-0  txt-ellipsis-1 text-secondary">
                                                            Receive the all
                                                            notifications via your smartwatch app</p>
                                                    </div>
                                                    <div class="form-check form-switch d-flex mt-1">

                                                        <input checked
                                                               class="form-check-input  form-check-primary ms-3 fs-3 me-3"
                                                               id="basic-switch-5" type="checkbox">
                                                        <label class="form-check-label pt-2"
                                                               for="basic-switch-5"></label>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div aria-labelledby="subscription-tab" class="tab-pane fade" id="subscription-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="card">
                            <div class="card-header">
                                <h5>Subscription</h5>
                            </div>
                            <div class="card-body">
                                <div class="subscription-plan">
                                    <div class="row">
                                        <div class="col-xxl-6">
                                            <div class="plan-choose">
                                                <h6 class="mb-0">Choose plan</h6>
                                                <div>
                                                    <button class="btn btn-light-primary"
                                                            type="button">Quartely
                                                    </button>
                                                    <button class="btn btn-light-secondary"
                                                            type="button">Yearly
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-selectgroup mt-5">
                                                <div class="select-item">
                                                    <input checked
                                                           class="form-check-input form-check-primary w-20 h-20"
                                                           id="planCheckbox1"
                                                           name="planoption" type="radio"
                                                           value="planoption1">
                                                    <label class="form-check-label"
                                                           for="planCheckbox1">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="ms-2">
                                                                                <span class="fs-6 mb-0">Mark Moen</span>
                                                                                <span class="d-block text-secondary mb-0">
                                                                                    UI/UX
                                                                                    Designer</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                    <div class="select-item-2 ms-2">
                                                        <h6 class="fs-6 mb-0">$69.44</h6>
                                                        <p class="text-secondary">1 users/quartly</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-selectgroup mt-4">
                                                <div class="select-item">
                                                    <input class="form-check-input form-check-primary w-20 h-20"
                                                           id="planCheckbox2" name="planoption"
                                                           type="radio" value="planoption2">
                                                    <label class="form-check-label"
                                                           for="planCheckbox2">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="ms-2">
                                                                                <span class="fs-6 mb-0">Mark Moen</span>
                                                                                <span class="d-block text-secondary">UI/UX
                                                                                    Designer</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                    <div class="select-item-2 ms-2">
                                                        <h6 class="fs-6 mb-0">$69.44</h6>
                                                        <p class="text-secondary">1 users/quartly</p>
                                                    </div>
                                                </div>

                                                <ul class="feachers-list">
                                                    <li class="f-s-16 text-secondary"><i
                                                            class="ph-bold  ph-check-circle me-2 f-s-16 text-success"></i>40
                                                        doenload per day.
                                                    </li>
                                                    <li class="f-s-16 text-secondary"><i
                                                            class="ph-bold  ph-check-circle me-2 f-s-16 text-success"></i>access
                                                        to all products or bundles.
                                                    </li>
                                                    <li class="f-s-16 text-secondary"><i
                                                            class="ph-bold  ph-check-circle me-2 f-s-16 text-success"></i>early
                                                        access to new/beta relese features.
                                                    </li>

                                                </ul>
                                                <div class="app-divider-v mt-4 mb-4"></div>
                                                <div class="team-accounts mb-4">
                                                                    <span class="privacy-content">
                                                                        <span class="mb-0 f-s-16 f-w-500">Team Accounts</span>
                                                                        <span class="text-secondary f-s-14 mb-0">starting
                                                                            at 5
                                                                            users in team plan , you can increase.</span>
                                                                    </span>
                                                    <div class="simplespin ms-3">
                                                        <button
                                                            class="circle-btn btn-light-primary decrement">
                                                            -
                                                        </button>
                                                        <input class="app-small-touchspin count"
                                                               type="text"
                                                               value="25">
                                                        <button
                                                            class="circle-btn btn-light-primary increment">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-selectgroup mt-4">
                                                <div class="select-item">
                                                    <input class="form-check-input form-check-primary w-20 h-20"
                                                           id="planCheckbox3" name="planoption"
                                                           type="radio" value="planoption3">
                                                    <label class="form-check-label"
                                                           for="planCheckbox3">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="ms-2">
                                                                                <span class="fs-6 mb-0">Business Pro</span>
                                                                                <span class="d-block text-secondary mb-0">
                                                                                    for
                                                                                    big teams</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                    <div class="feachers-list ms-2">
                                                        <h6 class="fs-6 mb-0">$250.44</h6>
                                                        <p class="text-secondary">31 users/quartly</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 mt-xxl-0 mt-5">
                                            <div class="plan-choose">
                                                <h6 class="mb-0">Payment plan</h6>
                                                <div>
                                                    <button class="btn btn-light-primary"
                                                            type="button">Credit
                                                        card
                                                    </button>
                                                    <button class="btn btn-light-secondary"
                                                            type="button">Paypal
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-selectgroup mt-5">
                                                <div class="select-item d-flex">
                                                    <input class="form-check-input form-check-primary w-20 h-20"
                                                           id="paymentCheckbox1" name="paymentoption"
                                                           type="radio" value="paymentoption1">
                                                    <label class="form-check-label"
                                                           for="paymentCheckbox1">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="ms-2">
                                                                                <span class="f-s-16 mb-0 f-w-500">**** 4426</span>
                                                                                <span
                                                                                    class="d-block text-secondary f-s-16 mb-0">
                                                                                    visa card</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                    <div class="ms-2">
                                                        <img alt=""
                                                             src="../assets/images/icons/visa-icon.png">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-selectgroup mt-4">
                                                <div class="select-item d-flex">
                                                    <input checked
                                                           class="form-check-input form-check-primary w-20 h-20"
                                                           id="paymentCheckbox2"
                                                           name="paymentoption" type="radio"
                                                           value="paymentoption2">
                                                    <label class="form-check-label"
                                                           for="paymentCheckbox2">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="ms-2">
                                                                                <span class="f-s-16 mb-0 f-w-500">**** 6790</span>
                                                                                <span
                                                                                    class="d-block text-secondary f-s-16 mb-0">
                                                                                    Master card</span>
                                                                            </span>
                                                                        </span>
                                                    </label>
                                                    <div class="ms-2">
                                                        <img alt=""
                                                             src="../assets/images/icons/master-icon.png">
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-success w-100 mt-4" type="button">+
                                                Add New Card
                                            </button>


                                            <div class="form-selectgroup p-3 mt-4">

                                                <h6 class="mb-2">Discount code</h6>
                                                <div class="input-group mb-3">
                                                    <input aria-describedby="button-addon2"
                                                           aria-label="Recipient's username"
                                                           class="form-control form-check-primary b-r-left"
                                                           placeholder="20FGJKYSD"
                                                           type="text">
                                                    <button class="btn btn-secondary b-r-right"
                                                            id="button-addon2" type="button">Apply
                                                    </button>
                                                </div>

                                                <P class="text-success f-s-16 mb-0">30% discount code
                                                    applied
                                                </P>
                                                <div class="team-accounts mt-3 mb-4 p-0">
                                                                    <span class="privacy-content me-2">
                                                                        <span class="mb-0 f-s-16 f-w-500">Team Plan</span>
                                                                        <span class="text-secondary f-s-14 mb-0">5 Users
                                                                            Quartely</span>
                                                                    </span>
                                                    <h4>$789.0</h4>
                                                </div>

                                                <div class="app-divider-v mt-4 mb-4"></div>
                                                <div class="plan-choose">
                                                    <h6 class="mb-0">Payment plan</h6>
                                                    <h5 class="text-success f-s-18">-$57.90</h5>
                                                </div>
                                                <div class="app-divider-v mt-4 "></div>
                                                <div class="team-accounts mt-3 mb-4 p-0">
                                                                    <span class="privacy-content">
                                                                        <span class="mb-0 f-s-16 f-w-500">Total</span>
                                                                        <span class="text-secondary f-s-14 mb-0">Next
                                                                            payment
                                                                            will charge 10th of january 2030</span>
                                                                    </span>
                                                    <h6>$789.0</h6>
                                                </div>
                                                <div class="app-divider-v mt-4"></div>
                                                <button class="btn btn-success w-100 " type="button">PAY
                                                    NOW
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="Connection-tab" class="tab-pane fade" id="Connection-tab-pane"
                         role="tabpanel" tabindex="0">
                        <div class="row">
                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/geethub.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">GitHub</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-7" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-7"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">GitHub can be connected to
                                            various continuous integration</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/slack.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Slack</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-8" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-8"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">send notifications to
                                            channels and create various projects</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/google.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Google</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-9" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-9"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">The core mission of Google
                                            is to organize the world's information.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/figma.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Figma</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-10" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-10"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Figma is a web-based
                                            design tool focused on collaborative design.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/drive.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Drive</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-11" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-11"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Google Drive is a
                                            comprehensive file storage and service.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/drop-box.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Drop Box</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-12" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-12"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">The service is designed to
                                            safeguard files malfunctions</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/facebook.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Facebook</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-13" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-13"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Facebook's journey from a
                                            university network to a global social media.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/instagram.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Instagram</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-14" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-14"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Instagram's mission is to
                                            bring people closer to the things and people.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/twitter.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Twitter</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary  fs-3"
                                                       id="basic-switch-15" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-15"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Twitter, now known as X,
                                            is a social media diffrent platform</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/dribble.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Dribble</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-16" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-16"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">Dribbble is a
                                            self-promotion and social networking platform.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/linkdin.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Linkedin</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input  form-check-primary fs-3"
                                                       id="basic-switch-17" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-17"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4"> LinkedIn boasts over 1
                                            billion registered members globally.</p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xxl-4">
                                <div class="card conection-setting">
                                    <div class="card-body">
                                        <div class="conection-item">
                                            <div class="position-relative">
                                                                <span class="position-absolute">
                                                                    <img alt=""
                                                                         class="w-35 h-35"
                                                                         src="../assets/images/setting-app/behance.png">
                                                                </span>
                                                <h5 class="ms-5 mt-1">Behance</h5>
                                            </div>
                                            <div class="form-check form-switch d-flex mt-1">

                                                <input checked=""
                                                       class="form-check-input form-check-primary fs-3"
                                                       id="basic-switch-18" type="checkbox">
                                                <label class="form-check-label pt-2"
                                                       for="basic-switch-18"></label>
                                            </div>
                                        </div>

                                        <p class="text-secondary f-s-16 mt-4">The platform allows
                                            creative professionals across various industries. </p>
                                    </div>
                                    <div class="card-footer text-end text-d-underline link-primary">
                                        <a href="#">View intigration</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--setting app end -->
    </div>
@endsection

@section('script')
    <!--customizer-->
    <div id="customizer"></div>

    <!-- apexcharts-->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Glight js -->
    <script src="{{ asset('assets/vendor/glightbox/glightbox.min.js') }}"></script>

    <!-- sweetalert js-->
    <script src="{{ asset('assets/vendor/sweetalert/sweetalert.js') }}"></script>

    <!-- select2 -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>

    <!--setting js  -->
    <script src="{{ asset('assets/js/setting.js') }}"></script>
@endsection
