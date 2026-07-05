<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<!-- Contacts -->
<div class="position-relative" id="contact">
    <div class="bg-primary bg-img-start" style="background-image: url(./assets/svg/components/shape-7.svg);">
        <div class="container content-space-t-2 content-space-t-lg-3 content-space-b-1">
            <!-- Heading -->
            <div class="w-lg-50 text-center mx-lg-auto mb-7">
                <span class="text-cap text-white-70">Contact me</span>
                <h2 class="text-white lh-base">Need a website or app that turns visitors into leads? <span
                        class="text-warning">Let’s talk.</span></h2>
                <p class="text-white-70 mt-3 mb-0">Send a quick message, email me directly, or call if you want a faster response.</p>
            </div>
            <!-- End Heading -->

            <div class="row g-3 justify-content-center mb-6">
                <div class="col-sm-4">
                    <div class="bg-white bg-opacity-10 text-white rounded-2 p-3 text-center h-100">
                        <div class="fw-semibold">10+ years of experience</div>
                        <small class="text-white-70">Working with clients since 2013.</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="bg-white bg-opacity-10 text-white rounded-2 p-3 text-center h-100">
                        <div class="fw-semibold">Replies in 3-4 business hours</div>
                        <small class="text-white-70">Clear next steps, no back and forth.</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="bg-white bg-opacity-10 text-white rounded-2 p-3 text-center h-100">
                        <div class="fw-semibold">Direct contact options</div>
                        <small class="text-white-70">Phone, email, or the form below.</small>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-4">
                    <div class="card h-100">
                        <img class="card-img-top" src="{{ url('assets/faisalimtiaz/faisalimtiaz.jpg') }}"
                            alt="Portrait of Faisal Imtiaz">

                        <div class="card-body">
                            <div class="mb-3">
                                <!-- <img class="avatar avatar-lg avatar-4x3" src="{{ url('assets/logo.png') }}" alt="Logo">-->
                                <h2>Faisa<span style="color: #8241B6;">l Imtiaz</span></h2>
                            </div>
                            <p class="card-text">
                                I’m available for websites, mobile apps, SEO-led builds, and project support.
                            </p>
                            <div class="mb-3">
                                <div class="mb-2">Connect with me:</div>
                                <div class="d-grid gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="tel:+923006770770">
                                        <i class="bi bi-telephone me-1"></i> Call +92 300 6770770
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm" href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20homepage">
                                        <i class="bi bi-envelope me-1"></i> Email Faisal
                                    </a>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-lg-6">
                                    <a href="https://fiverr.com/faysal1994" target="_blank">
                                        <img class="card-img-top" src="{{ url('assets/faisalimtiaz/fiverr.svg') }}"
                                            alt="Fiverr profile badge" style="width: 30px;">
                                    </a>
                                    <!-- <span>
                                            <a href="">Profile on FIVERR</a>
                                        </span> -->
                                    <a href="https://www.upwork.com/freelancers/~01f4d63b18385cb19b?viewMode=1"
                                        target="_blank">
                                        <img class="card-img-top" src="{{ url('assets/faisalimtiaz/upwork.svg') }}"
                                            alt="Upwork profile badge" style="width: 30px;">
                                    </a>
                                    <!-- <span>
                                        <a href="">Profile on UPWORK</a>
                                        </span> -->
                                </div>
                            </div>
                            



                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- style="max-width: 35rem;" -->
                    <!-- Card -->
                    <div class="card zi-2">
                        <div class="card-body">
                            <!-- Form -->
                            <form id="contact_form" method="POST" action="{{ url('contactus') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <!-- Form -->
                                        <div class="mb-4">
                                            <label class="form-label" for="hireUsFormFirstName">Name</label>
                                            <input type="text" class="form-control form-control-lg"
                                                name="hireUsFormNameFirstName" id="firstname" placeholder="Your name"
                                                aria-label="Your name" autocomplete="name" required>
                                        </div>
                                        <!-- End Form -->
                                    </div>
                                </div>
                                <!-- End Row -->

                                <!-- Form -->
                                <div class="mb-4">
                                    <label class="form-label" for="hireUsFormWorkEmail">Email address</label>
                                    <input type="email" class="form-control form-control-lg"
                                        name="hireUsFormNameWorkEmail" id="email" placeholder="email@site.com"
                                        aria-label="email@site.com" autocomplete="email" required>
                                </div>
                                <!-- End Form -->

                                <!-- Select -->
                                <div class="mb-4">
                                    <label class="form-label" for="hireUsFormBudget">Project type <span class="text-muted">(optional)</span></label>
                                    <select id="budget" class="form-select form-select-lg" name="budget"
                                        aria-label="Choose your project type">
                                        <option selected value="">Choose a project type</option>
                                        <option value="Website development">Website development</option>
                                        <option value="Mobile app development">Mobile app development</option>
                                        <option value="SEO services">SEO services</option>
                                        <option value="Not sure yet">Not sure yet</option>
                                    </select>
                                </div>
                                <!-- End Select -->

                                <!-- Form -->
                                <div class="mb-4">
                                    <label class="form-label" for="hireUsFormDetails">Project details</label>
                                    <textarea class="form-control form-control-lg" name="hireUsFormNameDetails"
                                        id="details" placeholder="Tell me what you need, your timeline, and the best way to help"
                                        aria-label="Tell me what you need, your timeline, and the best way to help" rows="4" required></textarea>
                                </div>
                                <!-- End Form -->

                                <!-- Check -->
                                <!-- <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="signupFormPrivacyCheck"
                                    name="signupFormPrivacyCheck" required>
                                <label class="form-check-label small" for="signupFormPrivacyCheck"> By submitting this
                                    form I have read and acknowledged the <a href=./page-privacy.html>Privacy
                                        Policy</a></label>
                            </div> -->
                                <!-- End Check -->

                                <div class="d-grid mb-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <div class="spinner-grow spinner-grow-sm text-light spinner"
                                            style="display: none;" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Send project details
                                    </button>
                                    <div class="alert alert-success text-center smsg" style="display: none;">
                                        Thanks. I’ve received your message and will reply soon.
                                    </div>
                                </div>

                                <div class="text-center">
                                    <span class="form-text">I’ll get back to you in 3-4 business hours.</span>
                                </div>
                            </form>
                            <!-- End Form -->
                        </div>
                    </div>
                    <!-- End Card -->
                </div>

            </div>


        </div>
    </div>
</div>
<!-- End Contacts -->

<script>
$('#contact_form').on('submit', function(e) {
    e.preventDefault();

    var firstname = $('#firstname').val();
    var budget = $('#budget').val();
    var email = $('#email').val();
    var details = $('#details').val();

    if (firstname.trim() == '' || email.trim() == '' || details.trim() == '') {
        if (firstname.trim() == '') {
            $('#firstname').addClass('is-invalid');
        } else {
            $('#firstname').removeClass('is-invalid');
        }
        if (email.trim() == '') {
            $('#email').addClass('is-invalid');
        } else {
            $('#email').removeClass('is-invalid');
        }
        if (details.trim() == '') {
            $('#details').addClass('is-invalid');
        } else {
            $('#details').removeClass('is-invalid');
        }
    } else {
        $('#details').removeClass('is-invalid');

        $('.spinner').show(200);
        $('button[type="submit"]').attr('disabled', true);
        $.ajax({
            url: "{{ url('contactus') }}",
            type: 'POST',
            data: {
                firstname: firstname,
                budget: budget,
                email: email,
                details: details,
                _token: $('input[name="_token"]').val(),
            },
            success: function(response) {
                console.log(response);
                if (response.msg == 'success') {
                    $('.spinner').hide(100);

                    // setTimeout(() => {
                    document.getElementById('contact_form').reset();
                    $('button[type="submit"]').fadeOut(700, function() {
                        $('.smsg').fadeIn(1000);
                        $('#firstname').removeClass('is-invalid');
                        $('#email').removeClass('is-invalid');
                        $('#details').removeClass('is-invalid');
                    });
                    // }, 10000);

                }
            },
            error: function(err) {
                var error = err.responseJSON || {};

                console.log(error.errors || error.message || err);
                $('.spinner').hide(100);
                $('button[type="submit"]').attr('disabled', false);

                if (error.errors && error.errors['firstname']) {
                    $('#firstname').addClass('is-invalid');
                } else {
                    $('#firstname').removeClass('is-invalid');
                }

                if (error.errors && error.errors['email']) {
                    $('#email').addClass('is-invalid');
                } else {
                    $('#email').removeClass('is-invalid');
                }

                if (error.errors && error.errors['details']) {
                    $('#details').addClass('is-invalid');
                } else {
                    $('#details').removeClass('is-invalid');
                }
            }
        });
    }


})
</script>
