@extends('layout.master')
@section('title', 'Ecommerce Dashboard')
@section('css')

    <!-- apexcharts css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">

    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- filepond css -->
    <link href="{{ asset('assets/vendor/filepond/filepond.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/filepond/image-preview.min.css') }}" rel="stylesheet">

@endsection
@section('main-content')
    <div class="container-fluid mt-3">
        <div class="row">

            <div class="col-lg-6 col-xxl-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card project-total-card">
                            <div class="card-body">
                                <div class="d-flex position-relative">
                                    <h5 class="text-dark txt-ellipsis-1">Total Hours</h5>
                                    <div class="clock-box">
                                        <div class="clock">
                                            <div class="hour" id="hour"></div>
                                            <div class="min" id="min"></div>
                                            <div class="sec" id="sec"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex">
                                        <h2 class="text-info-dark hour-display">00H</h2>
                                    </div>
                                    <div class="progress-labels mg-t-40">
                                        <span class="text-info">Productive</span>
                                        <span class="text-info">Middle</span>
                                        <span class="text-info">Idle</span>
                                    </div>
                                    <div class="custom-progress-container info-progress">
                                        <div class="progress-bar productive"></div>
                                        <div class="progress-bar middle"></div>
                                        <div class="progress-bar idle"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card bg-info-300 project-details-card">
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white-300 text-info-dark p-1  b-r-10">📱 Mobile app</span>
                                    <span class="badge dashed-1-info text-info-dark ms-2 p-1  b-r-10">Marketing</span>
                                </div>
                                <div class="my-4">
                                    <h5 class="f-w-600 text-info-dark txt-ellipsis-1">Project Alpha</h5>
                                    <p class="text-info f-s-13 txt-ellipsis-1 mb-0">Revolutionizing ideas,
                                        empowering innovation, and driving success.</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3">
                                    <ul class="avatar-group">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-primary b-2-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-success b-2-success"
                                            data-bs-title="Lennon Briggs" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-info b-2-info"
                                            data-bs-title="Maya Horton" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </li>
                                    </ul>
                                    <span class="badge bg-white-300 text-info-dark ms-2 ">🔥 1H left</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card bg-success-300 project-details-card">
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white-300 text-success-dark p-1 b-r-10">⚡ API </span>
                                    <span class="badge bg-transparent dashed-1-dark-light text-success-dark ms-2 p-1 b-r-10">Web Development</span>
                                </div>
                                <div class="my-4">
                                    <h5 class="f-w-600 text-success-dark txt-ellipsis-1">Project Beta</h5>
                                    <p class="text-success f-s-13 txt-ellipsis-1 mb-0"> Innovating solutions
                                        for seamless task management efficiency.</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3">
                                    <ul class="avatar-group">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-primary b-2-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-danger b-2-danger"
                                            data-bs-title="Maya Horton" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </li>
                                    </ul>
                                    <span class="badge bg-white-300 text-success-dark ms-2 ">✨ 2D left</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card core-teams-card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <h5 class="text-dark f-w-600 txt-ellipsis-1">💼 Core Teams</h5>
                                </div>
                                <div>
                                    <h2 class="text-warning-dark my-4 d-inline-flex align-items-baseline">1k
                                        <span class="f-s-12 text-dark">Team Members</span></h2>
                                    <ul class="avatar-group justify-content-start ">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-primary b-2-light"
                                            data-bs-title="Sabrina Torres" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-success b-2-light"
                                            data-bs-title="Eva Bailey" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-danger b-2-light"
                                            data-bs-title="Michael Hughes" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </li>
                                        <li class="text-bg-secondary h-35 w-35 d-flex-center b-r-50"
                                            data-bs-title="10 More" data-bs-toggle="tooltip">
                                            10+
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-4">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div>
                            <input class="file-uploader-box filelight file-light-info"
                                   data-allow-reorder="true"
                                   id="fileUploaderBox" multiple="multiple" type="file">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card ">
                            <div class="card-body">
                                <div class="project-expense" id="projectExpense"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6 col-xxl-4 ">
                <div class="card project-connect-card">
                    <div class="card-body pb-0">
                        <div class="text-center">
                            <h5 class=" mb-2 f-s-24">Get started <span class="text-primary f-w-700">Effortlessly.</span>
                            </h5>
                            <p class="f-s-14 text-dark pb-0 txt-ellipsis-2">
                                Connect your team's tools and unlock a unified view of every project's
                                progress, deadlines, and team contributions.
                            </p>
                        </div>
                        <div class="connect-chat-box">
                            <div class="avatar-connect-box">
                                <img alt="logo" class="avatar-connect-logo"
                                     src="{{ asset('../assets/images/dashboard/project/avatar.png') }}">
                                <img alt="logo" class="dribbble-connect-logo"
                                     src="{{ asset('../assets/images/dashboard/project/dribbble.png') }}">
                            </div>
                            <img alt="img" src="{{ asset('../assets/images/dashboard/project/chat.png') }}">
                            <img alt="logo"
                                 class="slack-logo animate__shakeY animate__animated animate__infinite animate__slower"
                                 src="{{ asset('../assets/images/dashboard/project/slack.png') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3 ">
                <div class="p-3">
                    <h5>Tracker</h5>
                </div>
                <div class="card">
                    <div class="card-body position-relative">
                        <div class="time-tracker">
                            <div class="share-time">
                                <div class="dropdown">
                                                <span aria-expanded="false"
                                                      class="w-35 h-35 bg-primary-300 text-info-dark rounded p-2 d-flex-center" data-bs-toggle="dropdown" role="button">
                                                    <i class="iconoir-share-android-solid"></i>
                                                </span>
                                    <ul class="dropdown-menu  dropdown-menu-end rounded">
                                        <li><a class="dropdown-item f-s-14" href="#"><i
                                                    class="iconoir-instagram text-danger-dark me-2 f-s-18 align-text-top"></i>Instagram</a>
                                        </li>
                                        <li><a class="dropdown-item f-s-14" href="#"><i
                                                    class="iconoir-twitter text-twitter me-2 f-s-18 align-text-top"></i>Twitter</a>
                                        </li>
                                        <li><a class="dropdown-item f-s-14" href="#"><i
                                                    class="iconoir-chat-lines text-whatsapp me-2 f-s-18 align-text-top"></i>Messenger</a>
                                        </li>
                                        <li><a class="dropdown-item f-s-14" href="#"><i
                                                    class="iconoir-more-horiz text-dark me-2 f-s-18 align-text-top"></i>Other
                                                Apps</a></li>
                                    </ul>
                                </div>
                            </div>
                            <h2 class="timer-display f-w-600">00:00:00</h2>
                            <div class="controls">
                                <button class="btn btn-light-primary icon-btn b-r-18" id="start-btn"><i
                                        class="iconoir-play-solid"></i></button>
                                <button class="btn btn-danger icon-btn b-r-18" disabled id="stop-btn"><i
                                        class="iconoir-square"></i></button>
                                <button class="btn btn-light-info icon-btn b-r-18" id="reset-btn"><i
                                        class="iconoir-refresh"></i></button>
                            </div>
                        </div>

                        <ul class="tracker-history-list app-scroll mt-3">
                            <li class="bg-info-300">
                                <div>
                                    <h6 class="text-info-dark mb-0">Session 1</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2">
                                    00:01:23
                                </div>
                            </li>

                            <li class="bg-primary-300">
                                <div>
                                    <h6 class="text-primary-dark mb-0">Session 2</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2">
                                    00:02:45
                                </div>
                            </li>
                            <li class="bg-danger-300">
                                <div>
                                    <h6 class="text-danger-dark mb-0">Session 3</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2">
                                    00:03:30
                                </div>
                            </li>
                            <li class="bg-warning-300">
                                <div>
                                    <h6 class="text-warning-dark mb-0">Session 4</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2">
                                    00:04:12
                                </div>
                            </li>
                            <li class="bg-success-300">
                                <div>
                                    <h6 class="text-success-dark mb-0">Session 5</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2">
                                    01:06:00
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-xxl-6 order-1-md">
                <div class="p-3">
                    <h5>Project Status</h5>
                </div>
                <div class="card mb-0">
                    <div class="card-body py-2 px-0 overflow-hidden">
                        <div class="table-responsive app-scroll ">
                            <table class="table align-middle project-status-table mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Project</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">TeamLead</th>
                                    <th scope="col">priority</th>
                                    <th scope="col">Remarks</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-success-dark text-nowrap">Web Redesign</h6>
                                    </td>
                                    <td><span
                                            class="badge text-light-warning f-s-9 f-w-700">In Progress</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto "
                                           data-bs-title="Athena Stewart" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/2.png') }}">
                                        </a>


                                    </td>
                                    <td class="text-success-dark f-w-600">
                                        High
                                    </td>
                                    <td>
                                                    <span class="text-dark f-s-14 f-w-500 text-nowrap"><i
                                                            class="ti ti-circle-filled me-2 f-s-6"></i> Design phase completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-warning-dark text-nowrap">Mobile App</h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-success f-s-9 f-w-700">Completed</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Jane Smith" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/3.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-secondary-dark f-w-600">
                                        Medium
                                    </td>
                                    <td>
                                                    <span class="text-dark f-s-14 f-w-500 text-nowrap"><i
                                                            class="ti ti-circle-filled me-2 f-s-6"></i> Project deployed successfully</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-danger-dark text-nowrap">
                                            Campaign</h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-secondary f-s-9 f-w-700">Not Started</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Mark Lee" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-danger-dark f-w-600">
                                        Low
                                    </td>
                                    <td>
                                                    <span class="text-dark f-s-14 f-w-500 text-nowrap"><i
                                                            class="ti ti-circle-filled me-2 f-s-6"></i> Campaign to begin in December</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-primary-dark text-nowrap">E-Commerce</h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-warning f-s-9 f-w-700">In Progress</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Alice Johnson" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-success-dark f-w-600">
                                        High
                                    </td>
                                    <td>
                                                    <span class="text-dark f-s-14 f-w-500 text-nowrap"><i
                                                            class="ti ti-circle-filled me-2 f-s-6"></i> Initial setup </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-success-dark text-nowrap">Social Media </h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-success f-s-9 f-w-700">Completed</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Sophia Green" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-danger-dark f-w-600">
                                        Low
                                    </td>
                                    <td>
                                                     <span class="text-dark f-s-14 f-w-500 text-nowrap">
                                                         <i class="ti ti-circle-filled me-2 f-s-6"></i> Campaign launched successfully
                                                     </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-success-dark text-nowrap">SEO Optimization</h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-warning f-s-9 f-w-700">In Progress</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Liam Carter" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-success-dark f-w-600">
                                        Medium
                                    </td>
                                    <td>
                                                   <span class="text-dark f-s-14 f-w-500 text-nowrap">
                                                       <i class="ti ti-circle-filled me-2 f-s-6"></i> Keyword analysis ongoing
                                                   </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-success-dark text-nowrap">UI/UX Revamp</h6>
                                    </td>
                                    <td>
                                        <span class="badge text-light-info f-s-9 f-w-700">Scheduled</span>
                                    </td>
                                    <td class="f-w-600 text-dark">
                                        <a class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-secondary m-auto"
                                           data-bs-title="Olivia Brown" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </a>
                                    </td>
                                    <td class="text-danger-dark f-w-600">
                                        Low
                                    </td>
                                    <td>
                                                       <span class="text-dark f-s-14 f-w-500 text-nowrap">
                                                           <i class="ti ti-circle-filled me-2 f-s-6"></i> Resources allocated
                                                       </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="table-footer ">
                    <p class="mb-0 f-s-15 f-w-500 txt-ellipsis-1">Showing 7 to 20 of 20 entries</p>
                    <ul class="pagination app-pagination justify-content-end ">
                        <li class="page-item disabled"><a class="page-link b-r-left"
                                                          href="#">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-5 col-xxl-3">
                <div class="p-3">
                    <h5>Today Tasks</h5>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="task-container slider">
                            <div class="card task-card bg-danger-300">
                                <div class="card-body">
                                    <h6 class="text-danger-dark txt-ellipsis-1">Finalize Project
                                        Proposal</h6>

                                    <ul class="avatar-group justify-content-start my-3">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-success"
                                            data-bs-title="Lennon Briggs" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-danger"
                                            data-bs-title="Maya Horton" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </li>
                                    </ul>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div aria-valuemax="100" aria-valuemin="0"
                                             aria-valuenow="25" class="progress w-100" role="progressbar">
                                            <div class="progress-bar bg-danger-dark progress-bar-striped progress-bar-animated"
                                                 style="width: 68%"></div>
                                        </div>
                                        <span class="badge bg-white-300 text-danger-dark ms-2 ">+ 68%</span>
                                    </div>


                                </div>
                            </div>

                            <div class="card">
                                <div class="d-flex justify-content-between align-items-center rounded p-1 bg-primary-300">
                                                <span class="bg-primary h-35 w-35 d-flex-center rounded">
                                                    <i class="iconoir-group f-s-18"></i>
                                                </span>
                                    <h6 class="mb-0 txt-ellipsis-1">Meeting</h6>
                                    <div class="d-flex gap-2">
                                                          <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                                  class="iconoir-more-horiz f-s-18"></i>
                                                          </span>
                                        <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                class="iconoir-copy f-s-18"></i>
                                                          </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card task-card bg-warning-300">
                                <div class="card-body">
                                    <h6 class="text-warning-dark txt-ellipsis-1">Design Homepage Layout</h6>
                                    <ul class="avatar-group justify-content-start my-3">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/3.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-info"
                                            data-bs-title="Sophia Turner" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/7.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-warning"
                                            data-bs-title="Lucas Green" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/8.png') }}">
                                        </li>
                                    </ul>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div aria-valuemax="100" aria-valuemin="0"
                                             aria-valuenow="25" class="progress w-100" role="progressbar">
                                            <div class="progress-bar bg-warning-dark progress-bar-striped progress-bar-animated"
                                                 style="width: 35%"></div>
                                        </div>

                                        <span class="badge bg-white-400 text-secondary-dark ms-2">+ 35%</span>
                                    </div>
                                </div>

                            </div>

                            <div class="card">
                                <div class="d-flex justify-content-between align-items-center rounded p-1 bg-info-300">
                                                <span class="bg-info h-35 w-35 d-flex-center rounded">
                                                    <i class="iconoir-group f-s-18"></i>
                                                </span>
                                    <h6 class="mb-0 txt-ellipsis-1">Meeting</h6>
                                    <div class="d-flex gap-2">
                                                          <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                                  class="iconoir-more-horiz f-s-18"></i>
                                                          </span>
                                        <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                class="iconoir-copy f-s-18"></i>
                                                          </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="d-flex justify-content-between align-items-center rounded p-1 bg-success-300">
                                                <span class="bg-success h-35 w-35 d-flex-center rounded">
                                                    <i class="iconoir-group f-s-18"></i>
                                                </span>
                                    <h6 class="mb-0 txt-ellipsis-1">Meeting</h6>
                                    <div class="d-flex gap-2">
                                                          <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                                  class="iconoir-more-horiz f-s-18"></i>
                                                          </span>
                                        <span class="w-35 h-35 bg-white-300 text-info-dark rounded p-2 d-flex-center"><i
                                                class="iconoir-copy f-s-18"></i>
                                                          </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card task-card bg-info-300">
                                <div class="card-body">
                                    <h6 class="text-info-dark txt-ellipsis-1">Develop API Integration</h6>
                                    <ul class="avatar-group justify-content-start my-3">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-info">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-info"
                                            data-bs-title="Michael Johnson" data-bs-toggle="tooltip">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/6.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-warning"
                                            data-bs-title="Emily Brown" data-bs-toggle="tooltip">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/5.png') }}">
                                        </li>
                                    </ul>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="60"
                                             class="progress w-100" role="progressbar">
                                            <div class="progress-bar bg-info-dark progress-bar-striped progress-bar-animated"
                                                 style="width: 60%"></div>
                                        </div>
                                        <span class="badge bg-white-400 text-secondary-dark ms-2">+ 60%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card task-card bg-success-300">
                                <div class="card-body">
                                    <h6 class="text-success-dark txt-ellipsis-1">Test User Feedback</h6>
                                    <ul class="avatar-group justify-content-start my-3">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-primary">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/9.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-info"
                                            data-bs-title="Alice Smith" data-bs-toggle="tooltip">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/10.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-success"
                                            data-bs-title="John Doe" data-bs-toggle="tooltip">
                                            <img alt="avatar" class="img-fluid"
                                                 src="{{ asset('../assets/images/avtar/11.png') }}">
                                        </li>
                                    </ul>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="80"
                                             class="progress w-100" role="progressbar">
                                            <div class="progress-bar bg-success-dark progress-bar-striped progress-bar-animated"
                                                 style="width: 80%"></div>
                                        </div>
                                        <span class="badge bg-white-400 text-secondary-dark ms-2">+ 80%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


@endsection



@section('script')

    <!-- modal -->

    <div class="modal" data-bs-backdrop="static" id="welcomeCard" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content welcome-card">

                <div class="modal-body p-0">
                    <div class="text-center position-relative welcome-card-content z-1 p-3">
                        <div class="text-end position-relative z-1">
                            <i class="ti ti-x fs-5 text-dark f-w-600" data-bs-dismiss="modal"></i>
                        </div>
                        <h2 class="f-w-700 text-primary-dark mb-0"><span>Welcome!</span> <img alt="gif"
                                                                                              class="w-45 d-inline align-baseline"
                                                                                              src="{{ asset('../assets/images/dashboard/ecommerce-dashboard/celebration.gif') }}">
                        </h2>

                        <div class="modal-img-box">
                            <img alt="img" class=" img-fluid" src="{{ asset('../assets/images/modals/welcome-1.png') }}">
                        </div>
                        <div class="modal-btn mb-4">
                            <button class="btn btn-primary text-white btn-sm rounded" data-bs-dismiss="modal" type="button">
                                Get Started
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>

    <!-- apexcharts js-->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

    <!-- filepond -->
    <script src="{{ asset('assets/vendor/filepond/file-encode.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/validate-size.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/validate-type.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/exif-orientation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/filepond.min.js') }}"></script>

    <!-- Project Dashboard js-->
    <script src="{{ asset('assets/js/project_dashboard.js') }}"></script>

@endsection
