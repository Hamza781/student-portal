// Small helpers for the browser.
// IMPORTANT: JavaScript only makes the site nicer. The REAL checks are always in PHP,
// because a hacker can turn JavaScript off.

// 1) Ask "Are you sure?" before a form with data-confirm is sent.
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!confirm(form.dataset.confirm)) {
      event.preventDefault();          // stop the form
    }
  });
});

// 2) Register / create user: warn if the two passwords are different.
var pw1 = document.getElementById('password');
var pw2 = document.getElementById('password2');
if (pw1 && pw2) {
  pw2.form.addEventListener('submit', function (event) {
    if (pw1.value !== pw2.value) {
      alert('Passwords do not match.');
      event.preventDefault();
    }
  });
}

// 3) Attendance page: buttons "All present" / "All absent".
document.querySelectorAll('[data-set-all]').forEach(function (button) {
  button.addEventListener('click', function () {
    var value = button.dataset.setAll;               // "present" or "absent"
    document.querySelectorAll('input[type=radio][value="' + value + '"]').forEach(function (radio) {
      radio.checked = true;
    });
  });
});