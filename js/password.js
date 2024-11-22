document.addEventListener('DOMContentLoaded', () => {
  const togglePasswordIcons = document.querySelectorAll('.toggle-password');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm-password');
  const lengthRequirement = document.getElementById('length');
  const uppercaseRequirement = document.getElementById('uppercase');
  const numberRequirement = document.getElementById('number');
  const specialRequirement = document.getElementById('special');
  const matchRequirement = document.getElementById('match');

  togglePasswordIcons.forEach(icon => {
    icon.addEventListener('click', () => {
      const input = icon.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      }
    });
  });

  function validatePassword() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Validate length
    if (password.length >= 8) {
      lengthRequirement.classList.add('valid');
    } else {
      lengthRequirement.classList.remove('valid');
    }

    // Validate uppercase letter
    if (/[A-Z]/.test(password)) {
      uppercaseRequirement.classList.add('valid');
    } else {
      uppercaseRequirement.classList.remove('valid');
    }

    // Validate number
    if (/\d/.test(password)) {
      numberRequirement.classList.add('valid');
    } else {
      numberRequirement.classList.remove('valid');
    }

    // Validate special character
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
      specialRequirement.classList.add('valid');
    } else {
      specialRequirement.classList.remove('valid');
    }

    // Validate match
    if (password === confirmPassword && password !== '' && confirmPassword !== '') {
      matchRequirement.classList.add('valid');
    } else {
      matchRequirement.classList.remove('valid');
    }
  }

  passwordInput.addEventListener('input', validatePassword);
  confirmPasswordInput.addEventListener('input', validatePassword);
});