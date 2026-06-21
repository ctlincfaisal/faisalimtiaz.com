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
            I am available for freelance work and project of any scale. Connect with me via
        </p>
        <div>
            Phone: +92-3006770770
            <br>
            Email: contact@faisalimtiaz.com
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
        
        <div itemscope itemtype='http://schema.org/Person' class='fiverr-seller-widget' style='display: inline-block;'>
             <a itemprop='url' href=https://www.fiverr.com/faysal1994 rel="nofollow" target="_blank" style='display: inline-block;'>
                <div class='fiverr-seller-content' id='fiverr-seller-widget-content-5a6bd7a8-211d-4f34-bf98-771e8ae17efe' itemprop='contentURL' style='display: none;'></div>
                <div id='fiverr-widget-seller-data' style='display: none;'>
                    <div itemprop='name' >faysal1994</div>
                    <div itemscope itemtype='http://schema.org/Organization'><span itemprop='name'>Fiverr</span></div>
                    <div itemprop='jobtitle'>Seller</div>
                    <div itemprop='description'>I am a highly skilled and experienced PHP developer with expertise in MySQL, Js, HTML, CSS, Wordpress, Laravel, Mobile app development, React Native, Ionic, Angular with Firebase. With over 10 years of experience in the field, I have worked with a wide range of clients from various industries and provided them with quality solutions that exceeded their expectations. If you are looking for a reliable and efficient developer to handle your project, then look no further! Whether you need a simple website, a complex web application, or a mobile app, I have the ability to bring your vision to life.</div>
                </div>
            </a>
        </div>
        
        <script id='fiverr-seller-widget-script-5a6bd7a8-211d-4f34-bf98-771e8ae17efe' src='https://widgets.fiverr.com/api/v1/seller/faysal1994?widget_id=5a6bd7a8-211d-4f34-bf98-771e8ae17efe' data-config='{"category_name":"Programming \u0026 Tech"}' async='true' defer='true'></script>
        

    </div>

    <span class="divider-center mt-6 mb-6">OR</span>

    <form id="contact_form">
        @csrf
        <div class="row">
            <div class="col-sm-6">
                <!-- Form -->
                <div class="mb-4">
                    <label class="form-label" for="hireUsFormFirstName">First name</label>
                    <input type="text" class="form-control form-control-lg" name="hireUsFormNameFirstName"
                        id="firstname" placeholder="First name" aria-label="First name">
                </div>
                <!-- End Form -->
            </div>
            <!-- End Col -->

            <div class="col-sm-6">
                <!-- Form -->
                <div class="mb-4">
                    <label class="form-label" for="hireUsFormLasttName">Last name</label>
                    <input type="text" class="form-control form-control-lg" name="hireUsFormNameLastName" id="lastname"
                        placeholder="Last name" aria-label="Last name">
                </div>
                <!-- End Form -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->

        <!-- Form -->
        <div class="mb-4">
            <label class="form-label" for="hireUsFormWorkEmail">Email address</label>
            <input type="email" class="form-control form-control-lg" name="hireUsFormNameWorkEmail" id="email"
                placeholder="email@site.com" aria-label="email@site.com">
        </div>
        <!-- End Form -->

        <!-- Select -->
        <div class="mb-4">
            <label class="form-label" for="hireUsFormBudget">Budget</label>
            <select id="budget" class="form-select form-select-lg" name="budget" aria-label="Tell us about your budget">
                <option selected value="">Tell us about your budget</option>
                <option value="1">less then $500</option>
                <option value="2">$500 - $1,000</option>
                <option value="3">$1,000 - $5,000</option>
                <option value="4">$5,000 or greater</option>
            </select>
        </div>
        <!-- End Select -->

        <!-- Form -->
        <div class="mb-4">
            <label class="form-label" for="hireUsFormDetails">Details</label>
            <textarea class="form-control form-control-lg" name="hireUsFormNameDetails" id="details"
                placeholder="Tell us about your project" aria-label="Tell us about your project" rows="4"></textarea>
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
                Send message
            </button>
            <div class="alert alert-success text-center smsg" style="display: none;">
                You have successfully contacted to Faisal Imtiaz.
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
    var lastname = $('#lastname').val();
    var budget = $('#budget').val();
    var email = $('#email').val();
    var details = $('#details').val();

    // console.log(firstname+lastname+budget+email+details);

    if (firstname.trim() == '' || lastname.trim() == '' || budget.trim() == '' || email.trim() == '' || details
        .trim() == '') {
        if (firstname.trim() == '') {
            $('#firstname').addClass('is-invalid');
        } else {
            $('#firstname').removeClass('is-invalid');
        }
        if (lastname.trim() == '') {
            $('#lastname').addClass('is-invalid');
        } else {
            $('#lastname').removeClass('is-invalid');
        }
        if (budget.trim() == '') {
            $('#budget').addClass('is-invalid');
        } else {
            $('#budget').removeClass('is-invalid');
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

        console.log('everything is filled');

        $('.spinner').show(200);
        $('button[type="submit"]').attr('disabled', true);
        $.ajax({
            url: "{{ url('contactus') }}",
            type: 'POST',
            data: {
                firstname: firstname,
                lastname: lastname,
                budget: budget,
                email: email,
                details: details,
                _token: $('input[name="_token"]').val(),
            },
            success: function(response) {
                console.log(response);
                if (response.msg == 'success') {

                    // setTimeout(() => {
                    document.getElementById('contact_form').reset();
                    $('button[type="submit"]').fadeOut(700, function() {
                        $('.smsg').fadeIn(1000);
                        $('#firstname').removeClass('is-invalid');
                        $('#lastname').removeClass('is-invalid');
                        $('#email').removeClass('is-invalid');
                        $('#budget').removeClass('is-invalid');
                        $('#details').removeClass('is-invalid');
                    });
                    // }, 10000);

                }
            },
            error: function(err) {
                var error = err.responseJSON;

                console.log(error.errors);
                $('.spinner').hide(100);
                $('button[type="submit"]').attr('disabled', false);

                if (error.errors['firstname'] && error.errors['firstname'][0] == 1) {
                    $('#firstname').addClass('is-invalid');
                } else {
                    $('#firstname').removeClass('is-invalid');
                }

                if (error.errors['lastname'] && error.errors['lastname'][0] == 2) {
                    $('#lastname').addClass('is-invalid');
                } else {
                    $('#lastname').removeClass('is-invalid');
                }

                if (error.errors['budget'] && error.errors['budget'][0] == 4) {
                    $('#budget').addClass('is-invalid');
                } else {
                    $('#budget').removeClass('is-invalid');
                }

                if (error.errors['email'] && error.errors['email'][0] == 3) {
                    $('#email').addClass('is-invalid');
                } else {
                    $('#email').removeClass('is-invalid');
                }

                if (error.errors['details'] && error.errors['details'][0] == 5) {
                    $('#details').addClass('is-invalid');
                } else {
                    $('#details').removeClass('is-invalid');
                }
            }
        });
    }


})
</script>