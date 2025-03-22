@extends('layout.master')
@section('title', 'Form Validation')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid">

        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Form Validation</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                      <span>
                        <i class="ph-duotone  ph-cardholder f-s-16"></i>  Forms elements
                      </span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Form Validation</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Form Validation start -->
        <div class="row ">
            <!-- Tooltips start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-column gap-2">
                        <h5>Tooltips</h5>
                        <p class="text-secondary">If your form layout allows it, you can swap the SP <span
                                class="text-danger"> .{valid|invalid}-feedback</span> classes for
                            <span class="text-danger"> .{valid|invalid}-tooltip</span> classes to display
                            validation feedback
                            in a styled tooltip. Be sure to
                            have a parent with <span class="text-danger">position: relative </span>on it for
                            tooltip
                            positioning. In the example below, our
                            column classes have this already, but your project may require an alternative
                            setup.
                        </p>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 app-form rounded-control" id="form-validation">
                            <div class="col-md-6">
                                <label class="form-label" for="userName">User Name</label>
                                <input class="form-control" id="userName" name="userName" type="text">
                                <div class="mt-1">
                                    <span class="text-danger" id="userNameError"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" type="email">
                                <div class="mt-1">
                                    <span class="text-danger" id="emailError"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" id="password" type="password">
                                <div class="mt-1">
                                    <span class="text-danger" id="passwordError"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="address">Address</label>
                                <input class="form-control" id="address" placeholder="1234 Main St"
                                       type="text">
                                <div class="mt-1">
                                    <span class="text-danger" id="addressError"></span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="address2">Address 2</label>
                                <input class="form-control" id="address2" placeholder="Address" type="text">
                                <div class="mt-1">
                                    <span class="text-danger" id="addressError2"></span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="city">City</label>
                                <input class="form-control" id="city" type="text">
                                <div class="mt-1">
                                    <span class="text-danger" id="cityError"></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="zipCode">Zip</label>
                                <input class="form-control" id="zipCode" type="text">
                                <div class="mt-1">
                                    <span class="text-danger" id="zipCodeError"></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check d-flex gap-1">
                                    <input class="form-check-input mg-2" id="gridCheck" type="checkbox">
                                    <label class="form-check-label" for="gridCheck">
                                        Check me out
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary b-r-22" type="submit" value="Submit">Submit
                                    form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Tooltips end -->
            <!-- Custom Styles start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-column gap-2">
                        <h5>Custom Styles</h5>
                        <div>
                            <p class="text-secondary">For custom Bootstrap form validation messages, you’ll
                                need to add the
                                <span class="text-danger">
                          novalidate </span>boolean attribute to your <span class="text-danger">

                        </span> This disables the browser default feedback tooltips, but still provides access to the
                                form
                                validation APIs in JavaScript. Try to submit the form below; our JavaScript
                                will intercept the
                                submit button and relay feedback to you.
                            </p>
                            <p class="text-secondary">When attempting to submit, you’ll see the <span
                                    class="text-danger">
                          :invalid and :valid
                        </span>styles applied to your form controls.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form class="app-form rounded-control row g-3 needs-validation" novalidate>
                            <div class="col-md-4">
                                <label class="form-label" for="validationCustom01">First name</label>
                                <input class="form-control" id="validationCustom01" required type="text"
                                       value="Mark">
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="validationCustom02">Last name</label>
                                <input class="form-control" id="validationCustom02" required type="text"
                                       value="Otto">
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="validationCustomUsername">Username</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                                    <input aria-describedby="inputGroupPrepend" class="form-control"
                                           id="validationCustomUsername"
                                           required type="text">
                                    <div class="invalid-feedback">
                                        Please choose a username.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="validationCustom03">City</label>
                                <input class="form-control" id="validationCustom03" required type="text">
                                <div class="invalid-feedback">
                                    Please provide a valid city.
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="validationCustom04">State</label>
                                <select class="form-select" id="validationCustom04" required>
                                    <option disabled selected value="">Choose...</option>
                                    <option>...</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a valid state.
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="validationCustom05">Zip</label>
                                <input class="form-control" id="validationCustom05" required type="text">
                                <div class="invalid-feedback">
                                    Please provide a valid zip.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check d-flex flex-wrap gap-1">
                                    <input class="form-check-input mg-2" id="invalidCheck" required
                                           type="checkbox" value="">
                                    <label class="form-check-label" for="invalidCheck">
                                        Agree to terms and conditions
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree before submitting.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary b-r-22" type="submit">Submit form</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Custom Styles end -->
            <!-- Browser Defaults -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-column gap-2">
                        <h5>Browser Defaults</h5>
                        <p class="text-secondary">Not interested in custom validation feedback messages or
                            writing
                            JavaScript to change form
                            behaviors? All good, you can use the browser defaults. Try submitting the form
                            below. Depending on
                            your browser and OS, you’ll see a slightly different style of feedback.
                            While these feedback styles cannot be styled with CSS, you can still customize
                            the feedback text
                            through JavaScript.</p>
                    </div>
                    <div class="card-body">
                        <form class="app-form rounded-control  row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="validationDefault01">First name</label>
                                <input class="form-control" id="validationDefault01" required type="text"
                                       value="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="validationDefault02">Last name</label>
                                <input class="form-control" id="validationDefault02" required type="text"
                                       value="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="validationDefaultUsername">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="inputGroupPrepend2">@</span>
                                    <input aria-describedby="inputGroupPrepend2" class="form-control"
                                           id="validationDefaultUsername"
                                           required type="text">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="validationDefault03">City</label>
                                <input class="form-control" id="validationDefault03" required type="text">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="validationDefault04">State</label>
                                <select class="form-select" id="validationDefault04" required>
                                    <option disabled selected value="">Choose...</option>
                                    <option>...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="validationDefault05">Zip</label>
                                <input class="form-control" id="validationDefault05" required type="text">
                            </div>
                            <div class="col-12">
                                <div class="form-check d-flex gap-1">
                                    <input class="form-check-input mg-2" id="invalidCheck2" required
                                           type="checkbox" value="">
                                    <label class="form-check-label" for="invalidCheck2">
                                        Agree to terms and conditions
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary b-r-22" type="submit">Submit form</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Browser Defaults -->
            <!-- Supported Elements end -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-column gap-2">
                        <h5>Supported Elements</h5>
                        <p class="text-secondary">Not interested in custom validation feedback messages or
                            writing
                            JavaScript to change form
                            behaviors? All good, you can use the browser defaults. Try submitting the form
                            below. Depending on
                            your browser and OS, you’ll see a slightly different style of feedback.
                            While these feedback styles cannot be styled with CSS, you can still customize
                            the feedback text
                            through JavaScript.</p>
                    </div>
                    <div class="card-body">
                        <form class="app-form rounded-control was-validated">
                            <div class="mb-3">
                                <label class="form-label" for="validationTextarea">Textarea</label>
                                <textarea class="form-control is-invalid" id="validationTextarea"
                                          placeholder="Required example textarea" required></textarea>
                                <div class="invalid-feedback">
                                    Please enter a message in the textarea.
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" id="validationFormCheck1" required
                                       type="checkbox">
                                <label class="form-check-label" for="validationFormCheck1">Check this
                                    checkbox</label>
                                <div class="invalid-feedback">Example invalid feedback text</div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" id="validationFormCheck3"
                                       name="radio-stacked"
                                       required
                                       type="radio">
                                <label class="form-check-label" for="validationFormCheck3">Or toggle this
                                    other radio</label>
                                <div class="invalid-feedback">More example invalid feedback text</div>
                            </div>

                            <div class="mb-3">
                                <select aria-label="select example" class="form-select" required>
                                    <option value="">Open this select menu</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <div class="invalid-feedback">Example invalid select feedback</div>
                            </div>

                            <div class="mb-3">
                                <input aria-label="file example" class="form-control" required type="file">
                                <div class="invalid-feedback">Example invalid form file feedback</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Supported Elements start -->
        </div>
        <!-- Form Validation end -->

    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!--js-->
<script src="{{ asset('assets/js/formvalidation.js') }}"></script>
@endsection
