document.getElementById('registerForm').addEventListener('submit', function(e) {
    let username = document.getElementById('username').value;
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;
    
    if (username === '' || email === '' || password === '') {
        e.preventDefault();
        alert('Wszystkie pola muszą być wypełnione.');
    }
});
