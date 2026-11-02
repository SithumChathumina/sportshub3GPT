// Run this after the page has loaded.
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('.needs-validation');
    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('submit', function (event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }

    // Ask before deleting a record or cancelling a registration.
    var buttons = document.querySelectorAll('[data-confirm]');
    for (var j = 0; j < buttons.length; j++) {
        buttons[j].addEventListener('click', function (event) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    }

    // Use the local calendar date as the earliest booking date.
    var today = new Date();
    var year = today.getFullYear();
    var month = ('0' + (today.getMonth() + 1)).slice(-2);
    var day = ('0' + today.getDate()).slice(-2);
    var dateInputs = document.querySelectorAll('input[data-future-date]');
    for (var k = 0; k < dateInputs.length; k++) {
        dateInputs[k].min = year + '-' + month + '-' + day;
    }
});
