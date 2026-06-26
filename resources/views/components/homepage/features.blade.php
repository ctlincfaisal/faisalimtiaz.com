<style>
    .features-to-get li{
        cursor: pointer;
        transition: 500ms all;
    }
    .features-to-get li:hover{
        color: #377dff;
        /* font-weight: bold; */
    }

    .feature-phone {
        background: linear-gradient(135deg, #f8fafc 0%, #cbd5e1 34%, #f9fafb 52%, #94a3b8 100%);
        border: 2px solid #e5e7eb;
        border-radius: 42px;
        box-shadow: 0 28px 70px rgba(15, 23, 42, .22), inset 0 0 0 2px rgba(255, 255, 255, .8), inset 0 0 0 7px rgba(148, 163, 184, .38);
        margin-right: 24px;
        overflow: hidden;
        padding: 14px;
        position: relative;
        transform-style: preserve-3d;
        transform-origin: center center;
        width: 285px;
    }

    .feature-phone::before {
        background: linear-gradient(180deg, #f8fafc 0%, #cbd5e1 100%);
        border: 1px solid rgba(148, 163, 184, .55);
        border-top: 0;
        border-radius: 0 0 16px 16px;
        content: "";
        height: 24px;
        left: 50%;
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        width: 96px;
        z-index: 4;
    }

    .feature-phone::after {
        background: rgba(15, 23, 42, .32);
        border-radius: 999px;
        content: "";
        height: 4px;
        left: 50%;
        position: absolute;
        top: 10px;
        transform: translateX(-50%);
        width: 38px;
        z-index: 5;
    }

    .feature-phone-screen {
        aspect-ratio: 9 / 19.5;
        background: #020617;
        border: 1px solid rgba(15, 23, 42, .18);
        border-radius: 30px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
        overflow: hidden;
        position: relative;
    }

    .feature-phone-screen img {
        display: block;
        height: 100%;
        object-fit: contain;
        width: 100%;
    }

    .feature-image-loader {
        align-items: center;
        background: rgba(255, 255, 255, .76);
        bottom: 0;
        display: none;
        justify-content: center;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        z-index: 2;
    }

    .feature-image-loader.active {
        display: flex;
    }

    .feature-image-spinner {
        animation: featureSpinner 800ms linear infinite;
        border: 3px solid rgba(55, 125, 255, .2);
        border-radius: 999px;
        border-top-color: #377dff;
        height: 42px;
        width: 42px;
    }

    .feature-phone.is-straight {
        transform: rotateY(0deg) rotateX(0deg) !important;
    }

    .feature-phone.is-spinning {
        animation: featurePhoneSpin 650ms ease-in-out forwards;
    }

    @keyframes featureSpinner {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes featurePhoneSpin {
        0% {
            transform: rotateY(0deg) rotateX(0deg);
        }
        45% {
            transform: rotateY(-28deg) rotateX(4deg) scale(.98);
        }
        100% {
            transform: rotateY(0deg) rotateX(0deg);
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

<div class="position-relative bg-light rounded-2 mx-3 mx-lg-10" id="whatyouget">
    <div class="container content-space-2 content-space-lg-3">
        <!-- Heading -->
        <div class="w-md-75 w-lg-50 text-center mx-md-auto mb-5">
            <h2>What you get</h2>
            <p>Following are the types mobile applications i can develop</p>
        </div>
        <!-- End Heading -->

        <div class="text-center mb-7">
            <!-- List Checked -->
            <ul class="list-inline list-checked list-checked-primary">
                <li class="list-inline-item list-checked-item">Performance</li>
                <li class="list-inline-item list-checked-item">Usability</li>
                <li class="list-inline-item list-checked-item">Compatibility</li>
                <li class="list-inline-item list-checked-item">Accessibility</li>
                <li class="list-inline-item list-checked-item">Scalability</li>
                <li class="list-inline-item list-checked-item">Security</li>
            </ul>
            <!-- End List Checked -->
        </div>

        <div class="row">
            <div class="col-lg-7">

                <div class="row">
                    <div class="col-lg-6">
                        <!-- List Checked -->
                        <ul class="list-checked list-checked-primary mb-5 features-to-get">
                            <li class="list-checked-item" data="1">User Authentication</li>
                            <li class="list-checked-item" data="2">Login with social networks</li>
                            <li class="list-checked-item" data="3">Twilio API for OTP</li>
                            <li class="list-checked-item" data="4">Real time chat system</li>
                            <li class="list-checked-item" data="5">WebRTC to make video/audio calls</li>
                            <li class="list-checked-item" data="6">Live Streaming</li>
                            <li class="list-checked-item" data="7">Payment Gateways</li>
                            <li class="list-checked-item" data="8">Maps and live tracking</li>
                            <li class="list-checked-item" data="9">Picture/Video Capturing</li>
                            <li class="list-checked-item" data="10">QR scanning and generation</li>
                            <li class="list-checked-item" data="11">Educational Apps</li>
                            <!-- <li class="list-checked-item" data="13">LMS</li>
                            <li class="list-checked-item" data="14">E-Commerce App</li>
                            <li class="list-checked-item" data="15">AR Technology</li> -->
                        </ul>
                        <!-- End List Checked -->
                    </div>
                    <div class="col-lg-6">
                        <!-- List Checked -->
                        <ul class="list-checked list-checked-primary mb-5 features-to-get">
                            <li class="list-checked-item" data="16">Profile Creation</li>
                            <li class="list-checked-item" data="17">Music Players</li>
                            <li class="list-checked-item" data="18">Push Notifications</li>
                            <li class="list-checked-item" data="19">Analytics</li>
                            <li class="list-checked-item" data="20">Voice Search</li>
                            <li class="list-checked-item" data="21">Youtube Data API</li>
                            <li class="list-checked-item" data="22">PUSHER API</li>
                            <li class="list-checked-item" data="23">Group chatting</li>
                            <li class="list-checked-item" data="24">Advance Camera filters and effects</li>
                            <li class="list-checked-item" data="25">Social Media Applications</li>
                            <li class="list-checked-item" data="26">Dating Apps</li>
                            <!-- <li class="list-checked-item" data="28">CMS</li>
                            <li class="list-checked-item" data="29">In app purchases</li>
                            <li class="list-checked-item" data="30">Artificial Intelligence related apps</li> -->
                        </ul>
                        <!-- End List Checked -->
                    </div>
                </div>

                <span>and many more...</span>

                
            </div>
            <!-- End Col -->


            <div class="col-lg-5 mb-9 mb-lg-0">
                <div class="pe-lg-6 d-flex justify-content-end">
                    <!-- Browser Device -->

                    <figure class="feature-phone">
                        <div class="feature-phone-screen">
                            <div class="feature-image-loader" aria-hidden="true">
                                <div class="feature-image-spinner"></div>
                            </div>
                            <img class="device-mobile-img device-preview" src="{{ url('assets/features/19.png') }}" alt="Image Description">
                        </div>
                    </figure>
                    <!-- End Mobile Device -->
                </div>
            </div>
            <!-- End Col -->


        </div>
        <!-- End Row -->
    </div>
</div>

<script>
    let featureImageRequest = 0;

    $('.features-to-get li').on('mouseenter', function(){
        
        var feature_selected = $(this).attr('data');
        var address = '';

        for( i=1; i<=30; i++ ){

            if( feature_selected == i ){
                var address = `{{ url('assets/features/${i}.png') }}`;
            }

        }

        // alert(address);
        const phone = $('.feature-phone');
        phone.removeClass('is-spinning is-straight');
        void phone[0].offsetWidth;
        phone.addClass('is-spinning');
        window.setTimeout(function () {
            phone.removeClass('is-spinning').addClass('is-straight');
        }, 650);

        if (!address || $('.device-preview').attr('src') === address) {
            return;
        }

        featureImageRequest++;
        const currentRequest = featureImageRequest;
        const previewImage = $('.device-preview');
        const loader = $('.feature-image-loader');
        const preloader = new Image();

        loader.addClass('active');

        preloader.onload = function () {
            if (currentRequest !== featureImageRequest) {
                return;
            }

            previewImage.attr('src', address);
            loader.removeClass('active');
        };

        preloader.onerror = function () {
            if (currentRequest === featureImageRequest) {
                loader.removeClass('active');
            }
        };

        preloader.src = address;

        // if( feature_selected==1 ){
        //     var address = "{{ url('assets/features/1.png') }}";
        // }
        // if( feature_selected==2 ){
        //     var address = "{{ url('assets/features/2.png') }}";
        // }
        // if( feature_selected==3 ){
        //     var address = "{{ url('assets/features/3.png') }}";
        // }
        // if( feature_selected==4 ){
        //     var address = "{{ url('assets/features/4.png') }}";
        // }
        // if( feature_selected==5 ){
        //     var address = "{{ url('assets/features/5.png') }}";
        // }
        // if( feature_selected==6 ){
        //     var address = "{{ url('assets/features/6.png') }}";
        // }
        // if( feature_selected==7 ){
        //     var address = "{{ url('assets/features/7.png') }}";
        // }
        // if( feature_selected==8 ){
        //     var address = "{{ url('assets/features/8.png') }}";
        // }
        // if( feature_selected==9 ){
        //     var address = "{{ url('assets/features/9.png') }}";
        // }
        // if( feature_selected==10 ){
        //     var address = "{{ url('assets/features/10.png') }}";
        // }

        // switch(feature_selected) {
        //     case 1:
        //         alert(1);
                
        //     break;
        // }

    });
</script>
