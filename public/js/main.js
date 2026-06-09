(function() {
    // Mobile nav toggle
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            var expanded = toggle.getAttribute('aria-expanded') === 'true' ? false : true;
            toggle.setAttribute('aria-expanded', expanded);
            nav.classList.toggle('open');
        });
    }

    // Animated counters
    var counters = document.querySelectorAll('[data-count]');
    function animateCounters() {
        counters.forEach(function(counter) {
            var target = parseInt(counter.getAttribute('data-count'));
            var prefix = counter.getAttribute('data-prefix') || '';
            var current = 0;
            var increment = Math.ceil(target / 60);
            var timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = prefix + current.toLocaleString();
            }, 25);
        });
    }

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    }, { threshold: 0.3 });
    if (counters.length > 0) {
        observer.observe(counters[0].closest('.stats') || document.body);
    }

    // Amount presets
    var presets = document.querySelectorAll('.amount-preset');
    var amountInput = document.getElementById('donationAmount');
    presets.forEach(function(btn) {
        btn.addEventListener('click', function() {
            presets.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            if (amountInput) {
                amountInput.value = btn.getAttribute('data-amount');
            }
        });
    });

    // Recurring toggle
    var recurringToggle = document.getElementById('recurringToggle');
    var recurringOptions = document.getElementById('recurringOptions');
    if (recurringToggle && recurringOptions) {
        recurringToggle.addEventListener('change', function() {
            recurringOptions.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Scroll to top
    var scrollBtn = document.getElementById('scrollToTop');
    if (scrollBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });
        scrollBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Payment method selection - show info for selected method
    var methodSelect = document.getElementById('paymentMethodSelect');
    var methodInfo = document.getElementById('paymentMethodInfo');
    if (methodSelect && methodInfo) {
        function updatePaymentInfo() {
            var opt = methodSelect.options[methodSelect.selectedIndex];
            var driver = opt ? opt.getAttribute('data-driver') : '';
            if (driver === 'crypto') {
                methodInfo.style.display = 'block';
                methodInfo.innerHTML = '<i class="fas fa-info-circle" style="margin-' + (document.dir === 'rtl' ? 'left' : 'right') + ':6px;color:#f59e0b"></i> سيتم توجيهك إلى تعليمات تحويل العملة الرقمية بعد تقديم الطلب. تأكد من اختيار الشبكة الصحيحة (TRC20/ERC20/BEP20).';
            } else if (driver === 'bank_transfer') {
                methodInfo.style.display = 'block';
                methodInfo.innerHTML = '<i class="fas fa-info-circle" style="margin-' + (document.dir === 'rtl' ? 'left' : 'right') + ':6px;color:var(--color-primary)"></i> بعد تقديم الطلب، ستظهر لك تعليمات التحويل البنكي وتفاصيل الحساب.';
            } else if (driver === 'stripe' || driver === 'paypal') {
                methodInfo.style.display = 'block';
                methodInfo.innerHTML = '<i class="fas fa-lock" style="margin-' + (document.dir === 'rtl' ? 'left' : 'right') + ':6px;color:var(--color-primary)"></i> سيتم تحويلك إلى بوابة الدفع الآمنة لإتمام عملية التبرع.';
            } else {
                methodInfo.style.display = 'none';
            }
        }
        methodSelect.addEventListener('change', updatePaymentInfo);
        updatePaymentInfo();
    }

    // Dark mode
    var darkToggle = document.getElementById('darkModeToggle');
    if (darkToggle) {
        var saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        darkToggle.addEventListener('click', function() {
            var html = document.documentElement;
            var isDark = html.getAttribute('data-theme') === 'dark';
            if (isDark) {
                html.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                darkToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                darkToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    }
})();
