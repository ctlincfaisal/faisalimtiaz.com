@extends('_layout.app')

@section('content')

<link rel="stylesheet" href="{{ url('assets/vendor/quill/dist/quill.snow.css') }}">


<div class="container content-space-b-1 content-space-lg-1" id="technologiesiuse">

    @include('components.about.main')

</div>
<!-- JS Implementing Plugins -->
<script src="{{ url('assets/vendor/hs-step-form/dist/hs-step-form.min.js') }}"></script>
<script src="{{ url('assets/vendor/hs-sticky-block/dist/hs-sticky-block.min.js') }}"></script>
<script src="{{ url('assets/vendor/hs-add-field/dist/hs-add-field.min.js') }}"></script>
<script src="{{ url('assets/vendor/imask/dist/imask.min.js') }}"></script>
<script src="{{ url('assets/vendor/quill/dist/quill.min.js') }}"></script>

<!-- JS Front -->

<!-- JS Plugins Init. -->
<script>
(function() {
    // INITIALIZATION OF STICKY BLOCKS
    // =======================================================
    new HSStickyBlock('.js-sticky-block', {
        targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' :
            null
    })


    // INITIALIZATION OF STEP FORM
    // =======================================================
    new HSStepForm('.js-step-form', {
        finish: () => {
            document.getElementById("uploadResumeStepFormProgress").style.display = 'none'
            document.getElementById("uploadResumeStepFormContent").style.display = 'none'
            document.getElementById("successMessageContent").style.display = 'block'
            scrollToTop('#header');
            const formContainerEg1 = document.getElementById('formContainerEg1')
            formContainerEg1.classList.remove('col-lg-8')
            formContainerEg1.classList.add('col-lg-12')
        },
        onNextStep: function() {
            // scrollToTop()
        },
        onPrevStep: function() {
            // scrollToTop()
        }
    })

    function scrollToTop(el = '.js-step-form') {
        el = document.querySelector(el)
        window.scrollTo({
            top: (el.getBoundingClientRect().top + window.scrollY) - 30,
            left: 0,
            behavior: 'smooth'
        })
    }

})()
</script>

@endsection