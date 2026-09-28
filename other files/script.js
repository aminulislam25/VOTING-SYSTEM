document.addEventListener('DOMContentLoaded', function() {
    const userTypeButtons = document.querySelectorAll('.user-type');
    const userTypeInput = document.getElementById('user_type');
    const loginForm = document.getElementById('loginForm');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.querySelector('.submit-btn');
    const passwordGroup = document.querySelector('.password-group');
    const mobileInput = document.getElementById('mobile');
    const passwordInput = document.getElementById('password');

    // User type selection
    userTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            userTypeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            userTypeInput.value = this.dataset.type;
        });
    });

    // Next button click handler
    nextBtn.addEventListener('click', function() {
        if (mobileInput.value.trim() === '') {
            alert('Please enter your mobile number');
            return;
        }

        // Validate mobile number
        const mobileRegex = /^[0-9]{10}$/;
        if (!mobileRegex.test(mobileInput.value)) {
            alert('Please enter a valid 10-digit mobile number');
            return;
        }

        // Check if mobile exists in database
        fetch('check_mobile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `mobile=${mobileInput.value}&user_type=${userTypeInput.value}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                nextBtn.style.display = 'none';
                passwordGroup.style.display = 'block';
                submitBtn.style.display = 'block';
                passwordInput.required = true;
            } else {
                alert('Mobile number not registered. Please sign up first.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        if (passwordGroup.style.display === 'none') {
            e.preventDefault();
            return;
        }

        if (passwordInput.value.trim() === '') {
            e.preventDefault();
            alert('Please enter your password');
        }
    });
}); 