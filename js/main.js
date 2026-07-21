/**
 * Habatech Digital Solutions - Front-End Logic Implementation Workspace
 * Configured dynamically for production deployment integration frameworks.
 */

$(document).ready(function() {

    $('.admin-datatable').DataTable({
        pageLength: 8,
        responsive: true
    });

    if ($('#trendChart').length) {
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun'],
                    datasets: [{
                        label: 'Registrations',
                        data: [12, 19, 15, 23, 28, 35],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.2)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }
    }

    if ($('#paymentChart').length) {
        const paymentCtx = document.getElementById('paymentChart');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Paid','Pending','Failed'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: ['#16a34a', '#f59e0b', '#ef4444']
                    }]
                },
                options: { responsive: true }
            });
        }
    }
    
    // Initialize AOS Animation Ecosystem safely
    if (typeof aos !== 'undefined' || $.isFunction(window.AOS?.init)) {
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-in-out'
        });
    }

    // 1. Dynamic Typing Automation Script Structure for Landing Hero Header
    const typingContainer = $('.typing-text');
    if (typingContainer.length > 0) {
        const phrases = ["Code Video Games.", "Build Mobile Apps.", "Design Web Applications."];
        let currentPhraseIndex = 0;
        let letterIndex = 0;
        let isDeleting = false;

        function playTypingLoop() {
            let currentString = phrases[currentPhraseIndex];
            if (isDeleting) {
                typingContainer.text(currentString.substring(0, letterIndex - 1));
                letterIndex--;
            } else {
                typingContainer.text(currentString.substring(0, letterIndex + 1));
                letterIndex++;
            }

            let timingDelta = isDeleting ? 40 : 100;
            if (!isDeleting && letterIndex === currentString.length) {
                timingDelta = 1800; // Pause at completion point
                isDeleting = true;
            } else if (isDeleting && letterIndex === 0) {
                isDeleting = false;
                currentPhraseIndex = (currentPhraseIndex + 1) % phrases.length;
                timingDelta = 400;
            }
            setTimeout(playTypingLoop, timingDelta);
        }
        playTypingLoop();
    }

    // 2. Incremental Statistics Numeric Counters Framework
    const counterElements = $('.counter');
    if (counterElements.length > 0) {
        let intersectionObserverAvailable = 'IntersectionObserver' in window;
        
        function runCounterAnimation(el) {
            let targetVal = parseInt($(el).attr('data-target'));
            $({ countNum: 0 }).animate({ countNum: targetVal }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $(el).text(Math.floor(this.countNum) + "+");
                },
                complete: function() {
                    $(el).text(this.countNum + "+");
                }
            });
        }

        if (intersectionObserverAvailable) {
            let counterObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        runCounterAnimation(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            counterElements.each(function() { counterObserver.observe(this); });
        } else {
            counterElements.each(function() { runCounterAnimation(this); });
        }
    }

    // 3. Early Bird Invoicing Expiration Counter Clock (Targets July 31, 2026)
    const timerDisplay = $('#countdown-timer');
    if (timerDisplay.length > 0) {
        const targetExpirationDate = new Date("July 31, 2026 23:59:59").getTime();

        const syncClockLoop = setInterval(function() {
            let nowTimestamp = new Date().getTime();
            let distanceRemaining = targetExpirationDate - nowTimestamp;

            if (distanceRemaining < 0) {
                clearInterval(syncClockLoop);
                timerDisplay.text("Early Bird Period Expired");
                return;
            }

            let calculationDays = Math.floor(distanceRemaining / (1000 * 60 * 60 * 24));
            let calculationHours = Math.floor((distanceRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let calculationMinutes = Math.floor((distanceRemaining % (1000 * 60 * 60)) / (1000 * 60));
            let calculationSeconds = Math.floor((distanceRemaining % (1000 * 60)) / 1000);

            timerDisplay.text(`${calculationDays}d ${calculationHours}h ${calculationMinutes}m ${calculationSeconds}s`);
        }, 1000);
    }

    // 4. Responsive Navbar Scroll-Shadow System Adjustment
    $(window).scroll(function() {
        if ($(this).scrollTop() > 40) {
            $('#mainNavbar').addClass('scrolled');
        } else {
            $('#mainNavbar').removeClass('scrolled');
        }
    });

    // ==========================================
    // MULTI-STEP WIZARD FORM LOGIC COMPONENT
    // ==========================================
    const wizardFormInstance = $('#multiStepForm');
    if (wizardFormInstance.length > 0) {
        let currentActiveStep = 1;
        const terminalStepsCount = 4;

        // Auto-detect routing queries (e.g. ?course=web) passed into platform window URL context
        const URLParameters = new URLSearchParams(window.location.search);
        const routedCourseParameter = URLParameters.get('course');
        if (routedCourseParameter) {
            $('#courseSelectionField').val(routedCourseParameter);
            evaluateLiveTuitionFees(routedCourseParameter);
        }

        // Live Local Upload Image Validation Preview Logic Layer
        $('#studentPhoto').change(function(e) {
            const uploadedFileRef = e.target.files[0];
            if (uploadedFileRef) {
                const targetReaderInstance = new FileReader();
                targetReaderInstance.onload = function(event) {
                    $('.upload-placeholder-icon').addClass('d-none');
                    $('#avatarImgPreview').attr('src', event.target.result).removeClass('d-none');
                }
                targetReaderInstance.readAsDataURL(uploadedFileRef);
            }
        });

        // Intercept Selection Configurations to Drive Fee Computations
        $('#courseSelectionField').change(function() {
            evaluateLiveTuitionFees($(this).val());
        });

        function evaluateLiveTuitionFees(courseKey) {
            const targetExpirationDate = new Date("July 31, 2026 23:59:59").getTime();
            let actualCurrentTimestamp = new Date().getTime();
            
            // Set fallback billing arrays dynamically
            let calculatedFinalFee = (actualCurrentTimestamp < targetExpirationDate) ? "30,000 CFA" : "35,000 CFA";
            
            let descriptiveCourseLabel = "None Selected";
            if (courseKey === 'scratch') descriptiveCourseLabel = "Game Dev with Scratch";
            if (courseKey === 'mobile') descriptiveCourseLabel = "Mobile App Development";
            if (courseKey === 'web') descriptiveCourseLabel = "Web App Development";

            $('#reviewCourseName').text(descriptiveCourseLabel);
            $('#reviewFeeDisplay').text(calculatedFinalFee);
        }

        // Handle Wizard Forward Navigation Adjustments
        $('#nextWizardStepBtn').click(function() {
            if (validateActiveStepDataInputs(currentActiveStep)) {
                if (currentActiveStep < terminalStepsCount) {
                    currentActiveStep++;
                    transitionWizardStepView(currentActiveStep);
                }
            }
        });

        // Handle Wizard Reverse Steps Back-Tracking Actions
        $('#prevWizardStepBtn').click(function() {
            if (currentActiveStep > 1) {
                currentActiveStep--;
                transitionWizardStepView(currentActiveStep);
            }
        });

        function validateActiveStepDataInputs(stepIndex) {
            let activeContainerNode = $(`.form-step[data-step="${stepIndex}"]`);
            let inputsAreValid = true;

            // Enforce basic HTML5 input constraints systematically
            activeContainerNode.find('input, select, textarea').each(function() {
                if (!this.checkValidity()) {
                    inputsAreValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            if (!inputsAreValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Incomplete Fields',
                    text: 'Please complete all required fields correctly before moving to the next section.',
                    confirmButtonColor: '#0B5ED7'
                });
            }
            return inputsAreValid;
        }

        function transitionWizardStepView(targetStepIndex) {
            // Update Horizontal Progress Tracker Node Element
            let progressPercentage = (targetStepIndex / terminalStepsCount) * 100;
            $('#wizardProgressBar').css('width', `${progressPercentage}%`);

            // Toggle Visibility Panels Across Steps Group Cards
            $('.form-step').addClass('d-none').removeClass('active-step');
            $(`.form-step[data-step="${targetStepIndex}"]`).removeClass('d-none').addClass('active-step');

            // Sync Active Indicator Tabs Group Layout Grid
            $('.step-tab').removeClass('active');
            $(`.step-tab[data-step="${targetStepIndex}"]`).addClass('active');

            // Adjust Footer Controls Display Visibility Array Rules
            if (targetStepIndex === 1) {
                $('#prevWizardStepBtn').addClass('d-none');
            } else {
                $('#prevWizardStepBtn').removeClass('d-none');
            }

            if (targetStepIndex === terminalStepsCount) {
                $('#nextWizardStepBtn').addClass('d-none');
                $('#submitWizardFormBtn').removeClass('d-none');
            } else {
                $('#nextWizardStepBtn').removeClass('d-none');
                $('#submitWizardFormBtn').addClass('d-none');
            }
        }

        // intercepted submit verification logic loop array configuration
        wizardFormInstance.on('submit', function(event) {
            event.preventDefault();
            
            if (!this.checkValidity()) {
                event.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Display native animated popup confirming validation transfer structures
            Swal.fire({
                icon: 'success',
                title: 'Registration Verified!',
                text: 'Your registration parameters are saved. Redirecting to payment processing gateway details...',
                timer: 3000,
                showConfirmButton: false,
                willClose: () => {
                    // This is where you smoothly link to your standard PHP endpoint or processing engine
                    console.log("Form processing verified successfully. Input fields package payload active.");
                }
            });
        });
    }
});