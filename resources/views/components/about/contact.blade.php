<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>


<!-- Header -->
<div class="card-header bg-img-start" style="background-image: url(../assets/svg/components/card-1.svg);">
    <div class="flex-grow-1">
        <!-- <span class="d-lg-none">Step 2 of 5</span> -->
        <h3 class="card-header-title">Contact me</h3>
    </div>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <!-- Content -->

    <div class="row">
        <div class="mb-3">
            <!-- <img class="avatar avatar-lg avatar-4x3" src="{{ url('assets/logo.png') }}" alt="Logo">-->
            <h2>Faisa<span style="color: #8241B6;">l Imtiaz</span></h2>
        </div>
        <p class="card-text">
            I’m available for websites, mobile apps, SEO-led builds, and project support.
        </p>
        <div>
            <a href="tel:+923006770770">Phone: +92-3006770770</a>
            <br>
            <a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20about%20page">Email: ctlinc.faisal@gmail.com</a>
        </div>
        <div class="bg-light rounded-2 p-3 mt-3">
            <h3 class="h6 mb-2">Work style</h3>
            <p class="mb-0">
                Clear scope, regular updates, and a handoff that includes the details needed for ongoing support.
            </p>
        </div>

        <ul class="col-lg-12 list-inline mb-0 mt-3">

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://www.youtube.com/@iamfaisalimtiaz"
                    target="_blank">
                    <i class="bi-youtube"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://www.linkedin.com/in/faysalimtiaz/"
                    target="_blank">
                    <i class="bi bi-linkedin"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://www.instagram.com/iamfaysalimtiaz/"
                    target="_blank">
                    <i class="bi bi-instagram"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://www.facebook.com/iamfaisalimtiaz/"
                    target="_blank">
                    <i class="bi-facebook"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://www.behance.net/ficreations" target="_blank">
                    <i class="bi bi-behance"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a class="btn btn-soft-dark btn-xl btn-icon" href="https://github.com/ctlincfaisal" target="_blank">
                    <i class="bi-github"></i>
                </a>
            </li>
        </ul>
        

    </div>

    <span class="divider-center mt-6 mb-6">OR</span>

                            <form id="contact_form" method="POST" action="{{ url('contactus') }}">
        @csrf
        <div class="row">
            <div class="col-12">
                <!-- Form -->
                <div class="mb-4">
                    <label class="form-label" for="hireUsFormFirstName">Name</label>
                    <input type="text" class="form-control form-control-lg" name="hireUsFormNameFirstName"
                        id="firstname" placeholder="Your name" aria-label="Your name" autocomplete="name" required>
                </div>
                <!-- End Form -->
            </div>
        </div>
        <!-- End Row -->

        <!-- Form -->
        <div class="mb-4">
            <label class="form-label" for="hireUsFormWorkEmail">Email address</label>
            <input type="email" class="form-control form-control-lg" name="hireUsFormNameWorkEmail" id="email"
                placeholder="email@site.com" aria-label="email@site.com" autocomplete="email" required>
        </div>
        <!-- End Form -->

        <!-- Select -->
        <div class="mb-4">
            <label class="form-label" for="hireUsFormBudget">Project type <span class="text-muted">(optional)</span></label>
            <select id="budget" class="form-select form-select-lg" name="budget" aria-label="Choose your project type">
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
            <textarea class="form-control form-control-lg" name="hireUsFormNameDetails" id="details"
                placeholder="Tell me what you need, your timeline, and the best way to help" aria-label="Tell me what you need, your timeline, and the best way to help" rows="4" required></textarea>
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
                <div class="spinner-grow spinner-grow-sm text-light spinner" style="display: none;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Send project details
            </button>
            <div class="alert alert-success text-center smsg" style="display: none;">
                Thanks. I’ve received your message and will reply soon.
            </div>
        </div>

        <div class="text-center">
            <span class="form-text">We'll get back to you in 3-4 business hours.</span>
        </div>
    </form>
    <!-- End Form -->




</div>

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
