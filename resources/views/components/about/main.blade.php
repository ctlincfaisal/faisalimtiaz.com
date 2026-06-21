<!-- Content -->
<div class="container content-space-2">
  <!-- Step Form -->
  <div class="js-step-form"
        data-hs-step-form-options='{
          "progressSelector": "#uploadResumeStepFormProgress",
          "stepsSelector": "#uploadResumeStepFormContent",
          "endSelector": "#uploadResumeFinishBtn",
          "isValidate": false
        }'>
    <div class="row">
      <div class="col-lg-4">
        <!-- Sticky Block -->
        <div id="stickyBlockStartPoint">
          <div class="js-sticky-block"
               data-hs-sticky-block-options='{
                 "parentSelector": "#stickyBlockStartPoint",
                 "breakpoint": "lg",
                 "startPoint": "#stickyBlockStartPoint",
                 "endPoint": "#stickyBlockEndPoint",
                 "stickyOffsetTop": 20,
                 "stickyOffsetBottom": 0
               }'>
            <!-- Step -->
            <ul id="uploadResumeStepFormProgress" class="js-step-progress step step-icon-xs step-border-last-0 mt-5">
              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepBasics"
                  }'>
                  <span class="step-icon step-icon-soft-dark"></span>
                  <div class="step-content">
                    <span class="step-title">Personal Information</span>
                    <span class="step-title-description step-text">General info about me</span>
                  </div>
                </a>
              </li>

              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepEducation"
                  }'>
                  <span class="step-icon step-icon-soft-dark"></span>
                  <div class="step-content">
                    <span class="step-title">Education</span>
                    <span class="step-title-description step-text">Educational Background</span>
                  </div>
                </a>
              </li>

              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepWork"
                  }'>
                  <span class="step-icon step-icon-soft-dark"></span>
                  <div class="step-content">
                    <span class="step-title">Work experience</span>
                    <span class="step-title-description step-text">Professional work experience</span>
                  </div>
                </a>
              </li>

              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepJobSkills"
                  }'>
                  <span class="step-icon step-icon-soft-dark"></span>
                  <div class="step-content">
                    <span class="step-title">Skills</span>
                    <span class="step-title-description step-text">Professional technical skills</span>
                  </div>
                </a>
              </li>

              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepTypeOfJob"
                  }'>
                  <span class="step-icon step-icon-soft-dark"></span>
                  <div class="step-content">
                    <span class="step-title">Contact me</span>
                    <span class="step-title-description step-text">Looking to contact me ?</span>
                  </div>
                </a>
              </li>

              <!-- <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;"
                   data-hs-step-form-next-options='{
                    "targetSelector": "#uploadResumeStepMyCV"
                  }'>
                  <span class="step-icon step-icon-soft-dark">6</span>
                  <div class="step-content">
                    <span class="step-title">MY CV</span>
                    <span class="step-title-description step-text">One click export to my CV</span>
                  </div>
                </a>
              </li> -->
            </ul>
            <!-- End Step -->
          </div>
        </div>
        <!-- End Sticky Block -->
      </div>
      <!-- End Col -->

      <div id="formContainer" class="col-lg-8">
        <!-- Content Step Form -->
        <div id="uploadResumeStepFormContent">
          <!-- Card -->
          <div id="uploadResumeStepBasics" class="card active">
            @include('components.about.personal')
          </div>
          <!-- End Card -->

          <div id="uploadResumeStepEducation" class="card" style="display: none;">
          @include('components.about.education')
          </div>

          <div id="uploadResumeStepWork" class="card" style="display: none;">
          @include('components.about.professional')
          </div>

          <div id="uploadResumeStepJobSkills" class="card" style="display: none;">
          @include('components.about.skills')
          </div>

          <div id="uploadResumeStepTypeOfJob" class="card" style="display: none;">
          @include('components.about.contact')
          </div>

          <div id="uploadResumeStepMyCV" class="card" style="display: none;">
          @include('components.about.mycv')
          </div>
        </div>

        <!-- Message Body -->
        <div id="successMessageContent" style="display: none;">
          <div class="text-center">
            <img class="img-fluid mb-3" src="../assets/svg/illustrations/medal.svg" alt="Image Description" style="max-width: 15rem;">

            <div class="mb-4">
              <h2>Successful!</h2>
              <p>Your resume job has been successfully created.</p>
            </div>

            <div class="d-flex justify-content-center">
              <a class="btn btn-primary" href="../demo-jobs/employee.html">
                Go to profile <i class="bi-chevron-right small ms-1"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- End Message Body -->

        <!-- Sticky Block End Point -->
        <div id="stickyBlockEndPoint"></div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Step Form -->
</div>
<!-- End Content -->