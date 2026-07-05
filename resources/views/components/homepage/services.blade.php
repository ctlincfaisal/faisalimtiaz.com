<!-- Card Grid -->
<div class="container content-space-2 content-space-t-xl-3 content-space-b-lg-3" id="whatido">
  <!-- Heading -->
  <div class="w-md-75 w-lg-50 text-center mx-md-auto mb-5">
    <h2>How I help</h2>
  </div>
  <!-- End Heading -->

  <div class="text-center mb-4">
    <!-- List Checked -->
    <ul class="list-inline list-checked list-checked-primary">
      <li class="list-inline-item list-checked-item">Clear project updates</li>
      <li class="list-inline-item list-checked-item">Launch and deployment support</li>
      <li class="list-inline-item list-checked-item">Ongoing maintenance and support</li>
    </ul>
    <!-- End List Checked -->
  </div>

  <div class="row mb-5 mb-md-0">
    <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
          <!-- Card -->
          <div class="card card-sm h-100">
            <div class="p-2">
              <img class="card-img" src="{{ url('assets/faisalimtiaz/app-development.png') }}" alt="Illustration for mobile app development services">
            </div>

        <div class="card-body">
          <h4 class="card-title" style="color: #8241B6!important;">Mobile app development</h4>
          <p class="card-text">I build Android and iOS apps that are designed around your product goals and user needs.</p>

          <!-- List Pointer -->
          <ul class="list-pointer mb-0">
            <li class="list-pointer-item">App UI and UX design</li>
            <li class="list-pointer-item">Frontend development</li>
            <li class="list-pointer-item">Backend integration</li>
            <li class="list-pointer-item">App Store and Play Store launch</li>
            <li class="list-pointer-item">Maintenance and bug fixes for 1 month</li>
          </ul>
          <!-- End List Pointer -->
        </div>

          <a class="card-footer card-link border-top" href="{{ route('services.mobile-app-development') }}">Learn more <i class="bi-chevron-right small ms-1"></i></a>
      </div>
      <!-- End Card -->
    </div>
    <!-- End Col -->

    <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
      <!-- Card -->
      <div class="card card-sm h-100">
        <div class="p-2">
          <img class="card-img" src="{{ url('assets/faisalimtiaz/website-development.png') }}" alt="Illustration for website development services">
        </div>

        <div class="card-body">
          <h4 class="card-title" style="color: #8241B6!important;">Website development</h4>
          <p class="card-text">I create fast, responsive websites and web apps that help you launch and grow online.</p>

          <!-- List Pointer -->
          <ul class="list-pointer mb-0">
            <li class="list-pointer-item">Website UI and UX design</li>
            <li class="list-pointer-item">Frontend development</li>
            <li class="list-pointer-item">Backend development</li>
            <li class="list-pointer-item">Server deployment</li>
            <li class="list-pointer-item">Maintenance and bug fixes for 1 month</li>
          </ul>
          <!-- End List Pointer -->
        </div>

        <a class="card-footer card-link border-top" href="{{ route('services.website-development') }}">Learn more <i class="bi-chevron-right small ms-1"></i></a>
      </div>
      <!-- End Card -->
    </div>
    <!-- End Col -->

    <div class="col-sm-6 col-lg-4">
      <!-- Card -->
      <div class="card card-sm h-100">
        <div class="p-2">
          <img class="card-img" src="{{ url('assets/faisalimtiaz/seo.png') }}" alt="Illustration for SEO services">
        </div>

        <div class="card-body">
          <h4 class="card-title" style="color: #8241B6!important;">SEO services</h4>
          <p class="card-text">I help your pages become easier to find, easier to understand, and easier to trust in search.</p>

          <!-- List Pointer -->
          <ul class="list-pointer mb-0">
            <li class="list-pointer-item">Speed improvements</li>
            <li class="list-pointer-item">Technical SEO cleanup</li>
            <li class="list-pointer-item">On-page optimization</li>
            <li class="list-pointer-item">Search-friendly structure</li>
            <li class="list-pointer-item">Post-launch tuning</li>
          </ul>
          <!-- End List Pointer -->
        </div>

        <a class="card-footer card-link border-top" href="{{ route('services.seo-services') }}">Learn more <i class="bi-chevron-right small ms-1"></i></a>
      </div>
      <!-- End Card -->
    </div>
    <!-- End Col -->
  </div>
  <!-- End Row -->

  <div class="text-center mt-5">
    <p class="mb-3">Need a React Native or Laravel-specific build? Explore the dedicated pages.</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a class="btn btn-outline-primary btn-sm" href="{{ route('services.react-native-development') }}">React Native development</a>
      <a class="btn btn-outline-primary btn-sm" href="{{ route('services.laravel-development') }}">Laravel development</a>
    </div>
  </div>
</div>
<!-- End Card Grid -->
