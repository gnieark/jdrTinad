document.addEventListener("DOMContentLoaded", function () {
  document.getElementById('account-form').addEventListener('submit', function (e) {
      const pwd = document.getElementById('password').value;
      const confirmPwd = document.getElementById('confirm_password').value;
      const msg = document.getElementById('form-message');
    
      if (pwd && pwd !== confirmPwd) {
        e.preventDefault();
        msg.textContent = "Les mots de passe ne correspondent pas.";
        msg.style.color = "red";
      } else {
        msg.textContent = "";
      }
    });
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = this.previousElementSibling;
        navigator.clipboard.writeText(input.value);
      });
    });
});