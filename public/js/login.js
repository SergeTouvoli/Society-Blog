document.addEventListener("DOMContentLoaded", () => {
    const eye = document.querySelector('#eye');
    const password = document.querySelector('#password');

    eye.addEventListener('click', function () {
        viewPassword(eye, password);
    });

});
